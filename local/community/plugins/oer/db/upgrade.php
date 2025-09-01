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
 * Local plugin "community_oer" - Upgrade plugin tasks
 *
 * @package     community_oer
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_community_oer_upgrade($oldversion) {

    global $DB, $PAGE;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019081943) {

        \local_metadata\mcontext::module()->add_field()->menu('certificatestamp', 'חותמת אישור', [
                'required' => 1,
                'locked' => 0,
                'visible' => 0,
                'signup' => 0,
                'defaultdata' => 'מורים מציעים|teachers_offer',
                'param1' => ['מורים מציעים|teachers_offer', 'בדיקת עמיתים|tested_by_teachers', 'בדיקת צוות פטל|tested_by_petel'],
        ]);

        // Build images.
        $PAGE->theme->force_svg_use(1);

        // Cache data module activity.
        $activity = new \community_oer\activity_oer;
        $activity->recalculate_all_activities_in_db_cache();
    }

    if ($oldversion < 2019081949) {

        $row = $DB->get_record('local_metadata_field', ['shortname' => 'certificatestamp', 'contextlevel' => CONTEXT_MODULE]);
        if (!empty($row)) {
            $row->datatype = 'menu';
            $DB->update_record('local_metadata_field', $row);
        }
    }

    if ($oldversion < 2019081954) {
        // Metadata fields.
        \local_metadata\mcontext::module()->add_field()->text('version', 'גרסה', [
                'required' => 1,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);

        \local_metadata\mcontext::module()->add_field()->textarea('versionhistory', 'היסטוריה של עדכונים גרסאות', [
                'required' => 0,
                'locked' => 0,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2019081960) {

        // Sequence module.
        $table = new xmldb_table('community_oer_sequence');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('seqid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recache', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexqid = new xmldb_index('seqid', XMLDB_INDEX_NOTUNIQUE, array('seqid'));
        $dbman->add_index($table, $indexqid);

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        $dbman->add_index($table, $indexcatid);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $dbman->add_index($table, $indexcourseid);

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        $dbman->add_index($table, $indexsectionid);

        $indexrecache = new xmldb_index('recache', XMLDB_INDEX_NOTUNIQUE, array('recache'));
        $dbman->add_index($table, $indexrecache);

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2019081964) {

        $table = new xmldb_table('community_oer_course');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uniqueid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('cid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recache', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexuniqueid = new xmldb_index('uniqueid', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));
        $dbman->add_index($table, $indexuniqueid);

        $indexcid = new xmldb_index('cid', XMLDB_INDEX_NOTUNIQUE, array('cid'));
        $dbman->add_index($table, $indexcid);

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        $dbman->add_index($table, $indexcatid);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $dbman->add_index($table, $indexcourseid);

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        $dbman->add_index($table, $indexsectionid);

        $indexrecache = new xmldb_index('recache', XMLDB_INDEX_NOTUNIQUE, array('recache'));
        $dbman->add_index($table, $indexrecache);

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2021101301) {

        $table = new xmldb_table('community_oerctlg_rvw_rqsts');
        $field = new xmldb_field('cohort', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2021101302) {

        // Course module.
        $table = new xmldb_table('community_oer_course');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uniqueid', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('cid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recache', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexuniqueid = new xmldb_index('uniqueid', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));
        $dbman->add_index($table, $indexuniqueid);

        $indexcid = new xmldb_index('cid', XMLDB_INDEX_NOTUNIQUE, array('cid'));
        $dbman->add_index($table, $indexcid);

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        $dbman->add_index($table, $indexcatid);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $dbman->add_index($table, $indexcourseid);

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        $dbman->add_index($table, $indexsectionid);

        $indexrecache = new xmldb_index('recache', XMLDB_INDEX_NOTUNIQUE, array('recache'));
        $dbman->add_index($table, $indexrecache);

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2021101303) {
        \local_metadata\mcontext::module()->add_field()->checkbox('cresearch', 'מיועד למחקר', [
                'required' => 0,
                'locked' => 1,
                'visible' => 2,
                'signup' => 0,
        ]);
    }

    if ($oldversion < 2021101311) {

        // Get courses.
        $courses = [];
        foreach ($DB->get_records('community_oer_course') as $item) {
            $courses[$item->cid] = $item->cid;
        }

        foreach ($courses as $cid) {
            $cfullname = \local_metadata\mcontext::course()->get($cid, 'cfullname');
            if (empty(trim($cfullname))) {
                $course = get_course($cid);

                // Fullname.
                if (!empty(trim($course->fullname))) {
                    \local_metadata\mcontext::course()->save($cid, 'cfullname', trim($course->fullname));
                }
            }
        }

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2021101322) {
        $table = new xmldb_table('community_oer_activity');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uniqueid', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recache', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexuniqueid = new xmldb_index('uniqueid', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));
        $dbman->add_index($table, $indexuniqueid);

        $indexcmid = new xmldb_index('cmid', XMLDB_INDEX_NOTUNIQUE, array('cmid'));
        $dbman->add_index($table, $indexcmid);

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        $dbman->add_index($table, $indexcatid);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $dbman->add_index($table, $indexcourseid);

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        $dbman->add_index($table, $indexsectionid);

        $indexrecache = new xmldb_index('recache', XMLDB_INDEX_NOTUNIQUE, array('recache'));
        $dbman->add_index($table, $indexrecache);

        // Add linksectionids field to module.
        \local_metadata\mcontext::module()->add_field()->text('linksectionids', 'נושא לימוד/מולטי פעילות', [
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2024012907) {

        $table = new xmldb_table('community_oer_question');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uniqueid', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('qid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('recache', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        $dbman->create_table($table);

        // Set indexes.
        $indexuniqueid = new xmldb_index('uniqueid', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));
        $dbman->add_index($table, $indexuniqueid);

        $indexqid = new xmldb_index('qid', XMLDB_INDEX_NOTUNIQUE, array('qid'));
        $dbman->add_index($table, $indexqid);

        $indexcatid = new xmldb_index('catid', XMLDB_INDEX_NOTUNIQUE, array('catid'));
        $dbman->add_index($table, $indexcatid);

        $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        $dbman->add_index($table, $indexcourseid);

        $indexsectionid = new xmldb_index('sectionid', XMLDB_INDEX_NOTUNIQUE, array('sectionid'));
        $dbman->add_index($table, $indexsectionid);

        $indexrecache = new xmldb_index('recache', XMLDB_INDEX_NOTUNIQUE, array('recache'));
        $dbman->add_index($table, $indexrecache);

        // Add qlinksectionids field to question.
        \local_metadata\mcontext::question()->add_field()->text('qlinksectionids', 'נושא לימוד/מולטי שאלות', [
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'signup' => 0,
        ]);

        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    if ($oldversion < 2024012910) {

        $table = new xmldb_table('community_oercatalog_er_imgs');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_er_imgs');
        }

        $table = new xmldb_table('community_oercatalog_errors');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_errors');
        }

        $table = new xmldb_table('community_oercatalog_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_log');
        }

        $table = new xmldb_table('community_oercatalog_reviews');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_reviews');
        }

        $table = new xmldb_table('community_oercatalog_wht_new');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_wht_new');
        }

        $table = new xmldb_table('community_oercatalog_shr_crt');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'community_oer_shr_crt');
        }
    }

    if ($oldversion < 2024012912) {
        // Create task (run immediately) for recache.
        $task = new \community_oer\task\adhoc_oer();
        $task->set_custom_data(
                array()
        );
        \core\task\manager::queue_adhoc_task($task);
    }

    return true;
}
