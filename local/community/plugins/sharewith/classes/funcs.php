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
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharewith;

defined('MOODLE_INTERNAL') || die();

use csv_import_reader;

require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/duplicate.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

class funcs {

    // Old function "community_sharewith_permission_allow_copy".
    private static function permission_allow_copy($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $categoryid = null) {

        switch ($type) {
            case "coursecopy":
                //if (has_capability('community/sharewith:copycourse', context_course::instance($sourcecourseid), $sourceuserid)
                //        AND has_capability('community/sharewith:copycourse', context_coursecat::instance($categoryid), $userid)) {
                //    return true;
                //}

                return true;
                break;
            case "sectioncopy":
                //if (has_capability('community/sharewith:copysection', context_course::instance($sourcecourseid), $sourceuserid)
                //        and has_capability('community/sharewith:copysection', context_course::instance($courseid), $userid)) {
                //    return true;
                //}

                return true;
                break;
            case "activitycopy":
                //if (has_capability('community/sharewith:copyactivity', context_course::instance($sourcecourseid), $sourceuserid)
                //        and has_capability('community/sharewith:copyactivity', context_course::instance($courseid), $userid)) {
                //    return true;
                //}

                return true;
                break;
        }
        return false;
    }

    // Old function "community_sharewith_permission_allow_share".
    private static function permission_allow_share($userid, $sourceuserid, $sourcecourseid, $courseid) {

        if (has_capability('community/sharewith:shareactivity', \context_course::instance($sourcecourseid), $sourceuserid)
                && has_capability('community/sharewith:copyactivity', \context_course::instance($courseid), $userid)) {
            return true;
        }
        return false;
    }

    // Old function "community_sharewith_send_message_to_teacher".
    private static function send_message_to_teacher($useridfrom, $useridto, $sharedactivityid, $component, $eventtype,
            $customdata = array()) {
        global $DB, $CFG, $USER;

        $smallmessage = get_string($component . '_' . $eventtype, 'message_petel');

        $time = time();
        $userfrom = $DB->get_record("user", array('id' => $useridfrom));

        $customdata['custom'] = true;
        $customdata[$eventtype] = true;
        $customdata['firstname'] = $userfrom->firstname;
        $customdata['lastname'] = $userfrom->lastname;
        $customdata['teacher_image'] = $CFG->wwwroot . '/user/pix.php/' . $useridfrom . '/f1.jpg';
        $customdata['dateformat'] = date("d.m.Y", $time);
        $customdata['timeformat'] = date("H:i", $time);

        // Prepare course.
        if (!empty($sharedactivityid)) {

            $activity = $DB->get_record('course_modules', array('id' => $sharedactivityid));
            if (!empty($activity)) {
                $modinfo = get_fast_modinfo($activity->course);
                $cm = $modinfo->cms[$sharedactivityid];
                $course = $cm->get_course();

                $a = new \stdClass;
                $a->teachername = $userfrom->firstname . ' ' . $userfrom->lastname;
                $a->activityname = $cm->name;
                $content = get_string('subject_message_for_teacher', 'community_sharewith', $a);

                $customdata['coursename'] = $course->fullname;
                $customdata['courseurl'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

                $customdata['activityid'] = $sharedactivityid;
                $customdata['activityname'] = $cm->name;

                $customdata['content'] = $content;
            }
        }

        $objinsert = new \stdClass();
        $objinsert->useridfrom = $useridfrom;
        $objinsert->useridto = $useridto;

        $objinsert->subject = $smallmessage;
        $objinsert->fullmessage = $smallmessage;
        $objinsert->fullmessageformat = 2;
        $objinsert->fullmessagehtml = '';
        $objinsert->smallmessage = $smallmessage;
        $objinsert->component = $component;
        $objinsert->eventtype = $eventtype;
        $objinsert->timecreated = $time;
        $objinsert->customdata = json_encode($customdata);

        $notificationid = $DB->insert_record('notifications', $objinsert);

        $objinsert = new \stdClass();
        $objinsert->notificationid = $notificationid;
        $DB->insert_record('message_petel_notifications', $objinsert);

        return $notificationid;
    }

    // Old function "community_sharewith_iterate_subsections".
    private static function iterate_subsections($section, &$subsectionstree) {
        global $CFG;

        require_once($CFG->dirroot . '/course/format/lib.php');

        $currentsection = $section->section;
        $currentsectionsubs = course_get_format($section->course)->get_subsections($currentsection);
        if (!$currentsectionsubs || count($currentsectionsubs) == 0) {
            return;
        }
        $subsectionstree[$currentsection] = $currentsectionsubs;
        foreach ($currentsectionsubs as $key => $sub) {
            self::iterate_subsections($sub, $subsectionstree);
        }

        return;
    }

    // Old function "community_sharewith_get_all_competencies_cm".
    private static function get_all_competencies_cm($item) {
        global $DB;

        $cm = $DB->get_record('course_modules', array('id' => $item->activity_id));

        if ($cm == false) {
            return [];
        }

        $allcompetencies = [];

        // Base score of activity.
        $competenciesinactivity = \core_competency\api::list_course_module_competencies_in_course_module($cm->id);
        if (count($competenciesinactivity) != 0) {
            $allcompetencies = $competenciesinactivity;
        }

        // For quiz type add quiestions score.
        if ($item->mod_type->mod_type == 'quiz' || $item->mod_type->mod_type == 'activequiz') {
            $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
            $questions = quiz_report_get_significant_questions($quiz);
            foreach ($questions as $question) {
                $qcompetency = \core_competency\api::list_question_competencies_in_question($question->id);
                if (!$qcompetency) {
                    continue;
                }
                $allcompetencies = array_merge($allcompetencies, $qcompetency);
            }
        }

        return $allcompetencies;
    }

    // Old function "community_sharewith_get_sharing_types".
    public static function get_sharing_types() {
        return array(
                'coursecopy',
                'sectioncopy',
                'activitycopy',
                'activityshare',
        );
    }

    // Old function "community_sharewith_add_task".
    public static function add_task($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $sourcesectionid,
            $sectionid, $categoryid = null, $sourceactivityid = null, $metadata = null, $chain = null) {
        global $DB;

        $result = false;
        // Check permission.
        if (self::permission_allow_copy($type, $userid, $sourceuserid, $sourcecourseid, $courseid, $categoryid)) {
            $obj = new \stdClass();
            $obj->type = $type;
            $obj->userid = $userid;
            $obj->sourceuserid = $sourceuserid;
            $obj->sourceactivityid = $sourceactivityid;
            $obj->sourcecourseid = $sourcecourseid;
            $obj->sourcesectionid = $sourcesectionid;
            $obj->courseid = $courseid;
            $obj->sectionid = $sectionid;
            $obj->categoryid = $categoryid;
            $obj->status = 0;
            $obj->timemodified = time();

            if ($chain != null && is_array($chain)) {
                if ($metadata != null) {
                    $meta = json_decode($metadata);
                    $meta->activitysequence = $chain;
                    $metadata = json_encode($meta);
                } else {
                    $metadata = json_encode(array('activitysequence' => $chain));
                }
            }

            $obj->metadata = $metadata;

            $result = $DB->insert_record('community_sharewith_task', $obj);
        }

        return $result;
    }

    // Old function "community_sharewith_save_task".
    public static function save_task($type, $shareid, $courseid, $sectionid, $categoryid = null, $metadata = null,
            $sourcesectionid = null) {
        global $DB, $USER;

        $sendenable = get_config('community_sharewith', 'activitysending');

        if ($sendenable == 1) {

            $share = $DB->get_record('community_sharewith_shared', array('useridto' => $USER->id, 'id' => $shareid));

            if ($share) {
                $activity = $DB->get_record('course_modules', array('id' => $share->activityid));
                if ($activity) {
                    // Check permission.
                    if (self::permission_allow_share($USER->id, $share->useridfrom, $share->courseid, $courseid)) {
                        $obj = new \stdClass();
                        $obj->type = $type;
                        $obj->userid = $USER->id;
                        $obj->sourceuserid = $share->useridfrom;
                        $obj->sourcecourseid = $share->courseid;
                        $obj->courseid = $courseid;
                        $obj->sourcesectionid = $sourcesectionid;
                        $obj->sectionid = $sectionid;
                        $obj->categoryid = $categoryid;
                        $obj->metadata = $metadata;
                        $obj->sourceactivityid = $share->activityid;
                        $obj->status = 0;
                        $obj->timemodified = time();

                        return array('result' => $DB->insert_record('community_sharewith_task', $obj), 'text' => '');
                    }
                } else {
                    return array('result' => 0, 'text' => get_string('activitydeleted', 'community_sharewith'));
                }
            }
        } else {
            return array('result' => 0, 'text' => get_string('sendingnotallowed', 'community_sharewith'));
        }
    }

    // Old function "community_sharewith_get_categories".
    public static function get_categories($courseid = null) {
        global $DB, $USER;

        if (!is_siteadmin($USER)) {
            $sql = "SELECT * FROM {course_categories}
                WHERE id IN (SELECT category from {course} WHERE id=?)";
            $categories = $DB->get_records_sql($sql, array($courseid));
        } else {
            // Get all categories without categories from oer catalog.
            $categories = $DB->get_records('course_categories', array('visible' => 1));

            list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();
            foreach ($categories as $key => $item) {
                if (in_array($item->id, $oercategories)) {
                    unset($categories[$key]);
                }
            }
        }

        return array_values($categories);
    }

    // Old function "community_sharewith_get_courses".
    public static function get_courses() {
        global $DB, $USER;

        $mycourses = enrol_get_my_courses('*', 'id DESC');
        foreach ($mycourses as $key => $item) {
            $mycourses[$key]->lastaccess = 0;
        }

        // Sort courses by last access of current user.
        $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');
        foreach ($lastaccesscourses as $c) {
            if (isset($mycourses[$c->courseid])) {
                $mycourses[$c->courseid]->lastaccess = $c->timeaccess;
            }
        }
        // Sort by user's lastaccess to course.
        usort($mycourses, function($a, $b) {
            return $b->lastaccess - $a->lastaccess;
        });

        list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();

        $result = [];

        $rolespermitted = array('editingteacher');

        foreach ($mycourses as $item) {

            $context = \context_course::instance($item->id);
            $roles = get_user_roles($context, $USER->id, true);
            $flagpermission = false;
            foreach ($roles as $role) {
                if (in_array($role->shortname, $rolespermitted)) {
                    $flagpermission = true;
                }
            }

            if (!in_array($item->id, $oercourses) && $flagpermission) {

                if (!has_capability('moodle/course:update', \context_course::instance($item->id), $USER->id)) {
                    continue;
                }

                $result[] = [
                        'id' => $item->id,
                        'fullname' => $item->fullname,
                        'shortname' => $item->shortname
                ];
            }
        }

        return $result;
    }

    // Old function "community_sharewith_get_section_bycourse".
    public static function get_section_bycourse($courseid) {
        global $DB;

        $course = get_course($courseid);

        $result = [];
        $sql = "SELECT cs.id AS section_id, cs.name AS section_name
            FROM {course} c
            LEFT JOIN {course_sections} cs ON c.id=cs.course
            WHERE c.id=?";
        $arr = $DB->get_records_sql($sql, array($courseid));
        $preresult = array_values($arr);
        foreach ($preresult as $key => $obj) {
            if (empty($obj->section_name)) {
                $objtmp = $obj;
                if ($key == 0) {
                    $objtmp->section_name = get_string('generalsectionname', 'community_sharewith');
                } else {
                    $objtmp->section_name = get_string('sectionname', 'format_' . $course->format) . ' ' . $key;
                }
                $result[] = $objtmp;
            } else {
                $result[] = $obj;
            }
        }

        return $result;
    }

    // Old function "community_sharewith_get_share_courses".
    public static function get_share_courses($activityid, $courseid) {
        global $DB, $PAGE, $USER;

        $context = \context_course::instance($courseid);
        $PAGE->set_context($context);

        $result = $courses = [];

        // Prepare courses.
        $tag = get_config('community_sharewith', 'course_tag');

        $sql = "
            SELECT c.id, c.fullname AS name
            FROM {tag_instance} AS ti
            LEFT JOIN {tag} AS t ON(ti.tagid = t.id)
            LEFT JOIN {course} AS c ON(ti.itemid = c.id)
            WHERE ti.component = 'core' AND ti.itemtype = 'course' AND t.name = ?
        ";

        foreach ($DB->get_records_sql($sql, [$tag]) as $value) {
            $context2 = \context_course::instance($value->id);
            if (!empty(get_user_roles($context2, $USER->id, false))) {
                $courses[] = ['id' => $value->id, 'name' => $value->name];
            }
        }

        $result['courses_enable'] = count($courses);
        $result['courses'] = $courses;

        return json_encode($result);
    }

    // Old function "community_sharewith_get_shared_teachers".
    public static function get_shared_teachers($activityid, $courseid) {
        global $DB, $PAGE, $USER, $CFG;

        $context = \context_course::instance($courseid);
        $PAGE->set_context($context);

        $result = [];

        // Find teachers whom sent message previously.
        $sql = "
        SELECT
            DISTINCT(u.id) AS user_id,
            u.firstname AS firstname,
            u.lastname AS lastname,
            CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
            CONCAT('" . $CFG->wwwroot . "/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url
            FROM {community_sharewith_shared} lss
            LEFT JOIN {user} u
                ON (lss.useridto=u.id)
            WHERE lss.useridfrom=? AND lss.activityid=?
                 AND (lss.source IS NULL OR lss.source = '')
    ";
        $teachers = $DB->get_records_sql($sql, array($USER->id, $activityid));
        $result['teachers'] = array_values($teachers);

        return json_encode($result);
    }

    // Old function "community_sharewith_autocomplete_teachers".
    public static function autocomplete_teachers($searchstring) {
        global $DB;

        $result = '';
        if (!empty($searchstring)) {
            $sql = "
                SELECT
                    DISTINCT u.id AS teacher_id,
                    c.id AS course_id,
                    c.fullname AS full_name,
                    u.username AS user_name,
                    u.firstname AS firstname,
                    u.lastname AS lastname,
                    CONCAT(u.firstname, ' ', u.lastname) AS teacher_name,
                    CONCAT('/user/pix.php/', u.id ,'/f1.jpg') AS teacher_url,
                    u.email AS teacher_mail
                FROM {course} c,
                     {role_assignments} AS ra,
                     {user} AS u, {context} AS ct
                WHERE c.id = ct.instanceid
                    AND ra.roleid IN(1,2,3,4)
                    AND ra.userid = u.id
                    AND ct.id = ra.contextid
                    AND ( u.email LIKE(?)
                        OR u.lastname LIKE(?)
                        OR u.firstname LIKE(?)
                        OR u.username LIKE(?)
                        OR CONCAT(u.firstname, ' ', u.lastname) LIKE(?))
                GROUP BY u.id;
            ";

            $searchstrquery = '%' . $searchstring . '%';
            $teachers = $DB->get_records_sql($sql, array($searchstrquery, $searchstrquery,
                    $searchstrquery, $searchstrquery, $searchstrquery));
            $result = json_encode(array_values($teachers));
        }

        return $result;
    }

    // Old function "community_sharewith_submit_teachers".
    public static function submit_teachers($activityid, $courseid, $teachersid, $coursesid, $message, $sequence) {
        global $USER, $DB;

        $modinfo = get_fast_modinfo($courseid);
        $cm = $modinfo->cms[$activityid];

        $teachersid = json_decode($teachersid);
        $coursesid = json_decode($coursesid);
        $sequence = json_decode($sequence);

        $messageid = 1;

        if ((!empty($teachersid) || !empty($coursesid)) && !empty($activityid) && $activityid != 0 && !empty($courseid) &&
                $courseid != 0) {

            $teachers = [];

            // Share to enroled users course.
            $config = get_config('community_sharewith', 'roles_share_teacher');
            $roles = explode(',', $config);

            foreach ($coursesid as $cid) {
                $context = \context_course::instance($cid);
                foreach (get_enrolled_users($context) as $u) {
                    foreach (get_user_roles($context, $u->id, false) as $role) {
                        if (in_array($role->shortname, $roles) && $u->id != $USER->id) {
                            $teachers[] = $u->id;
                            break;
                        }
                    }
                }
            }

            // Share to teacher.
            $arrteachers = $DB->get_records_sql("
                SELECT DISTINCT u.id AS teacher_id
                FROM {course} c,
                     {role_assignments} AS ra,
                     {user} AS u, {context} AS ct
                WHERE
                    c.id = ct.instanceid
                    AND ra.roleid IN(1,2,3,4)
                    AND ra.userid = u.id
                    AND ct.id = ra.contextid
                GROUP BY u.id;
            ");

            $arrfortest = [];
            foreach ($arrteachers as $item) {
                $arrfortest[] = $item->teacher_id;
            }

            foreach ($teachersid as $teacherid) {
                if (in_array($teacherid, $arrfortest)) {
                    $teachers[] = $teacherid;
                }
            }

            $eventtype = 'copy_to_teacher';
            $lib = new \duplicate();
            $chains = $lib->get_activities_chain($cm->id, $courseid);
            if (count($chains) > 1) {
                $eventtype = 'copy_to_teacher_sequence';
            }

            foreach (array_unique($teachers) as $teacherid) {

                // Save in community_sharewith_shared.
                $objinsert = new \stdClass();
                $objinsert->useridto = $teacherid;
                $objinsert->useridfrom = $USER->id;
                $objinsert->courseid = $courseid;
                $objinsert->activityid = $activityid;
                $objinsert->messageid = null;
                $objinsert->restoreid = null;
                $objinsert->complete = 0;
                $objinsert->timecreated = time();

                $rowid = $DB->insert_record('community_sharewith_shared', $objinsert);
                if (!$rowid) {
                    return false;
                }

                // Prepare message for user.
                $customdata = array('message' => $message);
                $messageid = self::send_message_to_teacher($USER->id, $teacherid, $activityid, 'community_sharewith',
                        $eventtype, $customdata);
            }
        }
        return $messageid;
    }

    // Old function "community_sharewith_copy_users_mod_game".
    public static function copy_users_mod_game($sourceactivityid, $targetactivity, $courseid, $sectionid) {
        global $DB;

        $sql = '
            SELECT m.name, cm.instance, cm.course
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module = m.id)
            WHERE cm.id = ?
        ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));

        if ($source->name == 'game') {

            if ($game = $DB->get_record('game', array('id' => $source->instance))) {

                $lib = new \duplicate();

                if (in_array($game->sourcemodule, array('quiz', 'glossary'))) {
                    $cmactivity = $DB->get_record('course_modules', array('id' => $targetactivity->id));
                    $gameupdate = $DB->get_record('game', array('id' => $cmactivity->instance));
                }

                switch ($game->sourcemodule) {
                    case 'quiz':
                        $quiz = $DB->get_record('quiz', array('id' => $game->quizid));

                        if (!empty($quiz)) {
                            $cm = $DB->get_record('course_modules', array('course' => $quiz->course, 'instance' => $quiz->id));

                            $newactivities = array();
                            $newactivity = $lib->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities);

                            $newcm = $DB->get_record('course_modules', array('id' => $newactivity->id));
                            $gameupdate->quizid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;
                    case 'glossary':
                        $glossary = $DB->get_record('glossary', array('id' => $game->glossaryid));

                        if (!empty($glossary)) {
                            $cm = $DB->get_record('course_modules', array('course' => $glossary->course, 'instance' => $glossary->id));

                            $newactivities = array();
                            $newactivity = $lib->duplicate_activity($cm->id, $courseid, $sectionid, $newactivities);

                            $newcm = $DB->get_record('course_modules', array('id' => $newactivity->id));
                            $gameupdate->glossaryid = $newcm->instance;
                            $DB->update_record('game', $gameupdate);

                            return $newactivity;
                        } else {
                            return null;
                        }
                        break;
                }
            }
        }

        return null;
    }

    // Old function "community_sharewith_copy_users_mod_database".
    public static function copy_users_mod_database($sourceactivityid, $targetactivityid) {
        global $DB, $CFG;

        $sql = '
            SELECT m.name, cm.instance, cm.course
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module = m.id)
            WHERE cm.id = ?
        ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));
        $target = $DB->get_record_sql($sql, array($targetactivityid));

        if ($source->name == 'data' && $target->name == 'data') {
            require_once($CFG->dirroot . '/mod/data/lib.php');

            $data = $DB->get_record('data', array('id' => $source->instance));
            $cm = get_coursemodule_from_instance('data', $data->id, $data->course);
            $context = \context_module::instance($cm->id);

            // Fill in missing properties needed for updating of instance.
            $data->course = $cm->course;
            $data->cmidnumber = $cm->idnumber;
            $data->instance = $cm->instance;

            $fieldrecords = $DB->get_records('data_fields', array('dataid' => $data->id), 'id');

            if (!empty($fieldrecords)) {

                // Populate objets for this databases fields.
                $fields = array();
                foreach ($fieldrecords as $fieldrecord) {
                    $fields[] = data_get_field($fieldrecord, $data);
                }

                // Selected fields.
                $selectedfields = array();
                foreach ($fieldrecords as $item) {
                    $selectedfields[] = $item->id;
                }

                $currentgroup = groups_get_activity_group($cm);
                $exportdata = data_get_exportdata($data->id, $fields, $selectedfields, $currentgroup, $context,
                        1, 1, false);
                $count = count($exportdata);

                $delimitername = 'comma';
                $csvstring = data_export_csv($exportdata, $delimitername, $data->name, $count, true);

                // Restore from string.
                $data = $DB->get_record('data', array('id' => $target->instance), '*', MUST_EXIST);
                $iid = csv_import_reader::get_new_iid('moddata');
                $cir = new csv_import_reader($iid, 'moddata');
                $readcount = $cir->load_csv_content($csvstring, 'utf-8', $delimitername);

                if ($fieldnames = $cir->get_columns()) {
                    $fieldnames = array_flip($fieldnames);

                    // Check the fieldnames are valid.
                    $rawfields = $DB->get_records('data_fields', array('dataid' => $data->id), '', 'name, id, type');
                    $fields = array();
                    $errorfield = '';
                    foreach ($fieldnames as $name => $id) {
                        if (!isset($rawfields[$name])) {
                            $errorfield .= "'$name' ";
                        } else {
                            $field = $rawfields[$name];
                            require_once("$CFG->dirroot/mod/data/field/$field->type/field.class.php");
                            $classname = 'data_field_' . $field->type;
                            $fields[$name] = new $classname($field, $data, $cm);
                        }
                    }

                    $cir->init();
                    $recordsadded = 0;
                    while ($record = $cir->next()) {
                        if ($recordid = data_add_record($data, 0)) {
                            foreach ($fields as $field) {
                                $fieldid = $fieldnames[$field->field->name];
                                if (isset($record[$fieldid])) {
                                    $value = $record[$fieldid];
                                } else {
                                    $value = '';
                                }

                                if (method_exists($field, 'update_content_import')) {
                                    $field->update_content_import($recordid, $value, 'field_' . $field->field->id);
                                } else {
                                    $content = new \stdClass();
                                    $content->fieldid = $field->field->id;
                                    $content->content = $value;
                                    $content->recordid = $recordid;
                                    $DB->insert_record('data_content', $content);
                                }
                            }
                            $recordsadded++;
                            print get_string('added', 'moodle', $recordsadded) . ". " . get_string('entry', 'data') .
                                    " (ID $recordid)<br />\n";
                        }
                    }
                    $cir->close();
                    $cir->cleanup(true);
                }
            }
        }
    }

    // Old function "community_sharewith_copy_entries_mod_glossary".
    public static function copy_entries_mod_glossary($sourceactivityid, $targetactivityid) {
        global $DB;

        $sql = '
            SELECT m.name, cm.instance, cm.course
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module = m.id)
            WHERE cm.id = ?
        ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));
        $target = $DB->get_record_sql($sql, array($targetactivityid));

        if ($source->name == 'glossary' && $target->name == 'glossary') {

            foreach ($DB->get_records('glossary_entries', ['glossaryid' => $source->instance]) as $entry) {
                unset($entry->id);

                $entry->glossaryid = $target->instance;

                $DB->insert_record('glossary_entries', $entry);
            }
        }
    }

    // Old function "community_sharewith_copy_users_mod_glossary".
    public static function copy_users_mod_glossary($sourceactivityid, $targetactivityid) {
        global $DB, $CFG, $USER;

        $sql = '
            SELECT m.name, cm.instance, cm.course
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module = m.id)
            WHERE cm.id = ?
        ';

        $source = $DB->get_record_sql($sql, array($sourceactivityid));
        $target = $DB->get_record_sql($sql, array($targetactivityid));

        if ($source->name == 'glossary' && $target->name == 'glossary') {
            require_once($CFG->dirroot . '/mod/glossary/lib.php');

            if ($glossary = $DB->get_record('glossary', array('id' => $source->instance))) {

                // Build xml file.
                $content = glossary_generate_export_file($glossary, null, 0);

                // Upload XML.
                if (!$glossary = $DB->get_record("glossary", array("id" => $target->instance))) {
                    throw new \moodle_exception('invalidid', 'glossary');
                }

                if ($cm = get_coursemodule_from_id('glossary', $targetactivityid)) {
                    $context = \context_module::instance($cm->id);

                    $data = new \stdClass();
                    $data->id = $targetactivityid;
                    $data->dest = 'current';
                    $data->catsincl = 1;

                    if ($xml = glossary_read_imported_file($content)) {
                        $importedentries = 0;
                        $importedcats = 0;
                        $entriesrejected = 0;
                        $rejections = '';
                        $glossarycontext = $context;

                        if ($data->dest == 'newglossary') {

                            // If the user chose to create a new glossary.
                            $xmlglossary = $xml['GLOSSARY']['#']['INFO'][0]['#'];

                            if ($xmlglossary['NAME'][0]['#']) {
                                $glossary = new \stdClass();
                                $glossary->modulename = 'glossary';
                                $glossary->module = $cm->module;
                                $glossary->name = ($xmlglossary['NAME'][0]['#']);
                                $glossary->globalglossary = ($xmlglossary['GLOBALGLOSSARY'][0]['#']);
                                $glossary->intro = ($xmlglossary['INTRO'][0]['#']);
                                $glossary->introformat =
                                        isset($xmlglossary['INTROFORMAT'][0]['#']) ? $xmlglossary['INTROFORMAT'][0]['#'] :
                                                FORMAT_MOODLE;
                                $glossary->showspecial = ($xmlglossary['SHOWSPECIAL'][0]['#']);
                                $glossary->showalphabet = ($xmlglossary['SHOWALPHABET'][0]['#']);
                                $glossary->showall = ($xmlglossary['SHOWALL'][0]['#']);
                                $glossary->cmidnumber = null;

                                // Setting the default values if no values were passed.
                                if (isset($xmlglossary['ENTBYPAGE'][0]['#'])) {
                                    $glossary->entbypage = ($xmlglossary['ENTBYPAGE'][0]['#']);
                                } else {
                                    $glossary->entbypage = $CFG->glossary_entbypage;
                                }
                                if (isset($xmlglossary['ALLOWDUPLICATEDENTRIES'][0]['#'])) {
                                    $glossary->allowduplicatedentries = ($xmlglossary['ALLOWDUPLICATEDENTRIES'][0]['#']);
                                } else {
                                    $glossary->allowduplicatedentries = $CFG->glossary_dupentries;
                                }
                                if (isset($xmlglossary['DISPLAYFORMAT'][0]['#'])) {
                                    $glossary->displayformat = ($xmlglossary['DISPLAYFORMAT'][0]['#']);
                                } else {
                                    $glossary->displayformat = 2;
                                }
                                if (isset($xmlglossary['ALLOWCOMMENTS'][0]['#'])) {
                                    $glossary->allowcomments = ($xmlglossary['ALLOWCOMMENTS'][0]['#']);
                                } else {
                                    $glossary->allowcomments = $CFG->glossary_allowcomments;
                                }
                                if (isset($xmlglossary['USEDYNALINK'][0]['#'])) {
                                    $glossary->usedynalink = ($xmlglossary['USEDYNALINK'][0]['#']);
                                } else {
                                    $glossary->usedynalink = $CFG->glossary_linkentries;
                                }
                                if (isset($xmlglossary['DEFAULTAPPROVAL'][0]['#'])) {
                                    $glossary->defaultapproval = ($xmlglossary['DEFAULTAPPROVAL'][0]['#']);
                                } else {
                                    $glossary->defaultapproval = $CFG->glossary_defaultapproval;
                                }

                                // These fields were not included in export, assume zero.
                                $glossary->assessed = 0;
                                $glossary->availability = null;

                                // New glossary is to be inserted in section 0, it is always visible.
                                $glossary->section = 0;
                                $glossary->visible = 1;
                                $glossary->visibleoncoursepage = 1;
                            }
                        }

                        $xmlentries = $xml['GLOSSARY']['#']['INFO'][0]['#']['ENTRIES'][0]['#']['ENTRY'];
                        $sizeofxmlentries = is_array($xmlentries) ? count($xmlentries) : 0;
                        for ($i = 0; $i < $sizeofxmlentries; $i++) {
                            // Inserting the entries.
                            $xmlentry = $xmlentries[$i];
                            $newentry = new \stdClass();
                            $newentry->concept = trim($xmlentry['#']['CONCEPT'][0]['#']);
                            $definition = $xmlentry['#']['DEFINITION'][0]['#'];
                            if (!is_string($definition)) {
                                throw new \moodle_exception('errorparsingxml', 'glossary');
                            }
                            $newentry->definition = trusttext_strip($definition);
                            if (isset($xmlentry['#']['CASESENSITIVE'][0]['#'])) {
                                $newentry->casesensitive = $xmlentry['#']['CASESENSITIVE'][0]['#'];
                            } else {
                                $newentry->casesensitive = $CFG->glossary_casesensitive;
                            }

                            $permissiongranted = 1;
                            if ($newentry->concept && $newentry->definition) {
                                if (!$glossary->allowduplicatedentries) {
                                    // Checking if the entry is valid (checking if it is duplicated when should not be).
                                    if ($newentry->casesensitive) {
                                        $dupentry = $DB->record_exists_select('glossary_entries',
                                                'glossaryid = :glossaryid AND concept = :concept', array(
                                                        'glossaryid' => $glossary->id,
                                                        'concept' => $newentry->concept));
                                    } else {
                                        $dupentry = $DB->record_exists_select('glossary_entries',
                                                'glossaryid = :glossaryid AND LOWER(concept) = :concept', array(
                                                        'glossaryid' => $glossary->id,
                                                        'concept' => \core_text::strtolower($newentry->concept)));
                                    }
                                    if ($dupentry) {
                                        $permissiongranted = 0;
                                    }
                                }
                            } else {
                                $permissiongranted = 0;
                            }

                            if ($permissiongranted) {
                                $newentry->glossaryid = $glossary->id;
                                $newentry->sourceglossaryid = 0;
                                $newentry->approved = 1;
                                $newentry->userid = $USER->id;
                                $newentry->teacherentry = 1;
                                $newentry->definitionformat = $xmlentry['#']['FORMAT'][0]['#'];
                                $newentry->timecreated = time();
                                $newentry->timemodified = time();

                                // Setting the default values if no values were passed.
                                if (isset($xmlentry['#']['USEDYNALINK'][0]['#'])) {
                                    $newentry->usedynalink = $xmlentry['#']['USEDYNALINK'][0]['#'];
                                } else {
                                    $newentry->usedynalink = $CFG->glossary_linkentries;
                                }
                                if (isset($xmlentry['#']['FULLMATCH'][0]['#'])) {
                                    $newentry->fullmatch = $xmlentry['#']['FULLMATCH'][0]['#'];
                                } else {
                                    $newentry->fullmatch = $CFG->glossary_fullmatch;
                                }

                                $newentry->id = $DB->insert_record("glossary_entries", $newentry);
                                $importedentries++;

                                $xmlaliases = @$xmlentry['#']['ALIASES'][0]['#']['ALIAS'];
                                $sizeofxmlaliases = is_array($xmlaliases) ? count($xmlaliases) : 0;
                                for ($k = 0; $k < $sizeofxmlaliases; $k++) {
                                    // Importing aliases.
                                    $xmlalias = $xmlaliases[$k];
                                    $aliasname = $xmlalias['#']['NAME'][0]['#'];

                                    if (!empty($aliasname)) {
                                        $newalias = new \stdClass();
                                        $newalias->entryid = $newentry->id;
                                        $newalias->alias = trim($aliasname);
                                        $newalias->id = $DB->insert_record("glossary_alias", $newalias);
                                    }
                                }

                                if (!empty($data->catsincl)) {

                                    // If the categories must be imported.
                                    $xmlcats = @$xmlentry['#']['CATEGORIES'][0]['#']['CATEGORY'];
                                    $sizeofxmlcats = is_array($xmlcats) ? count($xmlcats) : 0;
                                    for ($k = 0; $k < $sizeofxmlcats; $k++) {
                                        $xmlcat = $xmlcats[$k];

                                        $newcat = new \stdClass();
                                        $newcat->name = $xmlcat['#']['NAME'][0]['#'];
                                        $newcat->usedynalink = $xmlcat['#']['USEDYNALINK'][0]['#'];
                                        if (!$category = $DB->get_record("glossary_categories",
                                                array("glossaryid" => $glossary->id, "name" => $newcat->name))) {
                                            // Create the category if it does not exist.
                                            $category = new \stdClass();
                                            $category->name = $newcat->name;
                                            $category->glossaryid = $glossary->id;
                                            $category->id = $DB->insert_record("glossary_categories", $category);
                                            $importedcats++;
                                        }
                                        if ($category) {
                                            // Inserting the new relation.
                                            $entrycat = new \stdClass();
                                            $entrycat->entryid = $newentry->id;
                                            $entrycat->categoryid = $category->id;
                                            $DB->insert_record("glossary_entries_categories", $entrycat);
                                        }
                                    }
                                }

                                // Import files embedded in the entry text.
                                glossary_xml_import_files($xmlentry['#'], 'ENTRYFILES', $glossarycontext->id, 'entry', $newentry->id);

                                // Import files attached to the entry.
                                if (glossary_xml_import_files($xmlentry['#'], 'ATTACHMENTFILES', $glossarycontext->id, 'attachment',
                                        $newentry->id)) {
                                    $DB->update_record("glossary_entries", array('id' => $newentry->id, 'attachment' => '1'));
                                }

                            } else {
                                $entriesrejected++;
                                if ($newentry->concept && $newentry->definition) {
                                    // Add to exception report (duplicated entry)).
                                    $rejections .= "<tr><td>$newentry->concept</td>" .
                                            "<td>" . get_string("duplicateentry", "glossary") . "</td></tr>";
                                } else {
                                    // Add to exception report (no concept or definition found)).
                                    $rejections .= "<tr><td>---</td>" .
                                            "<td>" . get_string("noconceptfound", "glossary") . "</td></tr>";
                                }
                            }
                        }

                        // Reset caches.
                        \mod_glossary\local\concept_cache::reset_glossary($glossary);
                    }
                }
            }
        }
    }

    // Old function "community_sharewith_update_meta_id".
    public static function update_meta_id($newactivity) {

        // UPDATE MID IN METADATA.
        \local_metadata\mcontext::module()->save($newactivity->id, 'ID', $newactivity->id);
    }

    // Old function "community_sharewith_update_meta_history".
    public static function update_meta_history($sourceactivityid, $targetactivityid, $obj) {

        if (!isset($obj->disable_update_meta_history) || !$obj->disable_update_meta_history) {

            // Update historycmid in metadata.
            $history = \local_metadata\mcontext::module()->get($sourceactivityid, 'historycmid');

            if (!empty($history)) {
                $arr = explode(',', $history);
                $arr[] = $sourceactivityid;
                $data = implode(',', $arr);
            } else {
                $data = $sourceactivityid;
            }

            // Update or insert.
            \local_metadata\mcontext::module()->save($targetactivityid, 'historycmid', $data);

            // Update historyauthors in metadata.
            $historyauthors = \local_metadata\mcontext::module()->get($sourceactivityid, 'historyauthors');

            $data = '';
            if (isset($obj->sourceuserid)) {
                if (!empty($historyauthors)) {
                    $arr = explode(',', $historyauthors);
                    $arr[] = $obj->sourceuserid;
                    $data = implode(',', $arr);
                } else {
                    $data = $obj->sourceuserid;
                }
            }

            // Update or insert.
            \local_metadata\mcontext::module()->save($targetactivityid, 'historyauthors', $data);
        }
    }

    // Old function "community_sharewith_get_subsections_tree".
    public static function get_subsections_tree($sectionid) {
        global $DB;

        $subsectionstree = [];
        if ($section = $DB->get_record('course_sections', array('id' => $sectionid), '*', MUST_EXIST)) {
            self::iterate_subsections($section, $subsectionstree);
        }

        return $subsectionstree;
    }

    // Old function "community_sharewith_copy_competencies".
    public static function copy_competencies($cmid, $targetcourseid) {
        global $DB;

        $params = [];
        $params[] = $cmid;
        $sql = "SELECT m.name AS mod_type
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module = m.id)
            WHERE cm.id = ?";

        $modtype = $DB->get_record_sql($sql, $params);

        $item = new \stdClass();
        $item->activity_id = $cmid;
        $item->mod_type = $modtype;

        if ($competenciesobj = self::get_all_competencies_cm($item)) {
            foreach ($competenciesobj as $key => $c) {
                \core_competency\api::add_competency_to_course($targetcourseid, $c->get('competencyid'));
            }
        }

        return true;
    }

    // Old function "community_sharewith_change_availability".
    public static function change_availability($object, $cmid, $arrcmids) {
        global $DB;

        $obj = clone($object);

        if (isset($obj->op) && isset($obj->c)) {
            foreach ($obj->c as $key => $rule) {
                $res = self::change_availability($rule, $cmid, $arrcmids);

                if ($res != null) {
                    $obj->c[$key] = $res;
                } else {
                    unset($obj->c[$key]);

                    if (isset($obj->showc)) {
                        unset($obj->showc[$key]);
                    }
                }
            }

            if (!empty($obj->c)) {
                $obj->c = array_values($obj->c);

                if (isset($obj->showc)) {
                    $obj->showc = array_values($obj->showc);
                }
            } else {
                $obj = null;
            }
        }

        if (isset($obj->type)) {
            switch ($obj->type) {
                case 'completion':
                    if (isset($arrcmids[$obj->cm])) {
                        $obj->cm = $arrcmids[$obj->cm];
                    } else {
                        $obj = null;
                    }

                    break;
                case 'grade':
                    $oldcmid = 0;
                    if ($gradeold = $DB->get_record('grade_items', ['id' => $obj->id])) {
                        $module = $DB->get_record('modules', array('name' => $gradeold->itemmodule));
                        $cm = $DB->get_record('course_modules', [
                                'module' => $module->id,
                                'course' => $gradeold->courseid,
                                'instance' => $gradeold->iteminstance,
                        ]);

                        $oldcmid = $cm->id;
                    }

                    if (isset($arrcmids[$oldcmid])) {
                        $newcmid = $arrcmids[$oldcmid];

                        if ($cm = $DB->get_record('course_modules', array('id' => $newcmid))) {
                            $module = $DB->get_record('modules', array('id' => $cm->module));
                            $gradenew = $DB->get_record('grade_items', [
                                    'courseid' => $cm->course,
                                    'iteminstance' => $cm->instance,
                                    'itemmodule' => $module->name,
                            ]);

                            if ($gradenew) {
                                $obj->id = $gradenew->id;
                            } else {
                                $obj = null;
                            }
                        } else {
                            $obj = null;
                        }
                    } else {
                        $obj = null;
                    }

                    break;
                case 'group':
                    if ($cm = $DB->get_record('course_modules', array('id' => $cmid))) {
                        if ($group = $DB->get_record('groups', array('id' => $obj->id))) {
                            if ($group->courseid != $cm->course) {
                                $obj = null;
                            }
                        } else {
                            $obj = null;
                        }
                    } else {
                        $obj = null;
                    }

                    break;
                case 'sequence':
                    if (isset($arrcmids[$obj->cm])) {
                        $obj->cm = $arrcmids[$obj->cm];
                    } else {
                        $obj = null;
                    }

                    break;
                case 'quizquestion':
                    $module = $DB->get_record('modules', array('name' => 'quiz'));
                    if ($quiz = $DB->get_record('quiz', ['id' => $obj->quizid])) {
                        $cm = $DB->get_record('course_modules', [
                                'module' => $module->id,
                                'course' => $quiz->course,
                                'instance' => $obj->quizid,
                        ]);

                        if (isset($arrcmids[$cm->id])) {
                            $cmnew = $DB->get_record('course_modules', ['id' => $arrcmids[$cm->id]]);
                            $obj->quizid = $cmnew->instance;

                            $context = \context_module::instance($cmnew->id);

                            $query = "
                            SELECT q.* 
                            FROM {question} q                            
                            LEFT JOIN {question_versions} qv ON (q.id = qv.questionid)
                            LEFT JOIN {question_references} qr ON (qr.questionbankentryid = qv.questionbankentryid)                            
                            WHERE qr.questionbankentryid = ?
                            LIMIT 1
                        ";
                            $oldquestion = $DB->get_record_sql($query, [$obj->questionbankentryid]);

                            $questions = [];
                            foreach ($DB->get_records('question_categories', ['contextid' => $context->id]) as $cat) {

                                $query = "
                                SELECT q.name as name, qbe.id as questionbankentryid                                          
                                FROM {question} q               
                                JOIN {question_versions} qv ON qv.questionid = q.id
                                JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                                JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                                WHERE qc.id = ?;
                            ";

                                $arr = $DB->get_records_sql($query, [$cat->id]);
                                $questions = array_merge($questions, $arr);
                            }

                            $newquestionbankentryid = 0;
                            foreach ($questions as $q) {
                                if ($oldquestion->name == $q->name) {
                                    $newquestionbankentryid = $q->questionbankentryid;
                                }
                            }

                            if ($newquestionbankentryid) {
                                $obj->questionbankentryid = $newquestionbankentryid;
                            } else {
                                $obj = null;
                            }
                        } else {
                            $obj = null;
                        }
                    }
                    break;
            }
        }

        return $obj;
    }

    // Old function "community_sharewith_send_mail_toadmin_about_banksharing".
    public static function send_mail_toadmin_about_banksharing($sourcecmid, $newcmid) {
        global $DB, $OUTPUT, $CFG;

        $strmails = get_config('local_community', 'adminmails');
        $arrmails = explode(',', $strmails);

        if (empty($arrmails)) {
            return false;
        }

        list($sourcecourse, $sourcecm) = get_course_and_cm_from_cmid($sourcecmid);
        list($newcourse, $newcm) = get_course_and_cm_from_cmid($newcmid);

        $a = new \StdClass();
        $a->activity_name = $newcm->name;
        $a->course_name = $newcourse->fullname;

        // Get author (teacher) who created and shared this activity to the oer catalog.
        $userid = \local_metadata\mcontext::module()->get($newcmid, 'userid');
        $author = $DB->get_record('user', ['id' => trim($userid)]);

        $a->activity_user_fname = $author->firstname;
        $a->activity_user_lname = $author->lastname;
        $a->activity_user_email = $author->email;

        $a->new_activity_id = $newcmid;
        $a->url_new_activity = $newcm->url->out();

        $a->date = date("d-m-Y");
        $a->time = date("H:i:s");

        $a->url_original_activity = $sourcecm->url->out();

        // Alert if present mid in oer.
        $a->mid_present_oer = false;
        $newmid = trim(\local_metadata\mcontext::module()->get($newcmid, 'ID'));
        if (!empty($newmid)) {
            $activity = new \community_oer\activity_oer;
            $obj = $activity->query(-1)->compare('metadata_ID', $newmid)->groupBy('cmid');
            $values = array_values($obj->get());
            $data = array_shift($values);

            if (!empty($data)) {
                $a->mid_present_oer = true;
                $a->url_activity_mid = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $data->metadata_ID;

                $a->users = [];
                foreach ($data->users as $user) {
                    if ($user->userid > 0) {
                        $urlprofile = $CFG->wwwroot . '/user/profile.php?id=' . $user->userid;
                        $a->users[] = ['name' => $user->user_fname . ' ' . $user->user_lname, 'urlprofile' => $urlprofile];
                    }
                }
            }
        }

        if ($a->mid_present_oer && !empty($newmid)) {
            $subject = get_string('mail_subject_add_activity_existmid', 'local_community', $newmid);
        } else {
            $subject = get_string('mail_subject_add_activity', 'local_community');
        }

        foreach ($arrmails as $mail) {
            $touser = get_admin();
            $touser->email = trim($mail);

            $messagehtml = $OUTPUT->render_from_template('community_sharewith/mails/mail_share_to_bank', $a);
            $message = $messagehtml;
            if (!empty($touser) && !empty($touser->id) && !empty($touser->email)) {
                $fromuser = get_admin();
                email_to_user($touser, $fromuser, $subject, $message, $messagehtml);
            }
        }

        return true;
    }

    // Old function "community_sharewith_send_mail_toadmin_about_duplicate_mid".
    public static function send_mail_toadmin_about_duplicate_mid($sourcecmid, $newcmid) {
        global $OUTPUT;

        $strmails = get_config('local_community', 'adminmails');
        $arrmails = explode(',', $strmails);

        if (empty($arrmails)) {
            return false;
        }

        list($sourcecourse, $sourcecm) = get_course_and_cm_from_cmid($sourcecmid);
        list($newcourse, $newcm) = get_course_and_cm_from_cmid($newcmid);

        $a = new \StdClass();
        $a->new_activity_name = $newcm->name;
        $a->original_activity_name = $sourcecm->name;

        $a->url_new_activity = $newcm->url->out();
        $a->url_original_activity = $sourcecm->url->out();

        $a->course_name = $newcourse->fullname;
        $a->new_activity_id = $newcmid;

        $subject = get_string('mail_subject_duplicate_mid', 'community_sharewith');

        foreach ($arrmails as $mail) {
            $touser = get_admin();
            $touser->email = trim($mail);

            $messagehtml = $OUTPUT->render_from_template('community_sharewith/mails/mail_duplicate_mid', $a);
            $message = $messagehtml;
            if (!empty($touser) && !empty($touser->id) && !empty($touser->email)) {
                $fromuser = get_admin();
                email_to_user($touser, $fromuser, $subject, $message, $messagehtml);
            }
        }

        return true;
    }

    // Old function "community_sharewith_change_activity_name".
    public static function change_activity_name($metadata, $newactivityid) {
        global $DB;
        $cmid = get_coursemodule_from_id('', $newactivityid);
        $DB->update_record($cmid->modname, ['id' => $cmid->instance, 'name' => get_string('wordcopy', 'community_sharewith') . " " . $cmid->name]);
    }
}
