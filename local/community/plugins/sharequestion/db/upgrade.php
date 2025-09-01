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
 * @package     community_sharequestion
 * @category    upgrade
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_community_sharequestion_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020110804) {
        $table = new xmldb_table('community_sharequestion_task');
        $field = new xmldb_field('targetsectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'targetcatid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $indextargetsectionid = new xmldb_index('targetsectionid', XMLDB_INDEX_NOTUNIQUE, array('targetsectionid'));
        if (!$dbman->index_exists($table, $indextargetsectionid)) {
            $dbman->add_index($table, $indextargetsectionid);
        }

    }

    if ($oldversion < 2020110805) {

        \local_metadata\mcontext::question()->add_field()->text('authorhistory', 'היסטורית עורכים', [
                'description' => '<p dir="rtl" style="text-align: right;">רשימת קוד משתמש של עורכים, מופרד בפסיק. בכל פעם שהשאלה הועתקה.<br></p>',
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::question()->add_field()->text('qidhistory', 'היסטורית קוד שאלה', [
                'description' => '<p dir="rtl" style="text-align: right;">רשימת קוד שאלה IDNUMBER=QID , מופרד בפסיק. בכל פעם שהשאלה הועתקה.<br></p>',
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::question()->add_field()->text('questioncategoryid', 'היסטורית קוד קטגורית שאלה', [
                'description' => '<p dir="rtl" style="text-align: right;">רשימת קוד קטגורית שאלה, מופרד בפסיק. בכל פעם שהשאלה הועתקה.<br></p>',
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::question()->add_field()->menu('lang', 'שפה', [
                'required' => 1,
                'locked' => 0,
                'visible' => 2,
                'signup' => 1,
                'defaultdata' => 'עברית',
                'param1' => ['עברית', 'ערבית'],
        ]);

        \local_metadata\mcontext::question()->add_field()->menu('qdifficultylevel', 'רמת קושי', [
                'required' => 1,
                'locked' => 0,
                'visible' => 2,
                'signup' => 1,
                'param1' => ['קל', 'בינוני', 'קשה'],
        ]);

        \local_metadata\mcontext::question()->add_field()->menu('qduration', 'משך זמן שאלה', [
                'description' => '<p dir="rtl" style="text-align: right;">משך הזמן המוערך על ידי המורה לביצוע השאלה על ידי התלמיד<br></p>',
                'required' => 1,
                'locked' => 0,
                'visible' => 2,
                'signup' => 1,
                'param1' => ['2 דקות', '5 דקות', '10 דקות', 'יותר מ 10 דקות'],
        ]);

        \local_metadata\mcontext::question()->add_field()->text('qsource', 'מקור תוכן השאלה', [
                'description' => '<p dir="rtl" style="text-align: right;">המקור ממנו הועתקה השאלה, במידה והמורה המשתף לא יצר/ה בעצמו/ה.<br></p>',
                'required' => 1,
                'locked' => 0,
                'visible' => 1,
                'signup' => 1,
        ]);
    }

    if ($oldversion < 2020110807) {
        \local_metadata\mcontext::question()->add_field()->text('quserid', 'מחברים', [
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020110808) {
        \local_metadata\mcontext::question()->add_field()->checkbox('qhidden', 'מוסתר', [
                'required' => 0,
                'locked' => 1,
                'visible' => 1,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020110809) {
        if (!$dbman->table_exists('community_sharequestion_shr')) {
            $table = new xmldb_table('community_sharequestion_shr');

            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('qid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('useridfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);

            $indexqid = new xmldb_index('qid', XMLDB_INDEX_NOTUNIQUE, array('qid'));
            if (!$dbman->index_exists($table, $indexqid)) {
                $dbman->add_index($table, $indexqid);
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
    }

    if ($oldversion < 2020110819) {
        // Version.
        \local_metadata\mcontext::question()->add_field()->text('qversion', 'גרסה', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);

        // Version history.
        \local_metadata\mcontext::question()->add_field()->textarea('qversionhistory', 'היסטוריה של עדכוני גרסאות', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020110824) {
        \local_metadata\mcontext::question()->add_field()->text('qid', 'מזהה שאלה ייחודי', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2020110825) {
        \local_metadata\mcontext::question()->add_field()->textarea('qexpectedanswer', 'תשובה המצופה', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::question()->add_field()->textarea('qteachercomments', 'הערות המורה', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    return true;
}
