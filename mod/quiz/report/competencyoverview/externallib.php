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
 * External interface library for customfields component
 *
 * @package   quiz_competencyoverview
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/mod/quiz/report/competencyoverview/locallib.php';
require_once $CFG->dirroot . '/course/externallib.php';

/**
 * Class quiz_competencyoverview_external
 *
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_competencyoverview_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_courses_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'courses' => new external_value(PARAM_TEXT, 'json categories'),
            )
        );
    }

    /**
     * Get courses
     * @return array
     */
    public static function get_courses() {
        $result = array();

        $courses = quiz_competencyoverview_get_courses();

        if (!empty($courses)) {
            $result['result'] = 1;
            $result['courses'] = json_encode($courses);
        } else {
            $result['result'] = 0;
            $result['courses'] = json_encode($courses);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_activities_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'courseid'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_activities_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'activities' => new external_value(PARAM_TEXT, 'json categories'),
            )
        );
    }

    /**
     * Get activities
     * @return array
     */
    public static function get_activities($courseid) {

        $modinfo = get_fast_modinfo($courseid);
        $result = array();

        $activities = [];

        foreach ($modinfo->get_cms() as $cm) {
            $activities[] = [
                'id' => $cm->id, 'shortname' => $cm->name, 'modname' => $cm->modname,
                'section' => $cm->section, 'sectionnum' => $cm->sectionnum,
            ];
        }

        if (!empty($activities)) {
            $result['result'] = 1;
            $result['activities'] = json_encode($activities);
        } else {
            $result['result'] = 0;
            $result['activities'] = json_encode($activities);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_items_parameters() {
        return new external_function_parameters(
            array(
                'skills' => new external_value(PARAM_TEXT, 'skills'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_items_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'items' => new external_value(PARAM_RAW, 'json categories'),
            )
        );
    }

    /**
     * Get items
     * @return array
     */
    public static function get_items($skills) {
        $result = array();
        require_login();
        $items = quiz_competencyoverview_get_items(explode(',', $skills));

        if (!empty($items)) {
            $result['result'] = 1;
            $result['items'] = json_encode($items);
        } else {
            $result['result'] = 0;
            $result['items'] = json_encode($items);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_item_parameters() {
        return new external_function_parameters(
            array(
                'itemid' => new external_value(PARAM_INT, 'itemid'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_item_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'item' => new external_value(PARAM_RAW, 'json categories'),
            )
        );
    }

    /**
     * Get items
     * @return array
     */
    public static function get_item($itemid) {
        $result = array();

        $item = quiz_competencyoverview_get_item($itemid, false);

        if (!empty($item)) {
            $result['result'] = 1;
            $result['item'] = json_encode($item);
        } else {
            $result['result'] = 0;
            $result['item'] = json_encode($item);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_targetsections_parameters() {
        return new external_function_parameters(
            array(
                'currentcourseid' => new external_value(PARAM_INT, 'currentcourseid'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_targetsections_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_INT, 'result bool'),
                'sections' => new external_value(PARAM_TEXT, 'json categories'),
                'coursename' => new external_value(PARAM_TEXT, 'coursename'),
                'imgurl' => new external_value(PARAM_URL, 'imgurl'),
            )
        );
    }

    /**
     * Get targetsections
     * @return array
     */
    public static function get_targetsections($currentcourseid) {
        global $CFG, $PAGE;

        $modinfo = get_fast_modinfo($currentcourseid);
        $result = array();
        $modinfosections = $modinfo->get_section_info_all();
        $sections = [];

        foreach ($modinfosections as $s) {
            $sections[] = ['id' => $s->id, 'name' => $s->name, 'section' => $s->section];
        }

        $course = get_course($currentcourseid);
        $coursename = $course->fullname;
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $imgurl = '';
        $noimgurl = '';
        $context = context_course::instance($course->id);
        $PAGE->set_context($context);

        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $imgurl = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if (!$isimage) {
                $imgurl = $noimgurl;
            }
        }
        if (empty($imgurl)) {
            $imgurl = $PAGE->theme->setting_file_url('headerdefaultimage', 'headerdefaultimage', true);
            if (!$imgurl) {
                $imgurl = $noimgurl;
            }
        }

        if (!empty($sections)) {
            $result['result'] = 1;
            $result['sections'] = json_encode($sections);
            $result['imgurl'] = $imgurl;
            $result['coursename'] = $coursename;
        } else {
            $result['result'] = 0;
            $result['sections'] = json_encode($sections);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function submit_assignment_parameters() {
        return new external_function_parameters(
            array(
                'selectedusersjoin' => new external_value(PARAM_SEQUENCE, 'selectedusersjoin'),
                'selectedsource' => new external_value(PARAM_ALPHANUM, 'selectedsource'),
                'selectedcourse' => new external_value(PARAM_INT, 'selectedcourse', VALUE_OPTIONAL),
                'selectedactivity' => new external_value(PARAM_INT, 'selectedactivity', VALUE_OPTIONAL),
                'selecteditem' => new external_value(PARAM_INT, 'selecteditem', VALUE_OPTIONAL),
                'selectedtargetsection' => new external_value(PARAM_INT, 'selectedtargetsection'),
                'messagetostudents' => new external_value(PARAM_TEXT, 'messagetostudents', VALUE_OPTIONAL),
                'currentcourseid' => new external_value(PARAM_INT, 'currentcourseid', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function submit_assignment_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'result bool'),
            )
        );
    }

    /**
     * Submit assignment
     * @return array
     */
    public static function submit_assignment(
        $selectedusersjoin,
        $selectedsource,
        $selectedcourse,
        $selectedactivity,
        $selecteditem,
        $selectedtargetsection,
        $messagetostudents,
        $currentcourseid
    ) {
        global $USER, $DB;

        $success = false;

        switch ($selectedsource) {
            case 'course':
                $item = $selectedactivity;
                break;
            case 'repository':
                $item = $selecteditem;
                break;
            default:
                break;
        }

        $conditions = [
            'sourceactivityid' => $item,
            'courseid' => $currentcourseid,
            'sectionid' => $selectedtargetsection,
        ];
        $metadata = [
            'callbackpath' => '/mod/quiz/report/competencyoverview/locallib.php',
            'callbackfunc' => 'quiz_competencyoverview_message_to_students',
            'message' => $messagetostudents,
            'students' => $selectedusersjoin,
            'conditions' => $conditions,
        ];

        // Check for exist activity.
        $aa = $DB->get_record('quiz_competencyoverview_aa', $conditions);

        if ($aa) {
            // TODO Only restrict.
            quiz_competencyoverview_message_to_students($metadata, $aa->activityid);
            $success = true;
        } else {
            // Copy.
            $metadata = json_encode($metadata);
            $type = 'activitycopy';

            if (class_exists('\community_sharewith\funcs')) {
                $addtask = \community_sharewith\funcs::add_task(
                        $type,
                        $USER->id,
                        $USER->id,
                        $selectedcourse,
                        $currentcourseid,
                        null,
                        $selectedtargetsection,
                        null,
                        $item,
                        $metadata
                );
            }
        }

        if (!empty($success)) {
            $result['result'] = 1;
        } else {
            $result['result'] = 0;
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_questions_by_competency_table_parameters() {
        return new external_function_parameters(
            array(
                'compid' => new external_value(PARAM_INT, 'compid'),
                'cmid' => new external_value(PARAM_INT, 'cmid'),
                'quizid' => new external_value(PARAM_INT, 'quizid'),
                'courseid' => new external_value(PARAM_INT, 'courseid'),
                'qset' => new external_value(PARAM_SEQUENCE, 'qset'),
                'lastaccess' => new external_value(PARAM_INT, 'lastaccess'),
                'actualusers' => new external_value(PARAM_SEQUENCE, 'actualusers'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_questions_by_competency_table_returns() {
        return new external_single_structure(
            array(
                // 'result' => new external_value(PARAM_INT, 'result bool'),
                'questionstable' => new external_value(PARAM_RAW, 'questionstable html'),
            )
        );
    }

    /**
     * Get items
     * @return array
     */
    public static function get_questions_by_competency_table($compid, $cmid, $quizid, $courseid, $qset, $lastaccess, $actualusers) {
        $result = array();

        $questionstable = quiz_competencyoverview_get_questions_by_competency_table($compid, $cmid, $quizid, $courseid, $qset, $lastaccess, $actualusers);

        if (!empty($questionstable)) {
            $result['result'] = 1;
            $result['questionstable'] = json_encode($questionstable);
        } else {
            $result['result'] = 0;
            $result['questionstable'] = json_encode($questionstable);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_init_params_parameters() {
        return new external_function_parameters(
            array(
                'quizid' => new external_value(PARAM_INT, 'quizid'),
                'cmid' => new external_value(PARAM_INT, 'cmid'),
                'courseid' => new external_value(PARAM_INT, 'courseid'),
                'lastaccess' => new external_value(PARAM_INT, 'lastaccess'),
            )
        );
    }

    /**
     * Returns result
     * @return object
     */
    public static function get_init_params_returns() {
        return new external_single_structure(
            array(
                'params' => new external_value(PARAM_TEXT, 'json params'),
            )
        );
    }

    /**
     * Get courses
     * @return array
     */
    public static function get_init_params($quizid, $cmid, $courseid, $lastaccess) {
        $result = array();

        $params = quiz_competencyoverview_get_init_params($quizid, $cmid, $courseid, $lastaccess);

        if (!empty($params)) {
            $result['params'] = json_encode($params);
        } else {
            $result['params'] = json_encode($params);
        }

        return $result;
    }

}
