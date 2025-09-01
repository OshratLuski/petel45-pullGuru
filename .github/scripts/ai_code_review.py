#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# NOTE: All comments in English.

import os, json, re, argparse, sys
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
        print(f"[DEBUG] {msg}", file=sys.stderr)

# ---------- Path filters / normalization ----------
IGNORED_RE = re.compile(r'.*\.(min\.js|map)$')  # ignore *.min.js and *.map

def is_ignored_path(p: str) -> bool:
    p = p.lstrip('./')
    return bool(IGNORED_RE.match(p))

def norm_path(p: str) -> str:
    p = p.lstrip('./')
    if p.startswith('a/') or p.startswith('b/'):
        p = p[2:]
    return p

# ---------- GitHub helpers ----------
def gh_headers():
    return {
        "Authorization": f"Bearer {GITHUB_TOKEN}",
        "Accept": "application/vnd.github+json",
        "X-GitHub-Api-Version": "2022-11-28",
    }

def get_changed_files():
    url = f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}/files"
    files, page = [], 1
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
    existing = next((c for c in comments if c.get("body", "").startswith(marker)), None)
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
- Avoid minified or generated files (e.g. *.min.js, *.map).
"""

USER_PREFIX = """Review PR {pr} in {repo}. Here is a unified diff chunk.
Output ONLY the JSON described above. Do not write anything else.

Diff:

{diff}

"""

def parse_comments_json(text: str) -> list[dict]:
    try:
        obj = json.loads(text)
        return obj.get("comments", []) if isinstance(obj, dict) else []
    except Exception:
        start, end = text.find("{"), text.rfind("}")
        if start != -1 and end != -1 and end > start:
            try:
                obj = json.loads(text[start:end+1])
                return obj.get("comments", []) if isinstance(obj, dict) else []
            except Exception:
                return []
        return []

def build_unified_diff(files):
    """
    Build unified diff only for non-ignored files that actually have a patch.
    """
    diffs = []
    for f in files:
        filename = f.get("filename")
        if not filename or is_ignored_path(filename):
            debug(f"skip ignored in unified: {filename}")
            continue
        status = f.get("status")
        has_patch = "patch" in f
        debug(f"file: {filename} status={status} has_patch={has_patch}")
        if not has_patch:
            continue
        header = f"--- a/{filename}\n+++ {'/dev/null' if status=='removed' else 'b/'+filename}\n"
        diffs.append(header + f["patch"])
    return "\n".join(diffs)

def call_bedrock(prompt: str) -> str:
    req = {
        "anthropic_version": "bedrock-2023-05-31",
        "max_tokens": MAX_TOKENS,
        "system": SYSTEM_PROMPT,
        "messages": [{"role": "user", "content": [{"type": "text", "text": prompt}]}],
    }
    resp = bedrock.invoke_model(modelId=MODEL_ID, contentType="application/json",
                                accept="application/json", body=json.dumps(req))
    body = json.loads(resp["body"].read())
    parts = body.get("content", [])
    text = "".join(p.get("text", "") for p in parts if p.get("type") == "text")
    return text.strip()

def chunk_text(text: str, max_chars: int = 12000):
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

# ---------- Patch -> new-file line numbers ----------
HUNK_RE = re.compile(r'^@@ -\d+(?:,\d+)? \+(\d+)(?:,\d+)? @@')

def build_newline_map(patch: str) -> dict[int, int | None]:
    """
    Map patch line index (1-based) to RIGHT-side new-file line numbers.
    Non-right lines (headers or '-') map to None.
    """
    mapping: dict[int, int | None] = {}
    pos, new_line = 0, None
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

def normalize_code_line(s: str) -> str:
    # Collapse whitespace and strip trailing commas/semicolons.
    s = s.strip()
    s = re.sub(r'\s+', ' ', s)
    s = re.sub(r'[;,]\s*$', '', s)
    return s

def find_new_line_fuzzy(patch: str, anchor_text: str) -> int | None:
    """
    Find new-file line for anchor using exact -> normalized -> substring matching
    on '+' and ' ' lines. If the anchor matches a '-' line (deletion), bind to the
    nearest '+' line in the same hunk (replacement scenario).
    """
    if not anchor_text:
        return None
    target_exact = anchor_text.strip('\n')
    target_norm = normalize_code_line(target_exact)
    lines = patch.splitlines()
    m = build_newline_map(patch)

    def is_hunk_sep(s: str) -> bool:
        return s.startswith('@@')

    # 1) exact match on added or context line
    for idx, ln in enumerate(lines, 1):
        if (ln.startswith('+') or ln.startswith(' ')) and ln[1:] == target_exact:
            return m.get(idx)

    # 2) normalized equality on added or context
    for idx, ln in enumerate(lines, 1):
        if ln.startswith('+') or ln.startswith(' '):
            if normalize_code_line(ln[1:]) == target_norm:
                return m.get(idx)

    # 3) substring fallback (ignore too-short anchors)
    if len(target_norm) >= 6:
        for idx, ln in enumerate(lines, 1):
            if ln.startswith('+') or ln.startswith(' '):
                if target_norm in normalize_code_line(ln[1:]):
                    return m.get(idx)

    # 4) anchor matched a removed line -> bind to nearest '+' in same hunk
    for idx, ln in enumerate(lines, 1):
        if ln.startswith('-') and normalize_code_line(ln[1:]) == target_norm:
            # forward within hunk
            j = idx + 1
            while j <= len(lines) and not is_hunk_sep(lines[j-1]):
                if lines[j-1].startswith('+'):
                    return m.get(j)
                j += 1
            # backward within hunk
            j = idx - 1
            while j >= 1 and not is_hunk_sep(lines[j-1]):
                if lines[j-1].startswith('+'):
                    return m.get(j)
                j -= 1
            break

    return None

def post_inline_comment_single(path: str, commit_id: str, line: int, body: str):
    url = f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}/comments"
    payload = {"path": path, "commit_id": commit_id, "side": "RIGHT", "line": line, "body": body}
    debug(f"POST inline comment path={path} line={line} body_len={len(body)}")
    r = requests.post(url, headers=gh_headers(), json=payload, timeout=30)
    r.raise_for_status()

# ---------- Parse unified diff into per-file patches (fallback) ----------
def build_patch_map_from_unified(unified: str) -> dict[str, str]:
    """
    Parse the full unified diff text into per-file patch snippets keyed by the RIGHT path (b/<path>).
    Only returns patches that actually have hunks (@@ ...).
    """
    patches: dict[str, list[str]] = {}
    cur_path = None
    collecting = False
    buf: list[str] = []

    for line in unified.splitlines():
        if line.startswith('diff --git '):
            # flush previous
            if cur_path and buf and any(l.startswith('@@') for l in buf):
                patches[cur_path] = buf.copy()
            cur_path, collecting, buf = None, False, []
            continue

        if line.startswith('--- '):
            buf = [line]
            collecting = True
            continue

        if collecting and line.startswith('+++ '):
            buf.append(line)
            # decide path on RIGHT side
            if line.startswith('+++ b/'):
                cur_path = line[6:]
            elif line.startswith('+++ /dev/null'):
                cur_path = None  # removed file -> no RIGHT side
            else:
                cur_path = line[4:]
            continue

        if collecting:
            buf.append(line)

    # flush last
    if cur_path and buf and any(l.startswith('@@') for l in buf):
        patches[cur_path] = buf.copy()

    return {p: "\n".join(lines) for p, lines in patches.items()}

# ---------- Main ----------
def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--emit-verdict", action="store_true")
    args = parser.parse_args()

    files = get_changed_files()
    debug(f"files count from GitHub API: {len(files)}")

    # Theme warning
    theme_warnings = []
    for f in files:
        filename = f.get("filename", "")
        if filename.startswith("theme/") and not filename.startswith("theme/petel"):
            theme_warnings.append(f"- {filename}")
    if theme_warnings:
        create_or_update_comment(
            "⚠️ **Notice:** Changes detected in theme directories outside of `theme/petel`.\n\n"
            "The following files were modified:\n" + "\n".join(theme_warnings) +
            "\n\nPlease avoid editing themes other than `theme/petel`."
        )

    if not files:
        create_or_update_comment("No changed files detected.")
        if args.emit_verdict:
            print("comment", end="")
        return

    # Prepare diffs & patches (skip ignored)
    patch_by_path = {
        f["filename"]: f.get("patch", "")
        for f in files
        if f.get("patch") and not is_ignored_path(f.get("filename",""))
    }
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

    # Fill patch_by_path from unified diff for files missing patch (still skip ignored)
    if unified:
        fallback_map = build_patch_map_from_unified(unified)
        filled = 0
        for p, patch in fallback_map.items():
            if is_ignored_path(p):
                continue
            if p not in patch_by_path:
                patch_by_path[p] = patch
                filled += 1
        debug(f"filled {filled} missing patches from unified; total patch_by_path={len(patch_by_path)}")

    # 2) LLM
    chunks = chunk_text(unified if unified else "")
    raw_comments = []
    for i, chunk in enumerate(chunks, 1):
        user = USER_PREFIX.format(repo=REPO, pr=PR_NUMBER, diff=chunk)
        debug(f"sending chunk {i}/{len(chunks)} to model; chunk_len={len(chunk)}")
        out = call_bedrock(user)
        got = parse_comments_json(out)
        debug(f"model returned {len(got)} comments on chunk {i}")
        raw_comments.extend(got)

    # 3) Publish comments using fuzzy anchor matching
    pr = requests.get(f"{GH_API}/repos/{REPO}/pulls/{PR_NUMBER}", headers=gh_headers(), timeout=30)
    pr.raise_for_status()
    head_sha = pr.json()["head"]["sha"]

    published = 0
    skipped_no_patch = 0
    skipped_no_anchor = 0

    for c in raw_comments:
        path = norm_path(c.get("path", ""))
        if is_ignored_path(path):
            debug(f"skip ignored from model: {path}")
            continue

        anchor = (c.get("anchor_text") or "").rstrip("\n")
        msg = (c.get("message") or "").strip()
        suggestion = c.get("suggestion")

        if not path or not anchor or not msg:
            continue

        patch = patch_by_path.get(path)
        if not patch:
            skipped_no_patch += 1
            debug(f"no patch for path={path} (likely large/binary/minified) -> skip")
            continue

        new_line = find_new_line_fuzzy(patch, anchor)
        if new_line is None:
            skipped_no_anchor += 1
            debug(f"anchor not found in hunk: path={path} anchor='{anchor[:120]}'")
            continue

        body = msg
        if suggestion:
            body += "\n\n```suggestion\n" + suggestion.rstrip("\n") + "\n```"

        try:
            post_inline_comment_single(path, head_sha, new_line, body)
            published += 1
        except requests.HTTPError as e:
            debug(f"failed to post comment on {path}:{new_line} -> {e}")

    # Always post a summary so it's visible why there are/aren't comments
    summary = {
        "raw_comments_count": len(raw_comments),
        "published": published,
        "skipped_no_patch": skipped_no_patch,
        "skipped_no_anchor": skipped_no_anchor,
    }
    create_or_update_comment("AI review summary:\n\n```\n" + json.dumps(summary, indent=2) + "\n```")

    if published == 0:
        create_or_update_comment("AI review: no actionable inline comments on changed lines.")

    if args.emit_verdict:
        print("comment", end="")

if __name__ == "__main__":
    main()
