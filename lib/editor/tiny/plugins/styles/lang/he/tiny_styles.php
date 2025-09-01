<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     tiny_styles
 * @copyright   2025 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Tiny Styles';
$string['stylebutton'] = 'עיצובים';
$string['buttontitle'] = 'עיצובים';
$string['privacy:metadata'] = 'תוסף עיצובים עבור TinyMCE אינו שומר נתונים אישיים.';
$string['clearstyle'] = 'הסר עיצוב';
$string['settings'] = 'הגדרות תוסף עיצובים';
$string['config'] = 'תצורת עיצובים מותאמים אישית';
$string['config_desc'] = 'הגדר את רשימת העיצובים שזמינים בתוסף זה בפורמט JSON.

כל עיצוב חייב לכלול:
- "title": השם שיוצג בתפריט הנפתח.
- "type": "block" או "inline".
- "classes": שמות מחלקות CSS מופרדים ברווחים.

דוגמה:
[
    {
        "title": "תיבה ירוקה",
        "type": "block",
        "classes": "attostylesbox attostylesbox-outline-green"
    },
    {
        "title": "הדגשה צהובה",
        "type": "inline",
        "classes": "attostylestextmarker attostylestextmarker-yellow"
    }
]

מחלקות ה־CSS המוגדרות חייבות להיות קיימות בתבנית (theme) או להיווסף דרך HTML נוסף.';
