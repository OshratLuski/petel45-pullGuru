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
 * @package    theme_petel
 * @copyright  2025 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel;

use core\output\html_writer;

class funcs {

    public static function add_user_profile_uigender_to_body() {
        global $DB, $USER, $PAGE;

        $pagelayout = $PAGE->pagelayout;
        // Only activate this feature in main course view or module view pages.
        if ($pagelayout === 'incourse' || $pagelayout === 'course' ) {
            $userinfofield = $DB->get_record('user_info_field', ['shortname' => 'ui_gender']); // Field ui_gender must exists
            if ($userinfofield) {
                $userinfodata = $DB->get_record('user_info_data', ['fieldid' => $userinfofield->id, 'userid' => $USER->id]);
                if ($userinfodata) {
                    return strtoupper($userinfodata->data);
                }
            }
        }

        return '';
    }

    /**
     * Add role class to body
     * @return string
     */
    public static function add_role_class_to_body() {
        global $PAGE, $USER;

        $userrole = 'role-teacher';
        $isstudent = false;
        $isteachercolleague = false;
        $ismanager = false;
        $userroles = get_user_roles($PAGE->context, $USER->id, true);
        foreach ($userroles as $role) {
            if ($role->shortname === 'student') {
                $isstudent = true;
            }
            if ($role->shortname === 'teachercolleague') {
                $isteachercolleague = true;
            }
            if ($role->shortname === 'manager') {
                $ismanager = true;
            }
        }
        if ($isstudent) {
            $userrole = 'role-student';
        }
        if ($isteachercolleague) {
            $userrole = 'role-teachercolleague';
        }
        if ($ismanager) {
            $userrole = 'role-manager';
        }
        if (has_capability('moodle/site:config', \context_system::instance())) {
            $userrole = 'role-admin';
        }

        return $userrole;
    }

    /**
     * Add dark_mod class to body
     * @return string
     */
    public static function add_dark_class_to_body(){
        global $USER;

        $user_dark_mod = get_user_preferences('dark_mode', '', $USER->id);
        if($user_dark_mod){
            return 'dark_mode';
        }

        return '';
    }

    /**
     * Add dark_mod class to body
     * @return boolean
     */
    public static function block_expanded_in_course(){
        global $USER, $PAGE;

        $result = false;

        if (get_config('theme_petel', 'blockexpanded')) {
            if (get_user_preferences('blockexpanded', '', $USER->id)) {
                $result = false;
            } else {
                $result = true;
            }
        }

        if(in_array($PAGE->pagetype, ['mod-quiz-attempt', 'mod-quiz-review'])){
            if (get_user_preferences('quizblockexpanded', '', $USER->id)) {
                $result = false;
            } else {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Return section progress
     * @param \stdClass $course
     * @param \stdClass $section
     * @return int
     */
    public static function sectionprogress($course, $section) {
        global $DB, $USER;

        // Get all current user's completions on current course.
        $usercourseallcmcraw = $DB->get_records_sql("
                            SELECT cmc.*
                            FROM {course_modules} cm
                            INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
                            WHERE cm.course=? AND cmc.userid=?", array($course->id, $USER->id));

        $usercmscompletions = array();
        foreach ($usercourseallcmcraw as $record) {
            if ($record->completionstate <> 0) {
                $usercmscompletions[] = $record->coursemoduleid;
            }
        }

        // Get current course's completable cms.
        $ccompetablecms = array();
        $coursefminfo = get_fast_modinfo($course);
        foreach ($coursefminfo->get_cms() as $cm) {
            if ($cm->completion !== COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
                $ccompetablecms[] = $cm->id;
            }
        }

        $completedactivitiescount = 0;
        @$scms = $coursefminfo->sections[$section->section];
        if (!empty($scms)) {
            //$allcmsinsectioncount = count($scms);
            foreach ($scms as $arid => $scmid) {
                if (!in_array($scmid, $ccompetablecms)) {
                    unset($scms[$arid]);
                } else {
                    if (in_array($scmid, $usercmscompletions)) {
                        $completedactivitiescount++;
                    }
                }
            }
            $completablecmsinsectioncount = count($scms);
            if (!empty($completablecmsinsectioncount)) {
                $csectionprogress = round($completedactivitiescount / $completablecmsinsectioncount * 100);
            } else {
                $csectionprogress = 0;
            }
            return $csectionprogress;
        } else {
            return 0;
        }
    }

    /**
     * Return section progress html
     * @param object $course
     * @param object $section
     * @return string
     */
    public static function getsectionprogress($course, $section) {
        $progress = self::sectionprogress($course, $section);
        $o = html_writer::start_tag('div', array('class' => 'progressbar_wrap', 'title' => $progress.'%'));
        $o .= html_writer::tag('div', '', array('class' => 'progressbar_line', 'style' => 'width: '.$progress.'%'));
        $o .= html_writer::end_tag('div');

        return $o;
    }

    // Old function "theme_petel_if_course_oer_catalog".
    /**
     * Return if course from oer_catalog
     * @return bool
     */
    public static function if_course_oer_catalog() {
        global $PAGE;

        if ($PAGE->course->id > 1) {
            list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();
            if(in_array($PAGE->course->id, $oercourses)){
                return true;
            }
        }

        return false;
    }

    /**
     * Return if backtocourse enable
     * @return bool
     */
    public static function getbacktocourse() {
        global $PAGE;

        if ($PAGE->pagelayout === 'incourse') {
            if (!self::if_course_oer_catalog()) {
                return true;
            }
        }

        return false;
    }

    // Old function "petel_custom_messages".
    public static function custom_messages() {
        global $USER, $COURSE, $PAGE, $CFG, $DB;

        $preferencename = $CFG->instancename . 'custom_messages_course';
        $lifetime = 30*60;

        // Delete user_preference if time expired.
        if ($string =  get_user_preferences($preferencename, 0, $USER->id)) {
            $obj = json_decode($string);
            if (time() > $obj->time + $lifetime) {
                unset_user_preference($preferencename, $USER->id);
            }
        }

        if($PAGE->pagetype == 'mod-quiz-attempt'){
            $obj = new \stdClass();
            $obj->time = time();
            $obj->courseid =$COURSE->id;

            set_user_preference($preferencename,json_encode($obj), $USER->id);
        }

        if(in_array($PAGE->pagetype, ['mod-quiz-review'])){
            unset_user_preference($preferencename, $USER->id);
        }

        // Get courseid.
        $courseid = 0;
        if ($string =  get_user_preferences($preferencename, 0, $USER->id)) {
            $obj = json_decode($string);
            $courseid = $obj->courseid;
        }

        $course = $DB->get_record('course', ['id' => $courseid]);

        if (empty($course)) {
            return [false, []];
        }

        $context = \context_course::instance($courseid);
        $users = get_enrolled_users($context, 'enrol/manual:manage');

        // Check admin or teacher on course.
        $admins = [];
        foreach (get_admins() as $admin) {
            $admins[] = $admin->id;
        }

        foreach ($users as $admin) {
            $admins[] = $admin->id;
        }

        if(in_array($USER->id, array_unique($admins))){
            return [false, []];
        }

        // Find editingteacher.
        $neededusers = [];
        foreach($users as $u){
            foreach(get_user_roles($context, $u->id, true) as $role){
                if($role->shortname == 'editingteacher'){
                    $neededusers[$role->timemodified] = $role->userid;
                }
            }
        }

        krsort($neededusers);

        $d = array_values($neededusers);
        $userid = array_shift($d);

        // Prepare data.
        if(!isset($users[$userid])){
            $enable = false;
            $user = null;
        }else{
            $enable = true;
            $user = $users[$userid];
        }

        return [$enable, $user];
    }

    /**
     * Returns the instance type for the current Moodle environment.
     *
     * @return string 'petel', 'wiz', or 'other'
     */
    public static function get_instance_type() {
        global $CFG;

        $petel = ['math', 'physics', 'chemistry', 'biology', 'computerscience', 'sciences'];
        $wiz = ['feinberg', 'learnmed', 'tutorials', 'stmoodle'];

        if (in_array($CFG->instancename, $petel)) {
            return 'petel';
        } elseif (in_array($CFG->instancename, $wiz)) {
            return 'wiz';
        } else {
            return 'other';
        }
    }

}
