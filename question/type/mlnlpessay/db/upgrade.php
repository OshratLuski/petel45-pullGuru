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
 * Algebra question type upgrade code.
 *
 * @package    qtype_mlnlpessay
 * @copyright  Dor Herbesman - Devlion team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_mlnlpessay_upgrade($oldversion) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020061504) {
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('categoriesweight', XMLDB_TYPE_TEXT, '10', XMLDB_UNSIGNED, null, null, null, 'filetypeslist');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2020061504, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2020061505) {
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('categoriesweightteacher', XMLDB_TYPE_TEXT, '10', XMLDB_UNSIGNED, null, null, null,
                'filetypeslist');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061505, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2020061512) {
        $table = new xmldb_table('qtype_mlnlpessay_response');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('questionattemptid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quizattemptid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('pythonresponse', XMLDB_TYPE_TEXT, 10, null, false, null,);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexqid = new xmldb_index('questionid', XMLDB_INDEX_NOTUNIQUE, array('questionid'));
        $dbman->add_index($table, $indexqid);

        $indexqaid = new xmldb_index('questionattemptid', XMLDB_INDEX_UNIQUE, array('questionattemptid'));
        $dbman->add_index($table, $indexqaid);

        $indexquizaid = new xmldb_index('quizattemptid', XMLDB_INDEX_NOTUNIQUE, array('quizattemptid'));
        $dbman->add_index($table, $indexquizaid);

        upgrade_plugin_savepoint(true, 2020061512, 'qtype', 'mlnlpessay');

    }

    if ($oldversion < 2022052001) {
        $value = get_string('svgfeedbacktemplate', 'qtype_mlnlpessay');
        set_config('svgfeedbacktemplate', $value, 'qtype_mlnlpessay');

        upgrade_plugin_savepoint(true, 2022052001, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2022061405) {

        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'categoriesweight');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('qtype_mlnlpessay_response');

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'pythonresponse');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('qtype_mlnlpessay_task');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2022061405, 'qtype', 'mlnlpessay');

    }

    if ($oldversion < 2023042800) {

        // Define field minwordlimit to be added to qtype_essay_options.
        $table = new xmldb_table('qtype_mlnlpessay_options');
        $field = new xmldb_field('minwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'responsefieldlines');

        // Conditionally launch add field minwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxwordlimit', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'minwordlimit');

        // Conditionally launch add field maxwordlimit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxbytes', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, '0', 'responsetemplateformat');

        // Conditionally launch add field maxbytes.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Essay savepoint reached.
        upgrade_plugin_savepoint(true, 2023042800, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042801) {

        $table = new xmldb_table('qtype_mlnlpessay_categories');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255);
        $table->add_field('modelid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('tagid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('description', XMLDB_TYPE_TEXT);
        $table->add_field('topicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('subtopicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Essay savepoint reached.
        upgrade_plugin_savepoint(true, 2023042801, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042803) {

        $table = new xmldb_table('qtype_mlnlpessay_topics');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255);
        $table->add_field('active', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $table = new xmldb_table('qtype_mlnlpessay_subtopics');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('topicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, 255);
        $table->add_field('active', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $table = new xmldb_table('qtype_mlnlpessay_langs');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, 10);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255);
        $table->add_field('active', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Essay savepoint reached.
        upgrade_plugin_savepoint(true, 2023042803, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042806) {

        $table = new xmldb_table('qtype_mlnlpessay_categories');

        $field = new xmldb_field( 'modelid' );

        // Conditionally launch drop field intro.
        if ($dbman->field_exists( $table, $field )) {
            $dbman->drop_field( $table, $field );
        }

        $field = new xmldb_field( 'tagid' );

        // Conditionally launch drop field intro.
        if ($dbman->field_exists( $table, $field )) {
            $dbman->drop_field( $table, $field );
        }

        $field = new xmldb_field('model', XMLDB_TYPE_CHAR, '255', null, null, null, null,'name');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('tag', XMLDB_TYPE_CHAR, '255', null, null, null, null,'model');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('langid', XMLDB_TYPE_INTEGER, '10', null, true, null, 0,'description');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Essay savepoint reached.
        upgrade_plugin_savepoint(true, 2023042806, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042809) {
        $table = new xmldb_table('qtype_mlnlpessay_categories');

        $field = new xmldb_field( 'topicid' );

        // Conditionally launch drop field intro.
        if ($dbman->field_exists( $table, $field )) {
            $dbman->drop_field( $table, $field );
        }

        $field = new xmldb_field( 'subtopicid' );

        // Conditionally launch drop field intro.
        if ($dbman->field_exists( $table, $field )) {
            $dbman->drop_field( $table, $field );
        }

        $table = new xmldb_table('qtype_mlnlpessay_cattopics');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('topicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $table = new xmldb_table('qtype_mlnlpessay_catsubtopics');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('subtopicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        upgrade_plugin_savepoint(true, 2023042809, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042812) {
        $table = new xmldb_table('qtype_mlnlpessay_categories');
        $field = new xmldb_field('modelid', XMLDB_TYPE_CHAR, '255', null, null, null, null,'name');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023042812, 'qtype', 'mlnlpessay');
    }

    if ($oldversion < 2023042813) {
        $table = new xmldb_table('qtype_mlnlpessay_categories');
        $field = new xmldb_field('disabled', XMLDB_TYPE_INTEGER, '1', null, null, null, 0,'active');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2023042813, 'qtype', 'mlnlpessay');
    }

    return true;
}
