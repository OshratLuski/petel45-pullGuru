# TinyMCE Embed HTML Plugin for Moodle 4.5

This TinyMCE 6 plugin for Moodle allows users to upload `.html` files, stores them in Moodle's file system, and embeds them in the editor content as iframes.

## Features

- Upload an HTML file using a dialog.
- Validate and persist the file securely with `sesskey`.
- Embed using Moodle's `pluginfile.php`.

## Installation

1. Place the `embedhtml` folder inside `lib/editor/tiny/plugins/`.
2. Purge Moodle caches.
3. Ensure you set the `window.contextid` and `M.cfg.sesskey` in your page.

## Security

- Only `text/html` files accepted.
- CSRF protection using Moodle `sesskey`.

## License

GPL v3
