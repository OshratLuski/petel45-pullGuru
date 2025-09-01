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
 * @package    qtype_diagnosticadvdesc
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function for the diagnosticadvdesc question type plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Returns true on success.
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_diagnosticadvdesc_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2025031806) {

        $table = new xmldb_table('qtype_diagnosticadvdesc');
        $field = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0','relatedqid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table->add_key('quiz', XMLDB_KEY_FOREIGN, ['quizid'], 'quiz', ['id']);
        $dbman->drop_key($table, new xmldb_key('quizid', XMLDB_KEY_FOREIGN_UNIQUE, ['quizid'], 'quiz', ['id']));
        $dbman->add_key($table, new xmldb_key('quizid_fk', XMLDB_KEY_FOREIGN, ['quizid'], 'quiz', ['id']));

        upgrade_plugin_savepoint(true, 2025031806, 'qtype', 'diagnosticadvdesc');
    }

    return true;
}