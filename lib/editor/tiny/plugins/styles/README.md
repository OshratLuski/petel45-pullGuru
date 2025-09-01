# Tiny Styles Plugin for TinyMCE (Moodle)

This plugin adds a customizable **styles dropdown menu** to the TinyMCE editor in Moodle, allowing users to apply predefined CSS classes to selected text.

---

## Features

- Apply pre-configured **block or inline styles**.
- Each style can have:
  - Custom classes
  - Styled preview inside the dropdown
- Includes a **"Clear Style"** option to remove styling.
- Fully supports **RTL languages** (e.g., Hebrew).

---

## Installation

1. Place the plugin in the following directory:  
/lib/editor/tiny/plugins/tiny_styles

2. Navigate to:  
**Site administration > Plugins > TinyMCE editor > Manage TinyMCE plugins**,  
and enable `Tiny Styles`.

3. Define your styles under:  
**Site administration > Plugins > TinyMCE editor > Tiny Styles > Configuration**

---

## ⚙️ JSON Configuration Example

Paste the following in the configuration settings:

```json
[
  {
    "title": "Green box",
    "type": "block",
    "classes": "attostylesbox attostylesbox-outline-green"
  },
  {
    "title": "Yellow highlight",
    "type": "inline",
    "classes": "attostylestextmarker attostylestextmarker-yellow"
  }
]
```
💡 Make sure the CSS classes used are defined in your theme or added via Additional HTML.

## Clear Style Option
Selecting "Clear Style" from the dropdown removes all attostylesbox-related classes from the selected text, while keeping the rest of the classes and content intact.

## Translations
English (default)
Hebrew: included under lang/he/tiny_styles.php

## Developer
Created with ❤️ by
Oshrat Luski
📧 oshrat.luski@weizmann.ac.il

## License
This plugin is licensed under the GNU GPL v3.