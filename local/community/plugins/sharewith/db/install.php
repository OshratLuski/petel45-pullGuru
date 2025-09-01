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
 * @package     community_sharewith
 * @category    upgrade
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_community_sharewith_install() {
    global $DB;
    $dbman = $DB->get_manager();

    if (!$dbman->table_exists('community_sharewith_task')) {
        $table = new xmldb_table('community_sharewith_task');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('sourceuserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourcecourseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourcesectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sourceactivityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('metadata', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('error', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    if (!$dbman->table_exists('community_sharewith_shared')) {
        $table = new xmldb_table('community_sharewith_shared');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('useridfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('restoreid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('complete', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('source', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);
    }

    \local_metadata\mcontext::module()->add_field()->menu('durationactivity', 'משך זמן הפעילות', [
            'visible' => 2,
            'param1' => ['15 דקות', '30 דקות', '45 דקות', '60 דקות', '90 דקות', '120 דקות']
    ]);

    \local_metadata\mcontext::module()->add_field()->multimenu('technicalassessments', 'הערכות טכנית', [
            'visible' => 2,
            'param1' => ['נדרש מקרן|projector', 'הדפסת דפי עזר|page', 'מעבדה|lab_tubes']
    ]);

    \local_metadata\mcontext::module()->add_field()->multimenu('feedback', 'סוג המשוב', [
            'visible' => 2,
            'param1' => ['בדיקה אוטומטית|computer', 'נדרשת בדיקת מורה|teacher_test', 'משולב|combined']
    ]);

    \local_metadata\mcontext::module()->add_field()->multimenu('validation', 'תיקוף', [
            'visible' => 2,
            'param1' => ['התנסתי עם התלמידים', 'מורה אחר בדק את הפעילות', 'לא נבדק', 'נבדק ע"י צוות הפיתוח']
    ]);

    \local_metadata\mcontext::module()->add_field()->text('userid', 'יוצר', [
            'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->checkbox('originality',
            'האם הסתמכתם על פעילויות אחרות בפיתוח הפעילות', [
                    'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->fileupload('imageactivity', 'תמונה', [
            'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->menu('levelactivity', 'רמת קושי של הפעילות', [
            'visible' => 2,
            'param1' => ['קל|easy', 'בינוני|medium', 'קשה|hard']
    ]);

    \local_metadata\mcontext::module()->add_field()->text('sourceurl',
            'רישמו מהיכן המשאב ואם יש קישור אליו, אנא הוסיפו במידה וכן', [
                    'visible' => 2,
            ]);

    \local_metadata\mcontext::module()->add_field()->text('ID', 'מזהה ייחודי למשאב', [
            'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->textarea('teacherremarks', 'הנחיות למורים על הפעילות', [
            'description' => '<p>אנא ספרו למורים אחרים במספר מילים על הפעילות, מטרת הפעילות,&nbsp;ידע קודם הדרוש לביצוע הפעילות, תובנות מהפעלה עם תלמידים והמלצות שלכם להפעלה&nbsp;מוצלחת. המידע יופיע מתחת לכותרת הפריט במאגר המשותף</p>',
            'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->menu('language', 'שפה', [
            'description' => '<p>שפת התוכן של הפעילות</p>',
            'visible' => 2,
            'defaultdata' => 'עברית',
            'param1' => ['עברית', 'ערבית', 'אנגלית', 'רוסית']
    ]);

    \local_metadata\mcontext::module()->add_field()->menu('certificatestamp', 'חותמת אישור', [
            'required' => 1,
            'locked' => 0,
            'visible' => 0,
            'signup' => 0,
            'defaultdata' => 'מורים מציעים|teachers_offer',
            'param1' => ['מורים מציעים|teachers_offer', 'בדיקת עמיתים|tested_by_teachers', 'נבדק על ידי צוות פטל|tested_by_petel'],
    ]);

    \local_metadata\mcontext::module()->add_field()->text('version', 'גרסה', [
            'required' => 1,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::module()->add_field()->textarea('versionhistory', 'היסטורית עדכונים בתוכן הפעילות', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::module()->add_field()->text('translatemid', 'רכיב זה הוא תרגום של (קוד MID)', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::module()->add_field()->text('translatedby', 'פעילות תורגמה על ידי (ID)', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::module()->add_field()->text('sourcemid', 'פעילות המקורי (ID)', [
            'required' => 0,
            'locked' => 0,
            'visible' => 2,
            'signup' => 0,
    ]);

    return true;
}
