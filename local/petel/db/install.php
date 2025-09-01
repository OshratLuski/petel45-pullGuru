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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_petel
 * @category    upgrade
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_petel_install() {

    global $DB;
    $dbman = $DB->get_manager();

    $table = new xmldb_table('security_sms');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('ip', XMLDB_TYPE_CHAR, '20', null, null, null, null);
    $table->add_field('count', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $dbman->create_table($table);

    $table = new xmldb_table('applets_store');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('appletid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $dbman->create_table($table);

    $table = new xmldb_table('stats_user_timespent');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_field('timespent', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $dbman->create_table($table);

    $table = new xmldb_table('qtypes_favorites');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('qtypes', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $dbman->create_table($table);

    $table = new xmldb_table('question');
    $index = new xmldb_index('stamp', XMLDB_INDEX_NOTUNIQUE, array('stamp'));
    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index);
    }

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

    $table = new xmldb_table('abuse_ip');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('ip', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
    $table->add_field('country', XMLDB_TYPE_CHAR, '100', null, null, null, null);
    $table->add_field('confidence', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
    $table->add_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
    $dbman->create_table($table);

    return true;
}
