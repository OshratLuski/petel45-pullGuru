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
 * @package     community_oer
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_community_oer_uninstall() {

    global $DB;
    $dbman = $DB->get_manager();

    if ($dbman->table_exists('community_oer_activity')) {
        $table = new xmldb_table('community_oer_activity');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_question')) {
        $table = new xmldb_table('community_oer_question');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_sequence')) {
        $table = new xmldb_table('community_oer_sequence');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_course')) {
        $table = new xmldb_table('community_oer_course');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_wht_new')) {
        $table = new xmldb_table('community_oer_wht_new');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_log')) {
        $table = new xmldb_table('community_oer_log');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_reviews')) {
        $table = new xmldb_table('community_oer_reviews');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oerctlg_rvw_cmmnts')) {
        $table = new xmldb_table('community_oerctlg_rvw_cmmnts');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_er_imgs')) {
        $table = new xmldb_table('community_oer_er_imgs');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_oer_errors')) {
        $table = new xmldb_table('community_oer_errors');
        $dbman->drop_table($table);
    }

    return true;
}
