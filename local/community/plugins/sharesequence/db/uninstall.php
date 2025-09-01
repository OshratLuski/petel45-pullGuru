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
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     community_sharesequence
 * @category    upgrade
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure.
 */
function xmldb_community_sharesequence_uninstall() {
    global $DB;
    $dbman = $DB->get_manager();

    $table = new xmldb_table('community_sharesequence_task');
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    $table = new xmldb_table('community_sharesequence_shr');
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    return true;
}
