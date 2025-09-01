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
 * Upgrade code for petel message processor
 *
 * @package     message_petel
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the petel message processor
 *
 * @param int $oldversion The version that we are upgrading from
 */
function xmldb_message_petel_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019072503) {

        $exist = $DB->get_record('message_processors', array('name' => 'petel'));
        if (!$exist) {
            $provider = new stdClass();
            $provider->name = 'petel';
            $DB->insert_record('message_processors', $provider);
        }

        // Enable output petel and disable default output popup.
        $petelproc = $DB->get_record('message_processors', array('name' => 'petel'));
        $petelproc->enabled = 1;
        $DB->update_record('message_processors', $petelproc);
        $popupproc = $DB->get_record('message_processors', array('name' => 'popup'));
        $popupproc->enabled = 0;
        $DB->update_record('message_processors', $popupproc);

        if (!$dbman->table_exists('message_petel')) {
            $table = new xmldb_table('message_petel');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, true, null, null);
            $table->add_field('isread', XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);

            $index = new xmldb_index('isread', XMLDB_INDEX_NOTUNIQUE, array('isread'));
            $dbman->add_index($table, $index);

            $index = new xmldb_index('messageid-isread', XMLDB_INDEX_NOTUNIQUE, array('messageid', 'isread'));
            $dbman->add_index($table, $index);
        }

        if (!$dbman->table_exists('message_petel_notifications')) {
            $table = new xmldb_table('message_petel_notifications');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('notificationid', XMLDB_TYPE_INTEGER, '10', null, true, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);

            $index = new xmldb_index('notificationid', XMLDB_KEY_FOREIGN, array('notificationid'));
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2019072503, 'message', 'petel');
    }

    if ($oldversion < 2019072506) {
        if ($dbman->table_exists('message_petel_notifications')) {
            $table = new xmldb_table('message_petel_notifications');

            $field = new xmldb_field('popupremoved', XMLDB_TYPE_INTEGER, '3', null, false, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2019072506, 'message', 'petel');
    }

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
