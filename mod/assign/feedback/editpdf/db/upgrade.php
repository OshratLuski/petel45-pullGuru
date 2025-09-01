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
 * Upgrade code for the feedback_editpdf module.
 *
 * @package   assignfeedback_editpdf
 * @copyright 2013 Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * EditPDF upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignfeedback_editpdf_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2022112801) {
        $task = new \assignfeedback_editpdf\task\remove_orphaned_editpdf_files();
        \core\task\manager::queue_adhoc_task($task);

        upgrade_plugin_savepoint(true, 2022112801, 'assignfeedback', 'editpdf');
    }

    if ($oldversion < 2024100701) {
        // Define table assignfeedback_editpdf_htcm to be created.
        $table = new xmldb_table('assignfeedback_editpdf_htcm');

        // Adding fields to table assignfeedback_editpdf_rot.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gradeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('x', XMLDB_TYPE_INTEGER, '10', null, false, null, '0');
        $table->add_field('y', XMLDB_TYPE_INTEGER, '10', null, false, null, '0');
        $table->add_field('width', XMLDB_TYPE_INTEGER, '10', null, false, null, '120');
        $table->add_field('rawtext', XMLDB_TYPE_TEXT, null, null, false, null, null);
        $table->add_field('pageno', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('draft', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table assignfeedback_editpdf_rot.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('gradeid', XMLDB_KEY_FOREIGN, ['gradeid'], 'assign_grades', ['id']);

        // Adding indexes to table assignfeedback_editpdf_rot.
        $table->add_index('gradeid_pageno', XMLDB_INDEX_NOTUNIQUE, ['gradeid', 'pageno']);

        // Conditionally launch create table for assignfeedback_editpdf_rot.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Editpdf savepoint reached.
        upgrade_plugin_savepoint(true, 2024100701, 'assignfeedback', 'editpdf');
    }

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.5.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
