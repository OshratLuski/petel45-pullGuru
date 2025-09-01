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
 * @package     local_redmine
 * @category    upgrade
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_redmine_install() {
    global $DB;

    $dbman = $DB->get_manager();

    // Drop old table.
    if ($dbman->table_exists('local_redmine_chat')) {
        $table = new xmldb_table('local_redmine_chat');
        $dbman->drop_table($table);
    }

    // Chat table.
    $table = new xmldb_table('local_redmine_chat');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('issueid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('message', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    $dbman->create_table($table);

    // Set indexes.
    $indexissueid = new xmldb_index('issueid', XMLDB_INDEX_NOTUNIQUE, array('issueid'));
    $dbman->add_index($table, $indexissueid);

    $indexuserid = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $dbman->add_index($table, $indexuserid);

    return true;
}
