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
 * Essayrubric question type upgrade code.
 *
 * @package    qtype
 * @subpackage essayrubric
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the essayrubric question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_essayrubric_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2021052501) {

        // Define field maxbytes to be added to qtype_essayrubric_options.
        $table = new xmldb_table('qtype_essayrubric_options');
        $field = new xmldb_field('maxbytes', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, '0', 'responsetemplateformat');

        // Conditionally launch add field maxbytes.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Essayrubric savepoint reached.
        upgrade_plugin_savepoint(true, 2021052501, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2021052502) {

        // Define field minwordlimit to be added to qtype_essayrubric_options.
        $table = new xmldb_table('qtype_essayrubric_options');
        $field = new xmldb_field('minwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'responsefieldlines');

        // Conditionally launch add field minwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field maxwordlimit to be added to qtype_essayrubric_options.
        $table = new xmldb_table('qtype_essayrubric_options');
        $field = new xmldb_field('maxwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'minwordlimit');

        // Conditionally launch add field maxwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Essayrubric savepoint reached.
        upgrade_plugin_savepoint(true, 2021052502, 'qtype', 'essayrubric');
    }

    // Automatically generated Moodle v4.0.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2023110104) {

        $table = new xmldb_table('qtype_essayrubric_ind');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('indicatorid', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'id');
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'indicatorid');
        $table->add_field('model', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'name');
        $table->add_field('category', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'model');
        $table->add_field('research', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'category');
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'research');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Essayrubric savepoint reached.
        upgrade_plugin_savepoint(true, 2023110104, 'qtype', 'essayrubric');
    }

// qtype_essayrubric_options

    if ($oldversion < 2023110301) {

        $table = new xmldb_table('qtype_essayrubric_options');
        $field = new xmldb_field('indicators', XMLDB_TYPE_TEXT, null, null, null, null, null, 'filetypeslist');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Essayrubric savepoint reached.
        upgrade_plugin_savepoint(true, 2023110301, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023110303) {
        $table = new xmldb_table('qtype_essayrubric_resp');

        // Check if the table exists and drop it if it does.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Recreate the table with the specified fields.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0');
        $table->add_field('questionattemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0');
        $table->add_field('quizattemptid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0');
        $table->add_field('question', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('answer', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('response', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create the table.
        $dbman->create_table($table);

        // Set indexes.
        $indexqid = new xmldb_index('questionid', XMLDB_INDEX_NOTUNIQUE, array('questionid'));
        $dbman->add_index($table, $indexqid);

        $indexquizaid = new xmldb_index('quizattemptid', XMLDB_INDEX_NOTUNIQUE, array('quizattemptid'));
        $dbman->add_index($table, $indexquizaid);

        // Essayrubric savepoint reached.
        upgrade_plugin_savepoint(true, 2023110303, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023111404) {

        $table = new xmldb_table('qtype_essayrubric_resp');

        $fieldsToAdd = [
            new xmldb_field('isgradestypescalar', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1),
            new xmldb_field('weight', XMLDB_TYPE_INTEGER, '10'),
            new xmldb_field('indicatorid', XMLDB_TYPE_CHAR, '11'),
            new xmldb_field('name', XMLDB_TYPE_TEXT),
            new xmldb_field('type', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('qindid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, '0', 0),
            new xmldb_field('grade', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('checked', XMLDB_TYPE_INTEGER, '10'),
            new xmldb_field('normalizedweight', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('weightedgrade', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('maxmark', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('minfraction', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('maxfraction', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('usageid', XMLDB_TYPE_CHAR, '10'),
            new xmldb_field('slot', XMLDB_TYPE_INTEGER, '10'),
        ];

        $fieldsToRemove = ['response'];

        foreach ($fieldsToRemove as $fieldName) {
            $field = new xmldb_field($fieldName);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        foreach ($fieldsToAdd as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2023111404, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023111405) {

        $table = new xmldb_table('qtype_essayrubric_resp');

        $indexqindid = new xmldb_index('qindid', XMLDB_INDEX_NOTUNIQUE, array('qindid'));
        $dbman->add_index($table, $indexqindid);

        upgrade_plugin_savepoint(true, 2023111405, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023111500) {

        $table = new xmldb_table('qtype_essayrubric_resp');

        $fieldsToRemove = ['grade'];

        foreach ($fieldsToRemove as $fieldName) {
            $field = new xmldb_field($fieldName);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        $fieldsToAdd = [
            new xmldb_field('grade', XMLDB_TYPE_CHAR, '255'),
        ];

        foreach ($fieldsToAdd as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2023111500, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023112101) {

        $table = new xmldb_table('qtype_essayrubric_ind');

        $fieldsToAdd = [
            new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'visible'),
        ];

        foreach ($fieldsToAdd as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2023112101, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023112700) {

        $DB->delete_records('qtype_essayrubric_ind', ['deleted' => 1]);

        upgrade_plugin_savepoint(true, 2023112700, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2023112701) {

        $table = new xmldb_table('qtype_essayrubric_ind');

        $field = new xmldb_field('model', XMLDB_TYPE_TEXT, null, null, null, null, null, 'category');

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023112701, 'qtype', 'essayrubric');
    }


    if ($oldversion < 2024041502) {

        set_config('numberofcategories', 9, 'qtype_essayrubric');

        set_config('category1name_en', 'Causal relationship', 'qtype_essayrubric');
        set_config('category1name_he', 'קשר סיבתי', 'qtype_essayrubric');

        set_config('category2name_en', 'Use of the concept', 'qtype_essayrubric');
        set_config('category2name_he', 'שימוש במושג', 'qtype_essayrubric');

        set_config('category3name_en', 'Element in the chain of events', 'qtype_essayrubric');
        set_config('category3name_he', 'מרכיב בשרשרת האירועים', 'qtype_essayrubric');

        set_config('category4name_en', 'Claim', 'qtype_essayrubric');
        set_config('category4name_he', 'טענה', 'qtype_essayrubric');

        set_config('category5name_en', 'Evidence', 'qtype_essayrubric');
        set_config('category5name_he', 'עדויות/נתונים', 'qtype_essayrubric');

        set_config('category6name_en', 'Reasoning', 'qtype_essayrubric');
        set_config('category6name_he', 'הנמקה', 'qtype_essayrubric');

        set_config('category7name_en', 'Definition', 'qtype_essayrubric');
        set_config('category7name_he', 'הגדרה', 'qtype_essayrubric');

        set_config('category8name_en', 'Counter claim', 'qtype_essayrubric');
        set_config('category8name_he', 'טענה נגדית', 'qtype_essayrubric');

        set_config('category9name_en', 'Rebuttal', 'qtype_essayrubric');
        set_config('category9name_he', 'הפרכת טענה', 'qtype_essayrubric');

        upgrade_plugin_savepoint(true, 2024041502, 'qtype', 'essayrubric');
    }

    if ($oldversion < 2024080900) {

        $sql = "UPDATE {qtype_essayrubric_options}
                SET responsetemplateformat = 1
                WHERE responsetemplate IS NULL";

        $res = $DB->execute($sql);

        $sql = "UPDATE {qtype_essayrubric_options}
                SET responsetemplate = ''
                WHERE responsetemplate IS NULL";

        $res = $DB->execute($sql);

        upgrade_plugin_savepoint(true, 2024080900, 'qtype', 'essayrubric');
    }

    return true;
}
