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
 * @package     block_oer_items
 * @category    upgrade
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_block_oer_items_install() {
    global $DB;

    $dbman = $DB->get_manager();

    $table = new xmldb_table('block_oer_items');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    $dbman->create_table($table);

    // Set indexes.
    $indexuserid = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $dbman->add_index($table, $indexuserid);

    $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    $dbman->add_index($table, $indexcourseid);

    return true;
}
