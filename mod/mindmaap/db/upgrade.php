<?php
// This file is part of eMailTest plugin for Moodle - http://moodle.org/
//
// eMailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// eMailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with eMailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code for the invites local plugin.
 *
 * @param int $oldversion - the version we are upgrading from.
 * @return bool result
 */
function xmldb_mindmaap_upgrade($oldversion) {

    // Moodle v2.6.0 release upgrade line.
    // Upgrade steps below.
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019080808) {
        $table = new xmldb_table('mindmaap');

        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 2);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2019080811) {
        $table = new xmldb_table('mindmaap');

        $field = new xmldb_field('activityid');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    }
    return true;
}