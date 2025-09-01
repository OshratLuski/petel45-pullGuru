<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Metadata multiselect fieldtype plugin language file.
 *
 * @package metadatafieldtype_multiselect
 * @subpackage metadatafieldtype_multiselect
 * @author Tamir Hajaj <tamir@sysbind.co.il>
 * @copyright 2022 Tamir Hajaj {@link https://sysbind.co.il}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'multiselect metadata fieldtype';
$string['displayname'] = 'Dropdown multiselect';
$string['privacy:metadata'] = 'Fieldtypes store data';
$string['notinrightformat'] = 'Not in the right format, format should be like - <key>:<lang>=<value>|<lang>=<value>|...';
$string['checkifmultiselectornot'] = 'אפשרות לבחירה מרובה';
$string['duplicatevalues'] = 'קיימים ערכים כפולים.';
$string['rightformat_help'] = "The format should be used in the following way: <key>:<values> and each <values> should include at least an English ".
    " and a Hebrew value seperated by '|' in the format <lang>=<value>|<lang>=<value>, where Hebrew <lang> can be either 'he' or 'he_kids' depending on your system. ".
    " Here is a full example: 1:en=Checking|he_kids=בדיקה ";
$string['rightformat'] = 'יש להזין מפתח וערכים לפי תחביר מיוחד המוצג בחלונית העזרה';
$string['example'] = 'דוגמה:';
$string['type'] = 'סוג תצוגה';
$string['singlechoice'] = 'חד-ברירה';
$string['multichoice'] = 'רב-ברירה';
