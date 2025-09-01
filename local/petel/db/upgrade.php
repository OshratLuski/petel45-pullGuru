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
 * Local plugin "petel" custom services - Upgrade plugin tasks
 *
 * @package    local_petel
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_petel_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Fetch documents from documents directory and put them into the new documents filearea.
    if ($oldversion < 2017061701) {
        if (!$dbman->table_exists('applets_store')) {
            $table = new xmldb_table('applets_store');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('appletid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
            $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2017061701, 'local', 'petel');

    }

    // Store statistics of time spent by user on the system.
    // Based on user event diff mdl_logstore_standard_log that are less then 2h.
    if ($oldversion < 2017061719) {
        if (!$dbman->table_exists('stats_user_timespent')) {
            $table = new xmldb_table('stats_user_timespent');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
            $table->add_field('timespent', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2017061719, 'local', 'petel');

    }

    if ($oldversion < 2017061720) {
        // Metadata fields.
        \local_metadata\mcontext::module()->add_field()->text('version', 'גרסה', [
                'required' => 1,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::module()->add_field()->textarea('versionhistory', 'היסטוריה של עדכונים גרסאות', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2017061727) {

        if (!$dbman->table_exists('qtypes_favorites')) {
            $table = new xmldb_table('qtypes_favorites');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('qtypes', XMLDB_TYPE_TEXT, null, null, null, null, null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2017061727, 'local', 'petel');
    }

    if ($oldversion < 2017061729) {

        $table = new xmldb_table('question');
        $index = new xmldb_index('stamp', XMLDB_INDEX_NOTUNIQUE, array('stamp'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_plugin_savepoint(true, 2017061729, 'local', 'petel');
    }

    if ($oldversion < 2022072605) {

        if (!$dbman->table_exists('social_relationships')) {

            $table = new xmldb_table('social_relationships');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid_watching', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('userid_feedback', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('points', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);

            $indexusers = new xmldb_index('users', XMLDB_INDEX_NOTUNIQUE, array('userid_watching', 'userid_feedback'));
            if (!$dbman->index_exists($table, $indexusers)) {
                $dbman->add_index($table, $indexusers);
            }
        }

        upgrade_plugin_savepoint(true, 2022072605, 'local', 'petel');
    }

    if ($oldversion < 2022072607) {

        if (!$dbman->table_exists('stats_update_course_metadata')) {

            $table = new xmldb_table('stats_update_course_metadata');
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('counter', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);

            $indexusers = new xmldb_index('stats_update_course_metadata', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'counter'));
            if (!$dbman->index_exists($table, $indexusers)) {
                $dbman->add_index($table, $indexusers);
            }
        }

        upgrade_plugin_savepoint(true, 2022072607, 'local', 'petel');
    }

    if ($oldversion < 2024112402) {

        $table = new xmldb_table('abuse_ip');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ip', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('confidence', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeupdated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Save the upgrade point.
        upgrade_plugin_savepoint(true, 2024112402, 'local', 'petel');
    }


    return true;
}
