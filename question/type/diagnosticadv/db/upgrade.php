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
 * Diagnosticadv question type upgrade code.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_diagnosticadv_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023110100) {

        // Define field anonymous to be added to qtype_diagnosticadv_options.
        $table = new xmldb_table('qtype_diagnosticadv_options');
        $field = new xmldb_field('anonymous', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'usecase');

        // Conditionally launch add field anonymous.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('qtype_diagnosticadv_answers');

        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'custom');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field questiontextformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Plugin diagnosticadv savepoint reached.
        upgrade_plugin_savepoint(true, 2023110100, 'qtype', 'diagnosticadv');
    }

    if ($oldversion < 2023110108) {

        // Define field aianalytics to be added to qtype_diagnosticadv_options.
        $table = new xmldb_table('qtype_diagnosticadv_options');
        $field = new xmldb_field('aianalytics', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'anonymous');

        // Conditionally launch add field aianalytics.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field promt to be added to qtype_diagnosticadv_options.
        $field = new xmldb_field('promt', XMLDB_TYPE_TEXT, null, null, null, null, null, 'aianalytics');

        // Conditionally launch add field promt.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field temperature to be added to qtype_diagnosticadv_options.
        $field = new xmldb_field('temperature', XMLDB_TYPE_NUMBER, '15, 5', null, null, null, null, 'promt');

        // Conditionally launch add field temperature.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Plugin diagnosticadv savepoint reached.
        upgrade_plugin_savepoint(true, 2023110108, 'qtype', 'diagnosticadv');
    }

    if ($oldversion < 2023110109) {

        // Define field aianalytics to be added to qtype_diagnosticadv_options.
        $table = new xmldb_table('qtype_diagnosticadv_options');
        $field = new xmldb_field('teacherdesc', XMLDB_TYPE_TEXT, null, null, null, null, null, 'required');

        // Conditionally launch add field aianalytics.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Plugin diagnosticadv savepoint reached.
        upgrade_plugin_savepoint(true, 2023110109, 'qtype', 'diagnosticadv');
    }

    if ($oldversion < 2025031000) {

        // Define field aianalytics to be added to qtype_diagnosticadv_options.
        $table = new xmldb_table('qtype_diagnosticadv_options');
        $field = new xmldb_field('teacherdesc', XMLDB_TYPE_TEXT, null, null, null, null, null, 'required');

        // Conditionally launch add field aianalytics.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Plugin diagnosticadv savepoint reached.
        upgrade_plugin_savepoint(true, 2025031000, 'qtype', 'diagnosticadv');
    }

    if ($oldversion < 2025031002) {

        $sql = "SELECT qa.id
          FROM {question_answers} qa
          JOIN {question} q ON qa.question = q.id
         WHERE qa.answerformat = 0 AND q.qtype = :qtype";
        $params = ['qtype' => 'diagnosticadv'];

        $rows = $DB->get_records_sql($sql, $params);
        foreach ($rows as $row) {
            $DB->set_field('question_answers', 'answerformat', 1, ['id' => $row->id]);
        }

        upgrade_plugin_savepoint(true, 2025031002, 'qtype', 'diagnosticadv');
    }

    return true;
}
