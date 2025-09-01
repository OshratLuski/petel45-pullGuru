<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_codescore
 * @category    upgrade
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once (__DIR__ . '/upgradelib.php');

/**
 * Execute mod_codescore upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_codescore_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2024022005) {
        $table = new xmldb_table('codescore_attempts');
        $field = new \xmldb_field('feedback', XMLDB_TYPE_TEXT, '10', null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2024022200) {
        $table = new xmldb_table('codescore');
        $field = new \xmldb_field('feedbacklang', XMLDB_TYPE_TEXT, '10', null, null, null, null, null);
        $field2 = new \xmldb_field('showfeedback', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
    }
    if ($oldversion < 2024031900) {
        $table = new xmldb_table('codescore');
        $attempts = new xmldb_table('codescore_attempts');
        $field = new \xmldb_field('rubricsobject', XMLDB_TYPE_TEXT, '1000', null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($attempts, $field)) {
            $dbman->add_field($attempts, $field);
        }
    }
    if ($oldversion < 2024032004) {
        global $DB;
        $attempts = $DB->get_records('codescore_attempts');
        foreach ($attempts as $attempt) {
            $cm = get_coursemodule_from_id('codescore', $attempt->codescore);
            $codescore = $DB->get_record('codescore', ['id' => $cm->instance]);
            $rubrics = array(
                [
                    'name' => "Syntax grade",
                    'enabled' => 1,
                    'maxgrade' => $codescore->syntaxgrading ? $codescore->syntaxgrading : 25,
                    'grade' => $attempt->syntaxgrading,
                    'prompt' => "Grade for student's code syntax"
                ],
                [
                    'name' => "Output grade",
                    'enabled' => 1,
                    'maxgrade' => $codescore->outputgrading ? $codescore->outputgrading : 25,
                    'grade' => $attempt->outputgrading,
                    'prompt' => "Grade for the output of the code"
                ],
                [
                    'name' => "Problem solution grade",
                    'enabled' => 1,
                    'maxgrade' => $codescore->problemsolutiongrading ? $codescore->problemsolutiongrading : 25,
                    'grade' => $attempt->problemsolutiongrading,
                    'prompt' => "Grade for correct code solution"
                ],
                [
                    'name' => "All cases coverage grade",
                    'enabled' => 1,
                    'maxgrade' => $codescore->allcasesgrading ? $codescore->allcasesgrading : 25,
                    'grade' => $attempt->allcasesgrading,
                    'prompt' => "Grade for code covering all cases"
                ]
            );
            if ($attempt->syntaxgrading) {
                $attempt->rubricsobject = json_encode($rubrics);
                $DB->update_record('codescore_attempts', $attempt);
            }
        }
        $table = new xmldb_table('codescore');
        $attempts = new xmldb_table('codescore_attempts');
        $syntax = new \xmldb_field('syntaxgrading');
        $output = new \xmldb_field('outputgrading');
        $problemsolution = new \xmldb_field('problemsolutiongrading');
        $allcases = new \xmldb_field('allcasesgrading');

        if ($dbman->field_exists($table, $syntax)) {
            $dbman->drop_field($table, $syntax);
        }
        if ($dbman->field_exists($table, $output)) {
            $dbman->drop_field($table, $output);
        }
        if ($dbman->field_exists($table, $problemsolution)) {
            $dbman->drop_field($table, $problemsolution);
        }
        if ($dbman->field_exists($table, $allcases)) {
            $dbman->drop_field($table, $allcases);
        }
        
        if ($dbman->field_exists($attempts, $syntax)) {
            $dbman->drop_field($attempts, $syntax);
        }
        if ($dbman->field_exists($attempts, $output)) {
            $dbman->drop_field($attempts, $output);
        }
        if ($dbman->field_exists($attempts, $problemsolution)) {
            $dbman->drop_field($attempts, $problemsolution);
        }
        if ($dbman->field_exists($attempts, $allcases)) {
            $dbman->drop_field($attempts, $allcases);
        }
    }

    return true;
}
