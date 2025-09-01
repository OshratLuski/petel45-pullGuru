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
 * Metadata menu fieldtype plugin language file.
 *
 * @package local_metadata
 * @subpackage metadatafieldtype_treeselect
 * @author Mike Churchward <mike.churchward@poetgroup.org>
 * @copyright 2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Treeselect metadata fieldtype';
$string['displayname'] = 'Tree select';
$string['privacy:metadata'] = 'Fieldtypes do not store data.';
$string['describe'] = '
{
  "row_1": {
    "lang": {
      "en": "Row 1",
      "he": "שורה 1"
    },
    "branches": {
      "row_11": {
        "lang": {
          "en": "Row 11",
          "he": "1שורה 1"
        }
      }
    }
  },
  "row_2": {
    "lang": {
      "en": "Row 2",
      "he": "שורה 2"
    },
    "branches": {
      "row_21": {
        "lang": {
          "en": "Row 21",
          "he": "2שורה 1"
        }
      }
    }
  }
}
';
$string['describedefault'] = 'row_1,row_2';