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
 * @package     community_oer
 * @category    upgrade
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_community_oer_install() {

    global $DB, $PAGE;

    $dbman = $DB->get_manager();

    // Drop old tables.
    if ($dbman->table_exists('community_resources_activity')) {
        $table = new xmldb_table('community_resources_activity');
        $dbman->drop_table($table);
    }

    if ($dbman->table_exists('community_resources_question')) {
        $table = new xmldb_table('community_resources_question');
        $dbman->drop_table($table);
    }

    // Activity module.
    $table = new xmldb_table('community_oer_activity');

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

    // Question module.
    $table = new xmldb_table('community_oer_question');

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

    // Sequence module.
    $table = new xmldb_table('community_oer_sequence');

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

    // Course module.
    $table = new xmldb_table('community_oer_course');

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

    // Table community_oer_wht_new.
    $table = new xmldb_table('community_oer_wht_new');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('itemtype', XMLDB_TYPE_CHAR, '32', null, null, null, null);
    $table->add_field('useridfollow', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('useridfollowed', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('counter', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    $dbman->create_table($table);

    // Set indexes.
    $indexitemid = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));
    $dbman->add_index($table, $indexitemid);

    $indexuseridfollow = new xmldb_index('useridfollow', XMLDB_INDEX_NOTUNIQUE, array('useridfollow'));
    $dbman->add_index($table, $indexuseridfollow);

    $indexuseridfollowed = new xmldb_index('useridfollowed', XMLDB_INDEX_NOTUNIQUE, array('useridfollowed'));
    $dbman->add_index($table, $indexuseridfollowed);

    $indexcounter = new xmldb_index('counter', XMLDB_INDEX_NOTUNIQUE, array('counter'));
    $dbman->add_index($table, $indexcounter);

    // Table community_oer_log.
    $table = new xmldb_table('community_oer_log');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('newactivityid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    $dbman->create_table($table);

    // Set indexes.
    $indexuserid = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $dbman->add_index($table, $indexuserid);

    $indexactivityid = new xmldb_index('activityid', XMLDB_INDEX_NOTUNIQUE, array('activityid'));
    $dbman->add_index($table, $indexactivityid);

    $indexcourseid = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
    $dbman->add_index($table, $indexcourseid);

    $indexoercloguseactuix = new xmldb_index('useridactivityid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'activityid'));
    if (!$dbman->index_exists($table, $indexoercloguseactuix)) {
        $dbman->add_index($table, $indexoercloguseactuix);
    }

    // Update index in local_metadata.
    if ($dbman->table_exists('local_metadata')) {
        if ($dbman->field_exists('local_metadata', 'data')) {

            $tablelocal = new xmldb_table('local_metadata');
            $indexcheck = new xmldb_index('data', XMLDB_INDEX_NOTUNIQUE, array('data'));
            $indexinsert = new xmldb_index('data', XMLDB_INDEX_NOTUNIQUE, array('data(250)'));

            if (!$dbman->index_exists($tablelocal, $indexcheck)) {
                $dbman->add_index($tablelocal, $indexinsert);
            }
        }
    }

    // Update index in tag_instance.
    if ($dbman->table_exists('tag_instance')) {
        if ($dbman->field_exists('tag_instance', 'itemid')) {

            $tabletag = new xmldb_table('tag_instance');
            $indexitemid = new xmldb_index('itemid', XMLDB_INDEX_NOTUNIQUE, array('itemid'));

            if (!$dbman->index_exists($tabletag, $indexitemid)) {
                $dbman->add_index($tabletag, $indexitemid);
            }
        }
    }

    // Table community_oer_reviews.
    $table = new xmldb_table('community_oer_reviews');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('logid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('objid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('recommendation', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('feedback', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('logid', XMLDB_INDEX_NOTUNIQUE, array('logid'));

    $dbman->create_table($table);

    // Table community_oerctlg_rvw_rqsts.
    $table = new xmldb_table('community_oerctlg_rvw_rqsts');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('logid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('state', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
    $table->add_field('views', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('state', XMLDB_INDEX_NOTUNIQUE, array('state'));
    $table->add_index('views', XMLDB_INDEX_NOTUNIQUE, array('views'));

    $dbman->create_table($table);

    // Table community_oerctlg_rvw_cmmnts.
    $table = new xmldb_table('community_oerctlg_rvw_cmmnts');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('reviewid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('comment', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('reviewid', XMLDB_INDEX_NOTUNIQUE, array('reviewid'));

    $dbman->create_table($table);

    // Table community_oer_er_imgs.
    $table = new xmldb_table('community_oer_er_imgs');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('errorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('imagepath', XMLDB_TYPE_TEXT, null, null, null, null, null);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('errorid', XMLDB_INDEX_NOTUNIQUE, array('errorid'));

    $dbman->create_table($table);

    // Table community_oer_errors.
    $table = new xmldb_table('community_oer_errors');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('logid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('errortype', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
    $table->add_field('errortext', XMLDB_TYPE_TEXT, 'long', null, null, null, null);
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
    $table->add_index('logid', XMLDB_INDEX_NOTUNIQUE, array('logid'));

    $dbman->create_table($table);

    // Metadata fields.
    \local_metadata\mcontext::category()->add_field()->fileupload('imagecategory', 'איקון בתפריט', [
            'visible' => 2,
    ]);

    \local_metadata\mcontext::course()->add_field()->fileupload('imageoerdefault', 'תמונת ברירת מחדל', [
            'visible' => 2,
    ]);

    \local_metadata\mcontext::module()->add_field()->checkbox('cresearch', 'מיועד למחקר', [
            'required' => 0,
            'locked' => 1,
            'visible' => 2,
            'signup' => 0,
    ]);

    \local_metadata\mcontext::module()->add_field()->text('linksectionids', 'נושא לימוד/מולטי פעילות', [
            'required' => 0,
            'locked' => 1,
            'visible' => 0,
            'signup' => 0,
    ]);

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

    return true;
}
