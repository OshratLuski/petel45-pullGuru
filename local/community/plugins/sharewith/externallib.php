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
 * @package   community_sharewith
 * @copyright 2018 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/sharewith.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class community_sharewith_external
 *
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_sharewith_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_sharewith_task_parameters() {
        return new external_function_parameters(
                array(
                        'sourcecourseid' => new external_value(PARAM_INT, 'sourcecourse id int', VALUE_DEFAULT, null),
                        'type' => new external_value(PARAM_TEXT, 'type text', VALUE_DEFAULT, null),
                        'categoryid' => new external_value(PARAM_INT, 'category id int', VALUE_DEFAULT, null),
                        'courseid' => new external_value(PARAM_INT, 'course id int', VALUE_DEFAULT, null),
                        'sectionid' => new external_value(PARAM_INT, 'section id int', VALUE_DEFAULT, null),
                        'sourcesectionid' => new external_value(PARAM_INT, 'sourcesection id int', VALUE_DEFAULT, null),
                        'sourceactivityid' => new external_value(PARAM_INT, 'sourceactivity id int', VALUE_DEFAULT, null),
                        'chain' => new external_value(PARAM_RAW, 'chain text', VALUE_DEFAULT, null),
                        'copysub' => new external_value(PARAM_TEXT, 'copysub', VALUE_DEFAULT, null),
                        'messageid' => new external_value(PARAM_INT, 'message id int', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function add_sharewith_task_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'userid' => new external_value(PARAM_INT, 'user id'),
                        'modname' => new external_value(PARAM_RAW, 'mod name'),
                        'coursename' => new external_value(PARAM_RAW, 'course name'),
                        'userfirstname' => new external_value(PARAM_RAW, 'user firstname'),
                        'userlastname' => new external_value(PARAM_RAW, 'user lastname'),
                )
        );
    }

    /**
     * Add share task
     *
     * @param int $sourcecourseid
     * @param string $type
     * @param int $categoryid
     * @param int $courseid
     * @param int $sectionid
     * @param int $sourcesectionid
     * @param int $sourceactivityid
     * @param string $chain
     * @param int $messageid
     * @return array
     */
    public static function add_sharewith_task(
            $sourcecourseid,
            $type,
            $categoryid,
            $courseid,
            $sectionid,
            $sourcesectionid,
            $sourceactivityid,
            $chain,
            $copysub,
            $messageid
    ) {
        global $USER, $DB;

        $params = self::validate_parameters(
                self::add_sharewith_task_parameters(),
                array(
                        'sourcecourseid' => $sourcecourseid,
                        'type' => $type,
                        'categoryid' => $categoryid,
                        'courseid' => $courseid,
                        'sectionid' => $sectionid,
                        'sourcesectionid' => $sourcesectionid,
                        'sourceactivityid' => $sourceactivityid,
                        'chain' => $chain,
                        'copysub' => $copysub,
                        'messageid' => $messageid,
                )
        );

        $result = array();

        // Get activity property.
        $result['modname'] = '';
        $result['coursename'] = '';

        if(!empty($sourceactivityid)) {
            try {
                list($course, $cm) = get_course_and_cm_from_cmid($sourceactivityid);
                $result['modname'] = $cm->name;
                $result['coursename'] = $course->fullname;
            } catch (Exception $e) {
                throw new \moodle_exception('error');
            }
        }

        // Get user name from messageid.
        $result['userid'] = 0;
        $result['userfirstname'] = '';
        $result['userlastname'] = '';

        if ($params['messageid'] > 0) {
            $notification = $DB->get_record('notifications', ['id' => $params['messageid']]);
            $user = $DB->get_record('user', ['id' => $notification->useridfrom]);

            $result['userid'] = $user->id;
            $result['userfirstname'] = $user->firstname;
            $result['userlastname'] = $user->lastname;
        }

        // If type wrong.
        if (!in_array($params['type'], \community_sharewith\funcs::get_sharing_types())) {
            $result['result'] = 0;
            return $result;
        }
        // Check settings parameters.
        switch ($params['type']) {
            case 'coursecopy':
                if (!get_config('community_sharewith', 'coursecopy')) {
                    $result['result'] = 1;
                    return $result;
                }
                break;
            case 'sectioncopy':
                if (!get_config('community_sharewith', 'sectioncopy')) {
                    $result['result'] = 2;
                    return $result;
                }
                break;
            case 'activitycopy':
                if (!get_config('community_sharewith', 'activitycopy')) {
                    $result['result'] = 3;
                    return $result;
                }
                break;
        }

        $metadata = [];

        if ($copysub == 1) {
            $metadata['copysub'] = true;
        }

        $bool = \community_sharewith\funcs::add_task(
                $params['type'],
                $USER->id,
                $USER->id,
                $params['sourcecourseid'],
                $params['courseid'],
                $params['sourcesectionid'],
                $params['sectionid'],
                $params['categoryid'],
                $params['sourceactivityid'],
                json_encode($metadata),
                $params['chain']
        );

        $result['result'] = $bool ? 10 : 4;

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_upload_activity_parameters() {

        return new external_function_parameters(
                array(
                        'data' => new external_value(PARAM_RAW, 'upload data'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function submit_upload_activity_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'upload result'),
                        'validation' => new external_value(PARAM_BOOL, 'upload result'),
                        'errors' => new external_value(PARAM_RAW, 'upload result'),
                )
        );
    }

    /**
     * Submit upload activity
     *
     * @param string $data
     * @return array
     */
    public static function submit_upload_activity($data) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::submit_upload_activity_parameters(),
                array(
                        'data' => $data,
                )
        );

        $data = (array) json_decode($params['data']);
        $sharewith = new sharewith();
        $sharewith->setactivityid($data['activityid'], $data['courseid']);
        $sharewith->prepare_active_fields();

        // Prepare data.
        foreach ($sharewith->get_active_fields() as $field) {
            // Convert textarea.
            if ($field->datatype == 'textarea') {
                foreach ($data as $key => $item) {
                    if (strpos($key, $field->shortname) !== false && strpos($key, '[text]') !== false) {
                        $data[$field->shortname] = (!empty(trim(strip_tags($item)))) ? trim($item) : '';
                    }
                }
            }

            // Convert fileupload.
            if ($field->datatype == 'fileupload') {
                foreach ($data as $key => $item) {
                    if ($key == $field->shortname) {
                        $files = file_get_drafarea_files(trim($item), '/');
                        if (empty($files->list)) {
                            $data[$field->shortname] = '';
                        }
                    }
                }
            }
        }

        // Validation.
        $errors = [];
        foreach ($sharewith->get_active_fields() as $field) {
            if (in_array($field->datatype, ['selectsections', 'originality'])) {
                $field->required = false;
            }

            if ($field->required) {
                if (!isset($data[$field->shortname]) || strlen(trim($data[$field->shortname])) == 0) {
                    $errors[] = $field->shortname;
                }
            }

            // Check originality.
            if ($field->datatype == 'originality') {
                if (isset($data['question_activity']) && isset($data['question_activity_url'])) {
                    if ($data['question_activity'] == 'true') {
                        if (empty(trim($data['question_activity_url']))) {
                            $errors[] = $field->shortname;
                        }
                    }
                }
            }

            // If select section empty.
            if ($field->datatype == 'selectsections') {
                if (empty($data['selected_sections'])) {
                    $errors[] = 'selected_sections';
                }

                // Do not ask user to choose competencies: If select competency empty Or disabled.
                $requestcompetencies = false;
                $competenciescohort = get_config('community_oer', 'addcompetenciescohort');
                if ((int) $competenciescohort > 0 && $DB->record_exists('cohort_members',
                                array('cohortid' => $competenciescohort, 'userid' => $USER->id))) {
                    $requestcompetencies = true;
                }

                // PTL-8556.
                $requestcompetencies = false;

                if (!empty($data['selected_sections'])) {
                    foreach ($data['selected_sections'] as $item) {
                        if ($requestcompetencies) {

                            if (get_config('community_sharewith', 'showncompetencysection') != 1) {
                                $coursecompetencies = core_competency\api::list_course_competencies($item->course_id);
                                if (!empty($coursecompetencies)) {
                                    $flag = true;
                                    foreach ($data['selected_competencies'] as $comp) {
                                        if ($comp->section_id == $item->section_id) {
                                            $flag = false;
                                        }
                                    }

                                    if ($flag) {
                                        $errors[] = 'selected_competencies';
                                    }
                                }
                            } else {
                                $sectioncompetencies = [];
                                $row = $DB->get_record('course_sections', ['id' => $item->section_id]);
                                foreach (explode(',', $row->sequence) as $cmid) {
                                    if (!empty($cmid) && is_numeric($cmid)) {
                                        $cmcompetencies = core_competency\api::list_course_module_competencies($cmid);
                                        foreach ($cmcompetencies as $comp) {
                                            $sectioncompetencies[] = $comp['competency']->get('id');
                                        }
                                    }
                                }

                                if (!empty($sectioncompetencies)) {
                                    $flag = true;
                                    foreach ($data['selected_competencies'] as $comp) {
                                        if ($comp->section_id == $item->section_id) {
                                            $flag = false;
                                        }
                                    }

                                    if ($flag) {
                                        $errors[] = 'selected_competencies';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $result = true;

        // Checkbox agree to copyright.
        $agreetocopyright = get_user_preferences('activity_agree_to_copyright');

        if (!$agreetocopyright) {
            if (!isset($data['agreetocopyright'])) {
                $errors[] = 'agreetocopyright';
            }
        }

        $errors = array_filter($errors);
        $errors = array_values($errors);

        if (empty($errors)) {

            // Save agree to copyright.
            if (!$agreetocopyright) {
                set_user_preferences(['activity_agree_to_copyright' => 1]);
            }

            $result = $sharewith->savemanyactivitytomaagar($data);
        }

        $result = array(
                'result' => $result,
                'validation' => count($errors) ? false : true,
                'errors' => json_encode($errors),
        );

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_saveactivity_task_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id int', VALUE_DEFAULT, null),
                        'sectionid' => new external_value(PARAM_INT, 'section id int', VALUE_DEFAULT, null),
                        'shareid' => new external_value(PARAM_INT, 'shareid int', VALUE_DEFAULT, null),
                        'type' => new external_value(PARAM_TEXT, 'type text', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function add_saveactivity_task_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'text' => new external_value(PARAM_TEXT, 'result text'),
                )
        );
    }

    /**
     * Add task for saving new activity
     *
     * @param int $courseid
     * @param int $sectionid
     * @param int $shareid
     * @param string $type
     * @return array
     */
    public static function add_saveactivity_task($courseid, $sectionid, $shareid, $type) {
        global $USER;

        $params = self::validate_parameters(
                self::add_saveactivity_task_parameters(),
                array(
                        'courseid' => $courseid,
                        'sectionid' => $sectionid,
                        'shareid' => $shareid,
                        'type' => $type,
                )
        );

        $result = array();

        // If type wrong.
        if (!in_array($params['type'], \community_sharewith\funcs::get_sharing_types())) {
            $result['result'] = 0;
            $result['text'] = 'wrong type';
            return $result;
        }
        // Check settings parameters.
        switch ($params['type']) {
            case 'coursecopy':
                if (!get_config('community_sharewith', 'coursecopy')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t copy course';
                    return $result;
                }
                break;
            case 'sectioncopy':
                if (!get_config('community_sharewith', 'sectioncopy')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t copy section';
                    return $result;
                }
                break;
            case 'activityshare':
                if (!get_config('community_sharewith', 'activitysending')) {
                    $result['result'] = 0;
                    $result['text'] = 'can\'t share activity';
                    return $result;
                }
                break;
        }

        $result = \community_sharewith\funcs::save_task($params['type'], $params['shareid'], $params['courseid'], $params['sectionid']);

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_categories_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_categories_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'categories' => new external_value(PARAM_RAW, 'json categories'),
                )
        );
    }

    /**
     * Get categories
     *
     * @return array
     */
    public static function get_categories($courseid) {

        $params = self::validate_parameters(
                self::add_saveactivity_task_parameters(),
                array(
                        'courseid' => $courseid,
                )
        );

        $result = array();
        $categories = \community_sharewith\funcs::get_categories($courseid);

        switch (count($categories)) {
            case 0:
                $result['result'] = 0;
                $result['categories'] = json_encode($categories);
                break;
            case 1:
                $result['result'] = 2;
                $result['categories'] = json_encode($categories);;
                break;
            default:
                $result['result'] = 1;
                $result['categories'] = json_encode($categories);
        }

        // PTL-4381. Admin can select categories.
        $result['result'] = is_siteadmin() ? 1 : 2;

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_courses_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'courses' => new external_value(PARAM_RAW, 'json categories'),
                )
        );
    }

    /**
     * Get courses
     *
     * @return array
     */
    public static function get_courses() {
        $result = array();

        $courses = \community_sharewith\funcs::get_courses();

        if (!empty($courses)) {
            $result['result'] = 1;
            $result['courses'] = json_encode($courses);
        } else {
            $result['result'] = 0;
            $result['courses'] = '';
        }
        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_sectionid_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id'),
                        'firstcmid' => new external_value(PARAM_INT, 'cmid of the first mod in the section'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_sectionid_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'result bool'),
                        'sectionid' => new external_value(PARAM_INT, 'sectionid'),
                )
        );
    }

    /**
     * Get section id
     *
     * @return array
     */
    public static function get_sectionid($courseid, $firstcmid) {
        global $DB;

        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $result = [];
        $result['result'] = false;
        $result['sectionid'] = 0;

        if ($cmod = $DB->get_record('course_modules', array('id' => $firstcmid))) {
            $result['result'] = true;
            $result['sectionid'] = $cmod->section;
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_sections_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'result bool'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_sections_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                        'sections' => new external_value(PARAM_TEXT, 'json categories'),
                )
        );
    }

    /**
     * Get sections
     *
     * @param int $courseid
     * @return array
     */
    public static function get_sections($courseid) {
        $result = array();

        $sections = \community_sharewith\funcs::get_section_bycourse($courseid);

        if (!empty($sections)) {
            $result['result'] = 1;
            $result['sections'] = json_encode($sections);
        } else {
            $result['result'] = 0;
            $result['sections'] = json_encode($sections);
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_sections_html_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Cource ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_sections_html_returns() {
        return new external_single_structure(
                array(
                        'sections' => new external_value(PARAM_RAW, 'Html sections'),
                        'competencies' => new external_value(PARAM_RAW, 'competencies'),
                        'competencieshtml' => new external_value(PARAM_RAW, 'competencieshtml'),
                )
        );
    }

    /**
     * Get sections
     *
     * @param int $courseid
     * @return array
     */
    public static function get_sections_html($courseid) {
        global $OUTPUT, $USER;
        $result = array();

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!empty($courseid)) {
            $popupdata = new sharewith();
            $templatecontext['sections'] = $popupdata->get_sections_by_course($courseid);
            $html = $OUTPUT->render_from_template('community_sharewith/sections', $templatecontext);

            $coursecompetencies = core_competency\api::list_course_competencies($courseid);
            $cca = [];
            foreach ($coursecompetencies as $key => $comp) {
                $cca[$comp['competency']->get('id')] = $comp['competency']->get('shortname');
            }

            $compcontext = new stdClass();
            $compcontext->course_id = $courseid;
            $compcontext->competencies = $cca;

            $competencieshtml = $OUTPUT->render_from_template('community_sharewith/competencies', $compcontext);

            $result['sections'] = $html;
            $result['competencies'] = json_encode($cca);
            $result['competencieshtml'] = $competencieshtml;
        } else {
            $result['sections'] = '';
        }

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_community_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_community_returns() {
        return new external_value(PARAM_RAW, 'Community form');
    }

    /**
     * Get teachers list
     *
     * @param int $activityid
     * @param int $courseid
     * @return string
     */
    public static function get_community($activityid, $courseid) {

        $params = self::validate_parameters(
                self::get_community_parameters(),
                array(
                        'activityid' => $activityid,
                        'courseid' => $courseid,
                )
        );

        return \community_sharewith\funcs::get_share_courses($params['activityid'], $params['courseid']);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_teachers_returns() {
        return new external_value(PARAM_RAW, 'Teachers form');
    }

    /**
     * Get teachers list
     *
     * @param int $activityid
     * @param int $courseid
     * @return string
     */
    public static function get_teachers($activityid, $courseid) {

        $params = self::validate_parameters(
                self::get_teachers_parameters(),
                array(
                        'activityid' => $activityid,
                        'courseid' => $courseid,
                )
        );

        return \community_sharewith\funcs::get_shared_teachers($params['activityid'], $params['courseid']);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function autocomplete_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'searchstring' => new external_value(PARAM_TEXT, 'Search string'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function autocomplete_teachers_returns() {
        return new external_value(PARAM_RAW, 'Teachers list');
    }

    /**
     * Get teachers list
     *
     * @param int $activityid
     * @param int $courseid
     * @param string $searchstring
     * @return string
     */
    public static function autocomplete_teachers($searchstring) {

        $params = self::validate_parameters(
                self::autocomplete_teachers_parameters(),
                array(
                        'searchstring' => $searchstring,
                )
        );

        $teachers = \community_sharewith\funcs::autocomplete_teachers($params['searchstring']);

        return $teachers;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'activityid' => new external_value(PARAM_INT, 'Activity ID'),
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                        'teachersid' => new external_value(PARAM_RAW, 'Teachers ID'),
                        'coursesid' => new external_value(PARAM_RAW, 'courses ID'),
                        'message' => new external_value(PARAM_TEXT, 'Message to teacher'),
                        'sequence' => new external_value(PARAM_RAW, 'Activities sequence'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function submit_teachers_returns() {
        return new external_value(PARAM_RAW, 'Send activity result');
    }

    /**
     * Submit activity to teachers
     *
     * @param int $activityid
     * @param int $courseid
     * @param int $teachersid
     * @param string $message
     * @return int
     */
    public static function submit_teachers($activityid, $courseid, $teachersid, $coursesid, $message, $sequence) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::submit_teachers_parameters(),
                array(
                        'activityid' => $activityid,
                        'courseid' => $courseid,
                        'teachersid' => $teachersid,
                        'coursesid' => $coursesid,
                        'message' => $message,
                        'sequence' => $sequence,
                )
        );

        $teachers = \community_sharewith\funcs::submit_teachers(
                $params['activityid'],
                $params['courseid'],
                $params['teachersid'],
                $params['coursesid'],
                $params['message'],
                $params['sequence']
        );

        return $teachers;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function check_cm_status_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'Activity ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function check_cm_status_returns() {
        return new external_single_structure(
                array(
                        'cmstatus' => new external_value(PARAM_TEXT, 'mod status'),
                        'haveviewlink' => new external_value(PARAM_BOOL, 'have view link'),
                        'data' => new external_value(PARAM_RAW, 'json'),
                )
        );
    }

    /**
     * Check chain
     *
     * @param int $cmid
     * @return array
     */
    public static function check_cm_status($cmid) {
        global $USER, $CFG, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::check_cm_status_parameters(),
                array(
                        'cmid' => $cmid,
                )
        );

        $activity = $DB->get_record('course_modules', array('id' => $cmid));
        $modinfo = get_fast_modinfo($activity->course);
        $modname = $modinfo->get_cm($cmid)->modname;

        $haveviewlink = true;
        if ($modname == 'label') {
            $haveviewlink = false;
        }

        if ($modname == 'quiz') {
            $sharewith = new sharewith();
            if (!$sharewith->check_quiz_category($cmid)) {
                $content = array(
                        'cmstatus' => 'wrongquizcategory',
                        'haveviewlink' => $haveviewlink,
                        'data' => get_string('error_quiz_category', 'community_sharewith')
                );
                return $content;
            }
        }

        require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/duplicate.php');
        $lib = new \duplicate();
        $chains = $lib->get_activities_chain($cmid, $activity->course);

        $result = false;
        $items = array();
        if (count($chains) > 1) {
            $result = 'chain';

            for ($i = 0; $i < count($chains); $i++) {

                $cm = $modinfo->cms[$chains[$i]->id];
                $tmp = array(
                        'cmid' => $chains[$i]->id,
                        'name' => $cm->name
                );

                $items[] = $tmp;
            }
        }

        $content = [
                'cmstatus' => $result,
                'haveviewlink' => $haveviewlink,
                'data' => json_encode([
                        'chains' => $items
                ])
        ];

        return $content;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_amit_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'course module ID'),
                        'courseid' => new external_value(PARAM_INT, 'course ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_amit_teacher_returns() {
        return new external_single_structure(
                array(
                        'isamit' => new external_value(PARAM_BOOL, 'result bool'),
                        'amit' => new external_value(PARAM_RAW, 'json'),
                )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $cmid
     * @return array
     */
    public static function get_amit_teacher($cmid, $courseid) {
        global $USER, $DB;

        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $isactivityshared = false;
        $query = '
            SELECT *
            FROM {community_oer_log} ol
            WHERE ol.activityid=?
                AND ol.userid=?
                AND ol.courseid=?';

        $sectiondata = $DB->get_records_sql($query, [$cmid, $USER->id, $courseid]);

        if (count($sectiondata)) {
            $isactivityshared = true;
        }

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $teachers = get_role_users($role->id, $context);
        $teacher = array_shift($teachers);

        $cms = get_fast_modinfo($courseid);
        $course = $cms->get_course();
        $cminfo = $cms->get_cm($cmid);

        $data = new \stdClass;
        $data->activity_shared = $isactivityshared;
        $data->teacherid = $teacher->id;
        $data->modname = $cminfo->name;
        $data->coursename = $course->shortname;

        $content = array(
                'isamit' => true,
                'amit' => json_encode($data)
        );

        return $content;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_oercatalog_hierarchy_parameters() {
        return new external_function_parameters(
                array(
                        'selected' => new external_value(PARAM_RAW, 'selected category, course, section, '),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_oercatalog_hierarchy_returns() {
        return new external_single_structure(
                array(
                        'hierarchy' => new external_value(PARAM_RAW, 'hierarchy oercatalog json'),
                )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $cmid
     * @return array
     */
    public static function get_oercatalog_hierarchy($selected) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_oercatalog_hierarchy_parameters(),
                array(
                        'selected' => $selected,
                )
        );

        // Do not ask user to choose competencies if it is disabled.
        $requestcompetencies = false;
        $competenciescohort = get_config('community_oer', 'addcompetenciescohort');
        if ((int) $competenciescohort > 0 && $DB->record_exists('cohort_members',
                        array('cohortid' => $competenciescohort, 'userid' => $USER->id))) {
            $requestcompetencies = true;
        }

        $showncompetencysection = get_config('community_sharewith', 'showncompetencysection') == 1 ? true : false;

        $selected = json_decode($params['selected']);
        $result = [];
        foreach (\community_oer\main_oer::structure_main_catalog() as $category) {
            $tmp = [];
            $tmp['cat_id'] = $category['cat_id'];
            $tmp['cat_name'] = $category['cat_name'];

            foreach ($category['courses'] as $course) {
                $relevantsections = [];

                $sql = "
                    SELECT *
                    FROM {course_sections}
                    WHERE visible = 1 AND section !=0 AND course = ?
                ";

                foreach ($DB->get_records_sql($sql, [$course->id]) as $section) {
                    if (!in_array($section->id, $selected)) {

                        $modinfo = get_fast_modinfo($course->id);
                        $current = $modinfo->get_section_info($section->section)->getIterator()->getArrayCopy();

                        if (!isset($current['parent'])) {
                            $current['parent'] = 0;
                        }

                        if ($current['parent'] == 0) {
                            $tmpsection = [];
                            $tmpsection['section_id'] = $section->id;
                            $tmpsection['section_name'] = get_section_name($course->id, $section->section);

                            $tmpcompetencies = [];
                            if ($showncompetencysection) {
                                if ($requestcompetencies) {
                                    foreach (explode(',', $current['sequence']) as $cmid) {
                                        if (!empty($cmid) && is_numeric($cmid)) {
                                            $cmcompetencies = core_competency\api::list_course_module_competencies($cmid);
                                            foreach ($cmcompetencies as $comp) {
                                                if (!empty($comp['competency']->get('id')) &&
                                                        !empty($comp['competency']->get('shortname'))) {
                                                    $cca = [
                                                            'competency_id' => $comp['competency']->get('id'),
                                                            'competency_name' => $comp['competency']->get('shortname')
                                                    ];

                                                    $tmpcompetencies[] = $cca;
                                                }
                                            }
                                        }
                                    }
                                }

                                // TODO Need to remove.
                                //$tmpcompetencies = [
                                //    ['competency_id' => 1, 'competency_name' => $section->id.'sectioncomp1'], ['competency_id' => 2, 'competency_name' => $section->id.'sectioncomp2',],
                                //    ['competency_id' => 3, 'competency_name' => $section->id.'sectioncomp3'], ['competency_id' => 4, 'competency_name' => $section->id.'sectioncomp4',],
                                //    ['competency_id' => 5, 'competency_name' => $section->id.'sectioncomp5'], ['competency_id' => 6, 'competency_name' => $section->id.'sectioncomp6',],
                                //    ['competency_id' => 7, 'competency_name' => $section->id.'sectioncomp7'], ['competency_id' => 8, 'competency_name' => $section->id.'sectioncomp8',],
                                //    ['competency_id' => 9, 'competency_name' => $section->id.'sectioncomp9'], ['competency_id' => 10, 'competency_name' => $section->id.'sectioncomp10',],
                                //];
                            }

                            $tmpsection['section_competency'] = $tmpcompetencies;

                            $relevantsections[] = $tmpsection;
                        }
                    }
                }

                if (empty($relevantsections)) {
                    continue;
                }

                $tmpcorses = [];
                $tmpcorses['course_id'] = $course->id;
                $tmpcorses['course_name'] = $course->fullname;
                $tmpcorses['sections'] = $relevantsections;
                $tmpcorses['competencies'] = [];

                if (!$showncompetencysection) {
                    if ($requestcompetencies) {
                        $coursecompetencies = core_competency\api::list_course_competencies($course->id);
                        foreach ($coursecompetencies as $key => $comp) {
                            if (!empty($comp['competency']->get('id')) && !empty($comp['competency']->get('shortname'))) {
                                $cca = [
                                        'competency_id' => $comp['competency']->get('id'),
                                        'competency_name' => $comp['competency']->get('shortname')
                                ];

                                $tmpcorses['competencies'][] = $cca;
                            }
                        }
                    }

                    // TODO Need to remove.
                    //$tmpcorses['competencies'] = [
                    //    ['competency_id' => 11, 'competency_name' => 'comp11'], ['competency_id' => 12, 'competency_name' => 'comp12',],
                    //    ['competency_id' => 13, 'competency_name' => 'comp13'], ['competency_id' => 14, 'competency_name' => 'comp14',],
                    //    ['competency_id' => 15, 'competency_name' => 'comp15'], ['competency_id' => 16, 'competency_name' => 'comp16',],
                    //    ['competency_id' => 17, 'competency_name' => 'comp17'], ['competency_id' => 18, 'competency_name' => 'comp18',],
                    //    ['competency_id' => 19, 'competency_name' => 'comp19'], ['competency_id' => 110, 'competency_name' => 'comp110',],
                    //];
                }

                $tmp['courses'][] = $tmpcorses;
            }

            $result['categories'][] = $tmp;
        }

        $content = array(
                'hierarchy' => json_encode($result)
        );

        return $content;
    }
}
