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
 * External functions backported.
 *
 * @package    community_sharequestion
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/course/lib.php');

class community_sharecourse_external extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_upload_course_parameters() {

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
    public static function submit_upload_course_returns() {
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
    public static function submit_upload_course($data) {
        global $USER, $CFG;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::submit_upload_course_parameters(),
                array(
                        'data' => $data,
                )
        );

        require_once($CFG->dirroot . '/local/community/plugins/sharecourse/classes/sharecourse.php');

        $data = (array) json_decode($params['data']);
        $sharecourse = new \community_sharecourse\sharecourse();
        $sharecourse->prepare_active_fields();

        // Prepare data.
        foreach ($sharecourse->get_active_fields() as $field) {
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
        foreach ($sharecourse->get_active_fields() as $field) {
            if (in_array($field->datatype, ['selectcourses', 'originality'])) {
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
                        if (empty($data['question_activity_url']) ||
                                !filter_var($data['question_activity_url'], FILTER_VALIDATE_URL)) {
                            $errors[] = $field->shortname;
                        }
                    }
                }
            }

            // If select section empty.
            if ($field->datatype == 'selectcourses') {
                if (empty($data['selected_courses'])) {
                    $errors[] = 'selected_courses';
                }
            }
        }

        $result = true;

        $errors = array_filter($errors);
        $errors = array_values($errors);

        if (empty($errors)) {
            $result = $sharecourse->save_many_courses_tocatalog($data);
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
    public static function unshare_course_parameters() {

        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function unshare_course_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

    /**
     * Submit upload activity
     *
     * @param string $data
     * @return string
     */
    public static function unshare_course($courseid) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::unshare_course_parameters(),
                array(
                        'courseid' => $courseid,
                )
        );

        \local_metadata\mcontext::course()->saveEmpty($params['courseid'], 'csubject');
        \local_metadata\mcontext::course()->save($params['courseid'], 'chidden', 1);

        $DB->delete_records('community_oer_course', ['cid' => $params['courseid']]);

        $course = new \community_oer\course_oer();
        $course->recalculate_data_in_cache();

        // Unenrol users.
        $sharecourse = new \community_sharecourse\sharecourse();
        $sharecourse->unenrol_course($params['courseid']);

        // Event.
        $eventdata = array(
                'userid' => $USER->id,
                'currentcourseid' => $params['courseid'],
        );
        \community_sharecourse\event\course_unshare::create_event($params['courseid'], $eventdata)->trigger();

        return '';
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                        'teachersid' => new external_value(PARAM_RAW, 'Teachers ID'),
                        'coursesid' => new external_value(PARAM_RAW, 'Courses ID', VALUE_DEFAULT, ''),
                        'message' => new external_value(PARAM_TEXT, 'Message to teacher'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function submit_teachers_returns() {
        return new external_value(PARAM_RAW, 'Send submit teachers result');
    }

    /**
     * Submit activity to teachers
     *
     * @param int $courseid
     * @param string $teachersid
     * @param string $coursesid
     * @param string $message
     * @return string
     */
    public static function submit_teachers($courseid, $teachersid, $coursesid, $message) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::submit_teachers_parameters(),
                array(
                        'courseid' => $courseid,
                        'teachersid' => $teachersid,
                        'coursesid' => $coursesid,
                        'message' => $message,
                )
        );

        \community_sharecourse\funcs::submit_teachers(
                $params['courseid'],
                $params['teachersid'],
                $params['coursesid'],
                $params['message']
        );

        return json_encode(['result' => true]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_copy_course_parameters() {
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
    public static function popup_copy_course_returns() {
        return new external_single_structure(
                array(
                        'isadmin' => new external_value(PARAM_BOOL, 'isadmin bool'),
                        'categories' => new external_value(PARAM_RAW, 'json categories'),
                        'typeshare' => new external_value(PARAM_BOOL, 'typeshare bool'),
                        'teachercatid' => new external_value(PARAM_INT, 'teacher cat id int')
                )
        );
    }

    /**
     * Get categories
     *
     * @return array
     */
    public static function popup_copy_course($courseid) {
        global $USER, $DB;

        $params = self::validate_parameters(
                self::popup_copy_course_parameters(),
                array(
                        'courseid' => $courseid,
                )
        );

        $result = [];

        // PTL-4381. Admin can select categories.
        $result['isadmin'] = is_siteadmin() ? true : false;

        $catid = 0;
        if (!is_siteadmin()) {
            if ($cat = $DB->get_record('course_categories', ['idnumber' => $USER->idnumber])) {
                $catid = $cat->id;
            } else {
                $obj = new \StdClass();
                $obj->name = $USER->firstname . ' ' . $USER->lastname;
                $obj->idnumber = $USER->idnumber;
                $obj->parent = 0;
                $obj->visible = 1;

                $newcategory = \core_course_category::create($obj);
                $catid = $newcategory->id;
            }
        }

        $result['teachercatid'] = $catid;

        $categories = \community_sharecourse\funcs::get_categories($params['courseid']);
        $result['categories'] = json_encode($categories);

        $obj = \community_oer\course_oer::funcs()::get_course_shared($courseid);
        $result['typeshare'] = !empty($obj) && $USER->id == $obj->userid && !is_siteadmin() ? true : false;

        return $result;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_sharecourse_task_parameters() {
        return new external_function_parameters(
                array(
                        'sourcecourseid' => new external_value(PARAM_INT, 'sourcecourse id int', VALUE_DEFAULT, null),
                        'type' => new external_value(PARAM_TEXT, 'type text', VALUE_DEFAULT, null),
                        'categoryid' => new external_value(PARAM_INT, 'category id int', VALUE_DEFAULT, null),
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'metadata' => new external_value(PARAM_RAW, 'metadata', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function add_sharecourse_task_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_INT, 'result bool'),
                )
        );
    }

    /**
     * Add share task
     *
     * @param int $sourcecourseid
     * @param string $type
     * @param int $categoryid
     * @param int $userid
     * @param string $metadata
     * @return array
     */
    public static function add_sharecourse_task(
            $sourcecourseid,
            $type,
            $categoryid,
            $userid,
            $metadata
    ) {
        global $DB, $USER;

        $params = self::validate_parameters(
                self::add_sharecourse_task_parameters(),
                array(
                        'sourcecourseid' => $sourcecourseid,
                        'type' => $type,
                        'categoryid' => $categoryid,
                        'userid' => $userid,
                        'metadata' => $metadata,
                )
        );

        $userid = (!empty($userid)) ? $userid : $USER->id;

        $result = [];

        // Check settings parameters.
        if (!get_config('community_sharewith', 'coursecopy')) {
            $result['result'] = 1;
            return $result;
        }

        // Add task.
        $result['result'] = \community_sharecourse\funcs::add_task($type, $userid, $sourcecourseid, $categoryid);

        return $result;
    }
}
