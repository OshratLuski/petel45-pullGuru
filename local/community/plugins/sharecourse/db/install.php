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
 * @package     community_sharecourse
 * @category    upgrade
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_community_sharecourse_install() {

    global $DB;
    $dbman = $DB->get_manager();

    if (!$dbman->table_exists('community_sharecourse_shr')) {
        $table = new xmldb_table('community_sharecourse_shr');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('useridfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        if (!$dbman->index_exists($table, $indexcourseid)) {
            $dbman->add_index($table, $indexcourseid);
        }

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        if (!$dbman->index_exists($table, $indexcatid)) {
            $dbman->add_index($table, $indexcatid);
        }

        $indexuseridto = new xmldb_index('useridto', XMLDB_INDEX_NOTUNIQUE, array('useridto'));
        if (!$dbman->index_exists($table, $indexuseridto)) {
            $dbman->add_index($table, $indexuseridto);
        }

        $indexuseridfrom = new xmldb_index('useridfrom', XMLDB_INDEX_NOTUNIQUE, array('useridfrom'));
        if (!$dbman->index_exists($table, $indexuseridfrom)) {
            $dbman->add_index($table, $indexuseridfrom);
        }

        $indexmessageid = new xmldb_index('messageid', XMLDB_INDEX_NOTUNIQUE, array('messageid'));
        if (!$dbman->index_exists($table, $indexmessageid)) {
            $dbman->add_index($table, $indexmessageid);
        }
    }

    if (!$dbman->table_exists('community_sharecourse_task')) {
        $table = new xmldb_table('community_sharecourse_task');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('metadata', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    // Metadata fields.
    // ID.
    \local_metadata\mcontext::course()->add_field()->text('cID', 'מזהה ייחודי לקורס', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Hidden.
    \local_metadata\mcontext::course()->add_field()->checkbox('chidden', 'מוסתר', [
            'required' => 0,
            'locked' => 1,
            'visible' => 1,
            'signup' => 0,
            'defaultdata' => 1,
    ]);

    // Version.
    \local_metadata\mcontext::course()->add_field()->text('cversion', 'גרסה', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    // Version history.
    \local_metadata\mcontext::course()->add_field()->textarea('cversionhistory', 'היסטוריה של עדכונים גרסאות', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::course()->add_field()->text('cfullname', 'שם הקורס במאגר משותף', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::course()->add_field()->textarea('cdescription', 'תיאור הקורס במאגר משותף', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // User id.
    \local_metadata\mcontext::course()->add_field()->text('cuserid', 'מחברים', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Courses or categories.
    \local_metadata\mcontext::course()->add_field()->text('csubject', 'נושא לימוד/מולטי קורס', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Date share course.
    \local_metadata\mcontext::course()->add_field()->text('cshared_at', 'תאריך שיתוף', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::course()->add_field()->text('csource', 'מקור תוכן קורס', [
            'description' => '<p dir="rtl" style="text-align: right;">המקור ממנו הועתקה הקורס, במידה והמורה המשתף לא יצר/ה בעצמו/ה.<br></p>',
            'required' => 1,
            'locked' => 0,
            'visible' => 1,
            'signup' => 1,
    ]);

    \local_metadata\mcontext::course()->add_field()->fileupload('cimage', 'תמונת קורס', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
    ]);

    // Lang.
    \local_metadata\mcontext::course()->add_field()->menu('clang', 'שפה', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'defaultdata' => 'עברית',
            'param1' => ['עברית', 'ערבית', 'אנגלית'],
    ]);

    // Class.
    \local_metadata\mcontext::course()->add_field()->multimenu('cclass', 'שכבת גיל/כיתה', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'param1' => ['שכבה א', 'שכבה ב', 'שכבה ג', 'שכבה ד', 'שכבה ה', 'שכבה ו', 'שכבה ז', 'שכבה ח', 'שכבה ט', 'שכבה י',
                    'שכבה אי', 'שכבה יב'],
    ]);

    \local_metadata\mcontext::course()->add_field()->checkbox('callowfullcopy',
            'האם לאפשר להעתיק קורס שלם לסביבה אישית של מורה אחר?', [
                    'locked' => 0,
                    'visible' => 2,
                    'signup' => 1,
            ]);

    // Class level.
    \local_metadata\mcontext::course()->add_field()->multimenu('cclasslevel', ' רמת הכיתה', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'param1' => ['הקבצה א', 'הקבצה ב', 'הקבצה ג'],
    ]);

    return true;
}
