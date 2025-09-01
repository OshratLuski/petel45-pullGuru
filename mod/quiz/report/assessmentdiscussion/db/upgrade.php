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
 * Report plugin "quiz_assessmentdiscussion" - Upgrade plugin tasks
 *
 * @package     quiz_assessmentdiscussion
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_quiz_assessmentdiscussion_upgrade($oldversion) {

    global $DB, $PAGE;
    $dbman = $DB->get_manager();

    if ($oldversion < 2022121518) {

        // Drop old table.
        if ($dbman->table_exists('assessmentdiscussion_discus')) {
            $table = new xmldb_table('assessmentdiscussion_discus');
            $dbman->drop_table($table);
        }

        $table = new xmldb_table('assessmentdiscussion_discus');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('qid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('selecteduserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('attemptid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexuserid = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $dbman->add_index($table, $indexuserid);

        $indexcmid = new xmldb_index('cmid', XMLDB_INDEX_NOTUNIQUE, array('cmid'));
        $dbman->add_index($table, $indexcmid);

        $indexgroupid = new xmldb_index('groupid', XMLDB_INDEX_NOTUNIQUE, array('groupid'));
        $dbman->add_index($table, $indexgroupid);

        $indexqid = new xmldb_index('qid', XMLDB_INDEX_NOTUNIQUE, array('qid'));
        $dbman->add_index($table, $indexqid);

        $indexselecteduserid = new xmldb_index('selecteduserid', XMLDB_INDEX_NOTUNIQUE, array('selecteduserid'));
        $dbman->add_index($table, $indexselecteduserid);

        $indexattemptid = new xmldb_index('attemptid', XMLDB_INDEX_NOTUNIQUE, array('attemptid'));
        $dbman->add_index($table, $indexattemptid);
    }

    return true;
}
