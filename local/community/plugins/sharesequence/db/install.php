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
 * @package   community_sharesequence
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_community_sharesequence_install() {

    global $DB;
    $dbman = $DB->get_manager();

    if (!$dbman->table_exists('community_sharesequence_task')) {
        $table = new xmldb_table('community_sharesequence_task');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('activities', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('metadata', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $indexuserid = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        if (!$dbman->index_exists($table, $indexuserid)) {
            $dbman->add_index($table, $indexuserid);
        }

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        if (!$dbman->index_exists($table, $indexsectionid)) {
            $dbman->add_index($table, $indexsectionid);
        }
    }

    if (!$dbman->table_exists('community_sharesequence_shr')) {
        $table = new xmldb_table('community_sharesequence_shr');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('seqid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('useridfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        $indexseqid = new xmldb_index('seqid', XMLDB_INDEX_NOTUNIQUE, array('seqid'));
        if (!$dbman->index_exists($table, $indexseqid)) {
            $dbman->add_index($table, $indexseqid);
        }

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        if (!$dbman->index_exists($table, $indexcourseid)) {
            $dbman->add_index($table, $indexcourseid);
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

    // Insert section metadata.
    // History.
    \local_metadata\mcontext::section()->add_field()->text('shistory', 'היסטורית קוד לרצפי פעילויות', [
            'description' => '<p dir="rtl" style="text-align: right;">רשימת קוד שאלה IDNUMBER=QID , מופרד בפסיק. בכל פעם שהשאלה הועתקה.<br></p>',
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Lang.
    \local_metadata\mcontext::section()->add_field()->menu('slang', 'שפה', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'defaultdata' => 'עברית',
            'param1' => ['עברית', 'ערבית', 'אנגלית'],
    ]);

    // Levelactivity.
    \local_metadata\mcontext::section()->add_field()->menu('slevelactivity', 'רמת קושי', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'param1' => ['קל', 'בינוני', 'קשה'],
    ]);

    // Validation.
    \local_metadata\mcontext::section()->add_field()->multimenu('svalidation', 'תיקוף', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
            'param1' => ['התנסתי עם התלמידים', 'מורה אחר בדק את הפעילות', 'לא נבדק', 'נבדק ע"י צוות הפיתוח'],
    ]);

    // User id.
    \local_metadata\mcontext::section()->add_field()->text('suserid', 'מחברים', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Image.
    \local_metadata\mcontext::section()->add_field()->fileupload('simagesequence', 'תמונה לרצפי פעילויות', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
    ]);

    // Sequence description.
    \local_metadata\mcontext::section()->add_field()->textarea('sequencedescription', 'הערות המורה לרצפי פעילויות', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 1,
    ]);

    // Originality.
    \local_metadata\mcontext::section()->add_field()->text('soriginality', 'מקור תוכן לרצפי פעילויות', [
            'description' => '<p dir="rtl" style="text-align: right;">המקור ממנו הועתקה השאלה, במידה והמורה המשתף לא יצר/ה בעצמו/ה.<br></p>',
            'required' => 1,
            'locked' => 0,
            'visible' => 1,
            'signup' => 1,
    ]);

    // Date create sequence.
    \local_metadata\mcontext::section()->add_field()->text('screated_at', 'תאריך יצירה', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // ID.
    \local_metadata\mcontext::section()->add_field()->text('sID', 'מזהה ייחודי לרצפי פעילויות', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

    // Version.
    \local_metadata\mcontext::section()->add_field()->text('sversion', 'גרסה', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    // Version history.
    \local_metadata\mcontext::section()->add_field()->textarea('sversionhistory', 'היסטוריה של עדכונים גרסאות', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    return true;
}
