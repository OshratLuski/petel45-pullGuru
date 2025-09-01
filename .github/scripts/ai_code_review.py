#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# NOTE: All comments in English as requested.

import os, json, re, argparse
import requests
import boto3

# ---------- Env ----------
GITHUB_TOKEN = os.environ["GITHUB_TOKEN"]
REPO = os.environ["REPO"]
PR_NUMBER = os.environ["PR_NUMBER"]
AWS_REGION = os.environ.get("AWS_REGION", "us-west-2")
MODEL_ID = os.environ.get("ANTHROPIC_MODEL_ID", "anthropic.claude-3-sonnet-20240229-v1:0")
MAX_TOKENS = int(os.environ.get("MAX_TOKENS", "4000"))

GH_API = "https://api.github.com"

# Debug toggle (set DEBUG=1 in workflow env to enable)
DEBUG = os.environ.get("DEBUG", "0") == "1"
def debug(msg: str):
    if DEBUG:
        print(f"DEBUG: {msg}")

# ---------- GitHub helpers ----------
def gh_headers():
    return {
        "Authorization": f"Bearer {GITHUB_TOKEN}",
        "Accept": "application/vnd.github+json",
        "X-GitHub-Api-Version": "2022-11-28",
    }

def get_changed_files():
    url = f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}/files"
    files = []
    page = 1
    while True:
        resp = requests.get(url, headers=gh_headers(), params={"page": page, "per_page": 100}, timeout=30)
        resp.raise_for_status()
        chunk = resp.json()
        if not chunk:
            break
        files.extend(chunk)
        page += 1
    return files

def get_full_pr_diff_text() -> str:
    """
    Fallback: fetch entire PR diff as unified text using 'diff' media type.
    """
    url = f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}"
    headers = gh_headers().copy()
    headers["Accept"] = "application/vnd.github.v3.diff"
    resp = requests.get(url, headers=headers, timeout=30)
    resp.raise_for_status()
    return resp.text or ""

def create_or_update_comment(body: str):
    list_url = f"{GH_API}/repos/{REPO}/issues/{PR_NUMBER}/comments"
    resp = requests.get(list_url, headers=gh_headers(), timeout=30)
    resp.raise_for_status()
    comments = resp.json()
    marker = "<!-- ai-code-review:bedrock-claude -->"

    existing = next((c for c in comments if c.get("body","").startswith(marker)), None)
    payload = {"body": f"{marker}\n{body}"}

    if existing:
        edit_url = f"{GH_API}/repos/{REPO}/issues/comments/{existing['id']}"
        r = requests.patch(edit_url, headers=gh_headers(), json=payload, timeout=30)
        r.raise_for_status()
    else:
        r = requests.post(list_url, headers=gh_headers(), json=payload, timeout=30)
        r.raise_for_status()

# ---------- Bedrock (Anthropic Messages) ----------
bedrock = boto3.client("bedrock-runtime", region_name=AWS_REGION)

SYSTEM_PROMPT = """You are a senior Moodle developer doing inline code review on a GitHub Pull Request.
Return ONLY JSON (no markdown) with this schema:
{
  "comments": [
    {
      "path": "relative/file/path.php",
      "anchor_text": "exact line as it appears ADDED in the diff (without the leading '+')",
      "message": "short precise review comment, Moodle-specific if relevant",
      "suggestion": "optional: full replacement text for a GitHub suggestion block"
    }
  ]
}
Rules:
- Comment ONLY on lines that were ADDED/CHANGED in this PR (use the diff).
- Use 'anchor_text' to identify the added line to attach to.
- Up to 20 comments. Be concrete and actionable. Prefer one comment per issue.
- If you propose a fix, fill 'suggestion' with the exact replacement content (no backticks).
- Focus on Moodle standards (PSR-12, frankenstyle), security (XSS/CSRF/SQLi), API usage, I18N, accessibility, docs, tests, performance.
"""

USER_PREFIX = """Review PR {pr} in {repo}. Here is a unified diff chunk.
Output ONLY the JSON described above. Do not write anything else.

Diff:

{diff}

"""

def parse_comments_json(text: str) -> list[dict]:
    """Parse model output defensively and return list of comments."""
    try:
        obj = json.loads(text)
        return obj.get("comments", []) if isinstance(obj, dict) else []
    except Exception:
        start = text.find("{")
        end = text.rfind("}")
        if start != -1 and end != -1 and end > start:
            try:
                obj = json.loads(text[start:end+1])
                return obj.get("comments", []) if isinstance(obj, dict) else []
            except Exception:
                return []
        return []

def build_unified_diff(files):
    """
    Build a unified diff from the GitHub 'files' API.
    If files lack 'patch' (binary/large), returns empty string and caller may fallback.
    """
    diffs = []
    for f in files:
        filename = f.get("filename")
        status = f.get("status")
        has_patch = "patch" in f

        debug(f"file: {filename} status={status} has_patch={has_patch}")
        if not has_patch:
            continue

        if status == "removed":
            header = f"--- a/{filename}\n+++ /dev/null\n"
        else:
            header = f"--- a/{filename}\n+++ b/{filename}\n"
        patch = f["patch"]
        diffs.append(header + patch)
    return "\n".join(diffs)

def call_bedrock(prompt: str) -> str:
    req = {
        "anthropic_version": "bedrock-2023-05-31",
        "max_tokens": MAX_TOKENS,
        "system": SYSTEM_PROMPT,
        "messages": [
            {"role": "user", "content": [{"type":"text", "text": prompt}]}
        ]
    }
    resp = bedrock.invoke_model(
        modelId=MODEL_ID,
        contentType="application/json",
        accept="application/json",
        body=json.dumps(req),
    )
    body = json.loads(resp["body"].read())
    parts = body.get("content", [])
    text = ""
    for p in parts:
        if p.get("type") == "text":
            text += p.get("text","")
    return text.strip()

def chunk_text(text: str, max_chars: int = 12000):
    """Split large unified diff into chunk(s) at hunk boundaries when possible."""
    text = text.strip()
    if len(text) <= max_chars:
        return [text]
    chunks, start = [], 0
    while start < len(text):
        end = min(start + max_chars, len(text))
        split = text.rfind("\n@@", start, end)
        if split == -1 or split <= start + 1000:
            split = text.rfind("\n", start, end)
            if split == -1 or split <= start:
                split = end
        chunks.append(text[start:split])
        start = split
    return chunks

# ---------- Mapping patch -> new-file line numbers ----------
HUNK_RE = re.compile(r'^@@ -\d+(?:,\d+)? \+(\d+)(?:,\d+)? @@')

def build_newline_map(patch: str) -> dict[int, int | None]:
    """
    Returns mapping: patch-index (1-based) -> new-file line number.
    For lines not on RIGHT side (e.g. '-' deletions or headers), value is None.
    """
    mapping: dict[int, int | None] = {}
    pos = 0
    new_line = None
    for raw in patch.splitlines():
        pos += 1
        m = HUNK_RE.match(raw)
        if m:
            new_line = int(m.group(1))
            mapping[pos] = None
            continue
        if new_line is None:
            mapping[pos] = None
            continue
        if raw.startswith('+'):
            mapping[pos] = new_line
            new_line += 1
        elif raw.startswith('-'):
            mapping[pos] = None
        else:  # context ' '
            mapping[pos] = new_line
            new_line += 1
    return mapping

def find_position_and_line(patch: str, anchor_text: str) -> tuple[int | None, int | None]:
    """
    Returns (patch_position, new_file_line) for the first added line that equals anchor_text.
    """
    target = "+" + anchor_text.strip("\n")
    lines = patch.splitlines()
    for idx, ln in enumerate(lines, start=1):
        if ln == target:
            newline_map = build_newline_map(patch)
            return idx, newline_map.get(idx)
    return None, None

def post_inline_comment_single(path: str, commit_id: str, line: int, body: str):
    """
    Create a single inline PR comment using 'line' + side=RIGHT (no 'position').
    """
    url = f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}/comments"
    payload = {
        "path": path,
        "commit_id": commit_id,
        "side": "RIGHT",
        "line": line,
        "body": body
    }
    debug(f"POST inline comment path={path} line={line} body_len={len(body)}")
    r = requests.post(url, headers=gh_headers(), json=payload, timeout=30)
    r.raise_for_status()

# ---------- Main ----------
def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--emit-verdict", action="store_true")
    args = parser.parse_args()

    # 1) Changed files
    files = get_changed_files()
    debug(f"files count from GitHub API: {len(files)}")

    # Notice about theme changes outside theme/petel
    theme_warnings = []
    for f in files:
        filename = f.get("filename", "")
        if filename.startswith("theme/") and not filename.startswith("theme/petel"):
            theme_warnings.append(f"- {filename}")
    if theme_warnings:
        warning_text = (
            "⚠️ **Notice:** Changes detected in theme directories outside of `theme/petel`.\n\n"
            "The following files were modified:\n"
            + "\n".join(theme_warnings) +
            "\n\nPlease avoid editing themes other than `theme/petel`."
        )
        create_or_update_comment(warning_text)

    if not files:
        create_or_update_comment("No changed files detected.")
        if args.emit_verdict:
            print("comment", end="")
        return

    # Use patch from files API to compute lines; build unified diff for LLM context
    patch_by_path = {f["filename"]: f.get("patch", "") for f in files if f.get("patch")}
    unified = build_unified_diff(files)
    debug(f"unified diff length (from files API): {len(unified)}")

    if not unified:
        debug("no patches via files API; fetching full PR diff text…")
        unified = get_full_pr_diff_text()
        debug(f"unified diff length (full PR diff): {len(unified)}")

    if not unified and not patch_by_path:
        create_or_update_comment("Changed files are binary or too large; no textual diff available.")
        if args.emit_verdict:
            print("comment", end="")
        return

    # 2) Send to model in chunks and receive JSON with inline comments
    chunks = chunk_text(unified if unified else "")
    raw_comments = []
    for i, chunk in enumerate(chunks, 1):
        user = USER_PREFIX.format(repo=REPO, pr=PR_NUMBER, diff=chunk)
        debug(f"sending chunk {i}/{len(chunks)} to model; chunk_len={len(chunk)}")
        out = call_bedrock(user)
        raw_comments.extend(parse_comments_json(out))

    # 3) Process JSON -> per-line inline comments using line+side
    #    Fetch PR head sha once
    pr = requests.get(f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}", headers=gh_headers(), timeout=30)
    pr.raise_for_status()
    head_sha = pr.json()["head"]["sha"]

    published = 0
    for c in raw_comments:
        path = c.get("path")
        anchor = (c.get("anchor_text") or "").rstrip("\n")
        msg = (c.get("message") or "").strip()
        suggestion = c.get("suggestion")

        if not path or not anchor or not msg:
            continue

        patch = patch_by_path.get(path)
        if not patch:
            # No patch for this file (binary/too-large) -> cannot place inline comment
            continue

        patch_pos, new_line = find_position_and_line(patch, anchor)
        if new_line is None:
            # Could not resolve a new-file line for this anchor
            debug(f"anchor not found or not add-line: path={path} anchor='{anchor[:80]}'")
            continue

        body = msg
        if suggestion:
            body += "\n\n```suggestion\n" + suggestion.rstrip("\n") + "\n```"

        try:
            post_inline_comment_single(path, head_sha, new_line, body)
            published += 1
        except requests.HTTPError as e:
            # Log and continue with the rest
            debug(f"failed to post comment on {path}:{new_line} -> {e}")

    if not published:
        create_or_update_comment("AI review: no actionable inline comments on changed lines.")

    # Minimal feedback so you know the run executed
    if args.emit_verdict:
        print("comment", end="")

if __name__ == "__main__":
    main()
