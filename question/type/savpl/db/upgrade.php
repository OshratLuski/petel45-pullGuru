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
 * Upgrade script for savpl question type
 *
 * @package    qtype_savpl
 * @copyright  2024 Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the savpl question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_savpl_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ($oldversion < 2024040700) {
        // Define field usecase to be added to question_savpl.
        $table = new xmldb_table('question_savpl');
        $field = new xmldb_field('disablerun', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'gradingmethod');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Savepoint reached.
        //upgrade_plugin_savepoint(true, 2024040700, 'qtype', 'savpl');
    }

    if ($oldversion < 2024101100) {
        // Define field usecase to be added to question_savpl.
        $table = new xmldb_table('question_savpl_prompts');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('qattemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('prompt', XMLDB_TYPE_TEXT, 'small');
        $table->add_field('response', XMLDB_TYPE_TEXT, 'small');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table question_savpl_prompts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('qattemptid', XMLDB_KEY_FOREIGN, array('qattemptid'), 'question_attempts', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $field = new xmldb_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'quizid');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'qattemptid');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']);
        $dbman->add_key($table, $key);

        // Define field usecase to be added to question_savpl.
        $table = new xmldb_table('question_savpl');
        $field = new xmldb_field('aisupport', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'disablerun');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('aiteacherprompt', XMLDB_TYPE_TEXT, 'small');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('disableevaluate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'aiteacherprompt');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('trywithoutgrade', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'disableevaluate');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2024101700) {
        $table = new xmldb_table('question_savpl');
        $field = new xmldb_field('ainumrequests', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'aiteacherprompt');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
