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
 * Local plugin "staticpage" - Upgrade plugin tasks
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Process DB upgrade
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_community_sharewith_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020081106) {

        $table = new xmldb_table('community_sharewith_task');

        // Changing type of field isdeleted on table pdfannotator_comments to int.
        $field = new xmldb_field('metadata', XMLDB_TYPE_TEXT, 'long', null, null, null, null, 'categoryid');
        // Launch change of type for field isdeleted.
        $dbman->change_field_type($table, $field);

        upgrade_plugin_savepoint(true, 2020081106, 'community', 'sharewith');
    }

    if ($oldversion < 2020081107) {
        \local_metadata\mcontext::module()->add_field()->text('translatemid', 'רכיב זה הוא תרגום של (קוד MID)', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020081108) {

        $table = new xmldb_table('community_sharewith_task');

        $field = new xmldb_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null, 'status');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020081108, 'community', 'sharewith');
    }

    if ($oldversion < 2020081109) {
        \local_metadata\mcontext::module()->add_field()->text('translatedby', 'פעילות תורגמה על ידי (ID)', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020081110) {
        \local_metadata\mcontext::module()->add_field()->text('sourcemid', 'פעילות המקורי (ID)', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    return true;
}
