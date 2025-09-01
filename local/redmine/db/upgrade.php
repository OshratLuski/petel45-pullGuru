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
 * Local plugin "redmine" custom services - Upgrade plugin tasks
 *
 * @package    local_redmine
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_redmine_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2021061903) {

        $val = get_config('theme_petel', 'redminestatus');
        set_config('redminestatus', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redmineurl');
        set_config('redmineurl', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redmineusername');
        set_config('redmineusername', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redminepassword');
        set_config('redminepassword', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redminereporterid');
        set_config('redminereporterid', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redmine_leadoercatalog');
        set_config('redmine_leadoercatalog', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redmine_technopedagogical');
        set_config('redmine_technopedagogical', $val, 'local_redmine');

        $val = get_config('theme_petel', 'allmightymentor');
        set_config('allmightymentor', $val, 'local_redmine');

        $val = get_config('theme_petel', 'redminewatcherbugid');
        set_config('redminewatcherbugid', $val, 'local_redmine');

        $val = get_config('theme_petel', 'supportcourse');
        set_config('supportcourse', $val, 'local_redmine');
    }

    if ($oldversion < 2021061904) {

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
    }

    return true;
}
