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
 * Diagnosticadvai question type upgrade code.
 *
 * @package    qtype_diagnosticadvai
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function for the diagnosticadvai question type plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Returns true on success.
 */
function xmldb_qtype_diagnosticadvai_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025031304) {

        $table = new xmldb_table('qtype_diagadvai_prompts');
        $field = new xmldb_field('fullprompt', XMLDB_TYPE_TEXT, null, null, null, null, null, 'prompt');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025031304, 'qtype', 'diagnosticadvai');
    }

    if ($oldversion < 2025031305) {
        $table = new xmldb_table('qtype_diagadvai_options');
        $field = new xmldb_field('temperature', XMLDB_TYPE_NUMBER, '12, 7', null, null, null, null, 'relatedqid');
        $dbman->change_field_type($table, $field);
        upgrade_plugin_savepoint(true, 2025031305, 'qtype', 'diagnosticadvai');
    }

    if ($oldversion < 2025031805) {

        $table = new xmldb_table('qtype_diagadvai_options');
        $field = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'relatedqid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025031805, 'qtype', 'diagnosticadvai');
    }

    return true;
}
