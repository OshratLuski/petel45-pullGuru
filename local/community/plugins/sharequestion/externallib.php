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
 * @package     community_sharequestion
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/course/lib.php');

class community_sharequestion_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */

    public static function copy_to_quiz_html_parameters() {
        return new external_function_parameters(
                array(
                        'currentcourseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function copy_to_quiz_html($currentcourseid) {
        global $OUTPUT;

        $context = \context_system::instance();
        self::validate_context($context);

        $courses = \community_sharequestion\funcs::get_courses_for_quiz();

        $defaultkey = 0;
        foreach ($courses as $key => $item) {
            if ($item->courseid == $currentcourseid) {
                $courses[$key]->selected = true;
                $defaultkey = $key;
            } else {
                $courses[$key]->selected = false;
            }
        }

        $context = [
                'courses' => $courses,
                'activities' => isset($courses[$defaultkey]->courseid) ?
                        \community_sharequestion\funcs::get_quiz_by_course($courses[$defaultkey]->courseid) : [],
        ];

        return $OUTPUT->render_from_template('community_sharequestion/copy_questions_to_quiz', $context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function copy_to_quiz_html_returns() {
        return new external_value(PARAM_RAW, 'The html of copy questiions to quiz');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */

    public static function get_quizes_by_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function get_quizes_by_course($courseid) {

        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $params = self::validate_parameters(self::get_quizes_by_course_parameters(),
                array(
                        'courseid' => (int) $courseid,
                )
        );

        $context = [
                'activities' => \community_sharequestion\funcs::get_quiz_by_course($params['courseid']),
        ];

        return json_encode($context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_quizes_by_course_returns() {
        return new external_value(PARAM_RAW, 'Get quizes by course');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */

    public static function save_questions_to_cron_parameters() {
        return new external_function_parameters(
                array(
                        'type' => new external_value(PARAM_RAW, 'Type'),
                        'targetid' => new external_value(PARAM_INT, 'Target ID'),
                        'questionids' => new external_value(PARAM_RAW, 'Question IDs'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function save_questions_to_cron($type, $targetid, $questionids) {
        global $DB, $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::save_questions_to_cron_parameters(),
                array(
                        'type' => $type,
                        'targetid' => (int) $targetid,
                        'questionids' => $questionids,
                )
        );

        $questions = json_decode($params['questionids']);

        // Add cron and event by type.
        switch ($params['type']) {
            case 'copy_to_quiz':
                foreach ($questions as $qid) {
                    $DB->insert_record('community_sharequestion_task', [
                            'type' => 'copy_to_quiz',
                            'sourcequestionid' => $qid,
                            'targetcmid' => $params['targetid'],
                            'status' => 0,
                            'error' => '',
                            'timemodified' => time(),
                    ]);

                    $DB->insert_record('community_sharequestion_shr', [
                            'type' => 'copy_to_quiz',
                            'qid' => $qid,
                            'useridfrom' => $USER->id,
                            'timecreated' => time(),
                    ]);
                }

                $eventdata = array(
                        'userid' => $USER->id,
                        'sourcequestionids' => implode(',', $questions),
                        'cmid' => $params['targetid'],
                );

                \community_sharequestion\event\question_to_quiz_copy::create_event($eventdata)->trigger();
                break;

            case 'copy_to_category':
                foreach ($questions as $qid) {
                    $DB->insert_record('community_sharequestion_task', [
                            'type' => 'copy_to_category',
                            'sourcequestionid' => $qid,
                            'targetcatid' => $params['targetid'],
                            'status' => 0,
                            'error' => '',
                            'timemodified' => time(),
                    ]);

                    $DB->insert_record('community_sharequestion_shr', [
                            'type' => 'copy_to_category',
                            'qid' => $qid,
                            'useridfrom' => $USER->id,
                            'timecreated' => time(),
                    ]);
                }

                $eventdata = array(
                        'userid' => $USER->id,
                        'sourcequestionids' => implode(',', $questions),
                        'catid' => $params['targetid'],
                );

                \community_sharequestion\event\question_to_category_copy::create_event($eventdata)->trigger();
                break;
        }

        $context = [
                'result' => true,
        ];

        return json_encode($context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function save_questions_to_cron_returns() {
        return new external_value(PARAM_RAW, 'Get quizes by course');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */

    public static function copy_to_category_html_parameters() {
        return new external_function_parameters(
                array(
                        'currentcourseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function copy_to_category_html($currentcourseid) {
        global $OUTPUT;

        $context = \context_system::instance();
        self::validate_context($context);

        $courses = \community_sharequestion\funcs::get_courses_for_category();

        $defaultkey = 0;
        foreach ($courses as $key => $item) {
            if ($item->id == $currentcourseid) {
                $courses[$key]->selected = true;
                $defaultkey = $key;
            } else {
                $courses[$key]->selected = false;
            }
        }

        $context = [
                'courses' => $courses,
                'categories' => isset($courses[$defaultkey]->id) ?
                        \community_sharequestion\funcs::get_categories_by_course($courses[$defaultkey]->id) : [],
        ];

        return $OUTPUT->render_from_template('community_sharequestion/copy_questions_to_category', $context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function copy_to_category_html_returns() {
        return new external_value(PARAM_RAW, 'The html of copy questiions to quiz');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */

    public static function get_categories_by_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function get_categories_by_course($courseid) {

        $context = \context_course::instance($courseid);
        self::validate_context($context);

        $params = self::validate_parameters(self::get_categories_by_course_parameters(),
                array(
                        'courseid' => (int) $courseid,
                )
        );

        $context = [
                'categories' => \community_sharequestion\funcs::get_categories_by_course($params['courseid']),
        ];

        return json_encode($context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_categories_by_course_returns() {
        return new external_value(PARAM_RAW, 'Get quizes by course');
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

        $showncompetencysection = get_config('community_sharequestion', 'showncompetencysection') == 1 ? true : false;

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
                    //                $tmpcorses['competencies'] = [
                    //                    ['competency_id' => 1, 'competency_name' => 'comp1'], ['competency_id' => 2, 'competency_name' => 'comp2',],
                    //                    ['competency_id' => 3, 'competency_name' => 'comp3'], ['competency_id' => 4, 'competency_name' => 'comp4',],
                    //                    ['competency_id' => 5, 'competency_name' => 'comp5'], ['competency_id' => 6, 'competency_name' => 'comp6',],
                    //                    ['competency_id' => 7, 'competency_name' => 'comp7'], ['competency_id' => 8, 'competency_name' => 'comp8',],
                    //                    ['competency_id' => 9, 'competency_name' => 'comp9'], ['competency_id' => 10, 'competency_name' => 'comp10',],
                    //                ];
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
        global $USER, $DB, $CFG;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::submit_upload_activity_parameters(),
                array(
                        'data' => $data,
                )
        );

        require_once($CFG->dirroot . '/local/community/plugins/sharequestion/classes/sharequestion.php');

        $data = (array) json_decode($params['data']);
        $sharequestion = new sharequestion();
        $sharequestion->prepare_active_fields();

        // Prepare data.
        foreach ($sharequestion->get_active_fields() as $field) {
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
        foreach ($sharequestion->get_active_fields() as $field) {
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
                        if (empty($data['question_activity_url']) ||
                                !filter_var($data['question_activity_url'], FILTER_VALIDATE_URL)) {
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

                // If select competency empty.
                $requestcompetencies = false;
                $competenciescohort = get_config('community_oer', 'addcompetenciescohort');
                if ((int) $competenciescohort > 0 && $DB->record_exists('cohort_members',
                                array('cohortid' => $competenciescohort, 'userid' => $USER->id))) {
                    $requestcompetencies = true;
                }

                $showncompetencysection = get_config('community_sharequestion', 'showncompetencysection') == 1 ? true : false;

                if ($requestcompetencies && $showncompetencysection) {
                    if (!empty($data['selected_sections'])) {
                        foreach ($data['selected_sections'] as $item) {
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
                        }
                    }
                }
            }
        }

        $result = true;

        $errors = array_filter($errors);
        $errors = array_values($errors);

        if (empty($errors)) {
            $result = $sharequestion->save_many_questions_tocatalog($data);
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

    public static function copy_to_teacher_html_parameters() {
        return new external_function_parameters(
                array(
                        'currentcourseid' => new external_value(PARAM_INT, 'Course ID'),
                        'questionids' => new external_value(PARAM_RAW, 'Questions ID'),
                )
        );
    }

    /**
     * @return string welcome message
     */
    public static function copy_to_teacher_html($currentcourseid, $questionids) {
        global $OUTPUT;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::copy_to_teacher_html_parameters(),
                array(
                        'currentcourseid' => (int) $currentcourseid,
                        'questionids' => $questionids,
                )
        );

        $context = [
                'teachers' => []
        ];

        return $OUTPUT->render_from_template('community_sharequestion/copy_questions_to_teacher', $context);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function copy_to_teacher_html_returns() {
        return new external_value(PARAM_RAW, 'The html of copy questiions to quiz');
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
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::autocomplete_teachers_parameters(),
                array(
                        'searchstring' => $searchstring,
                )
        );

        $teachers = \community_sharequestion\funcs::autocomplete_teachers($params['searchstring']);

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
                        'questionids' => new external_value(PARAM_RAW, 'Questions ID'),
                        'teachersid' => new external_value(PARAM_RAW, 'Teachers ID'),
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
     * @param string questionids
     * @param int $teachersid
     * @param string $message
     * @return string
     */
    public static function submit_teachers($questionids, $teachersid, $message) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::submit_teachers_parameters(),
                array(
                        'questionids' => $questionids,
                        'teachersid' => $teachersid,
                        'message' => $message,
                )
        );

        \community_sharequestion\funcs::submit_teachers(
                $params['questionids'],
                $params['teachersid'],
                $params['message']
        );

        return json_encode(['result' => true]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_by_user_parameters() {
        return new external_function_parameters(
                array(
                        'search' => new external_value(PARAM_RAW, 'Search'),
                        'uniqueid' => new external_value(PARAM_INT, 'Unique ID'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_courses_by_user_returns() {
        return new external_value(PARAM_RAW, 'Get all courses by user');
    }

    /**
     * Copy from mycourses content
     *
     * @param string $search
     * @param int $uniqueid
     * @return string
     */
    public static function get_courses_by_user($search, $uniqueid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_courses_by_user_parameters(),
                array(
                        'search' => $search,
                        'uniqueid' => $uniqueid,
                )
        );

        $courses = [];
        foreach (\community_sharequestion\copy_from_mycourses::get_courses_for_current_user($params['search']) as $course) {

            if (empty(trim($params['search']))) {
                $courses[] = $course;
            } else {
                $bank = \community_sharequestion\copy_from_mycourses::get_bank_categories_by_course($course->courseid, $params['search']);
                $categories = \community_sharequestion\copy_from_mycourses::get_quiz_categories_by_course($course->courseid, $params['search']);

                if (!empty($bank) || !empty($categories)) {
                    $courses[] = $course;
                }
            }
        }

        if (!empty(trim($params['search']))) {
            $courses = array_values($courses);

            foreach ($courses as $key => $item) {
                $item->course_show = $key == 0 ? true : false;
            }
        }

        $result = [
                'uniqueid' => $uniqueid,
                'courses' => $courses,
        ];

        return json_encode(['result' => $result]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_bank_categories_on_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                        'uniqueid' => new external_value(PARAM_INT, 'Unique ID'),
                        'search' => new external_value(PARAM_RAW, 'Search'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_bank_categories_on_course_returns() {
        return new external_value(PARAM_RAW, 'Get all questions on course');
    }

    /**
     * Copy from mycourses content
     *
     * @param int $cmid
     * @param int $uniqueid
     * @return string
     */
    public static function get_bank_categories_on_course($courseid, $uniqueid, $search) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_bank_categories_on_course_parameters(),
                array(
                        'courseid' => $courseid,
                        'uniqueid' => $uniqueid,
                        'search' => $search,
                )
        );

        $result = [
                'courseid' => $courseid,
                'uniqueid' => $uniqueid,
                'categories' => \community_sharequestion\copy_from_mycourses::get_bank_categories_by_course($params['courseid'],
                        $params['search'])
        ];

        return json_encode(['result' => $result]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_question_categories_by_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                        'uniqueid' => new external_value(PARAM_INT, 'Unique ID'),
                        'search' => new external_value(PARAM_RAW, 'Search'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_question_categories_by_course_returns() {
        return new external_value(PARAM_RAW, 'Copy from my courses content');
    }

    /**
     * Copy from mycourses content
     *
     * @param int $cmid
     * @param int $uniqueid
     * @return string
     */
    public static function get_question_categories_by_course($courseid, $uniqueid, $search) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_question_categories_by_course_parameters(),
                array(
                        'courseid' => $courseid,
                        'uniqueid' => $uniqueid,
                        'search' => $search,
                )
        );

        $data = [
                'categories' => \community_sharequestion\copy_from_mycourses::get_quiz_categories_by_course($params['courseid'],
                        $params['search']),
                'uniqueid' => $params['uniqueid']
        ];

        return json_encode(['result' => $data]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_questions_by_category_parameters() {
        return new external_function_parameters(
                array(
                        'catid' => new external_value(PARAM_INT, 'Course ID'),
                        'uniqueid' => new external_value(PARAM_INT, 'Unique ID'),
                        'search' => new external_value(PARAM_RAW, 'Search'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function get_questions_by_category_returns() {
        return new external_value(PARAM_RAW, 'Copy from my courses content');
    }

    /**
     * Copy from mycourses content
     *
     * @param int $cmid
     * @param int $uniqueid
     * @return string
     */
    public static function get_questions_by_category($catid, $uniqueid, $search) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_questions_by_category_parameters(),
                array(
                        'catid' => $catid,
                        'uniqueid' => $uniqueid,
                        'search' => $search,
                )
        );

        $data = [
                'catid' => $params['catid'],
                'questions' => \community_sharequestion\copy_from_mycourses::get_questions_by_category($params['catid'],
                        $params['search']),
                'uniqueid' => $params['uniqueid']
        ];

        return json_encode(['result' => $data]);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_questions_to_quiz_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                        'qids' => new external_value(PARAM_RAW, 'Question IDs'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_value
     */
    public static function save_questions_to_quiz_returns() {
        return new external_value(PARAM_RAW, 'Copy from my courses content');
    }

    /**
     * Copy from mycourses content
     *
     * @param int $cmid
     * @param int $uniqueid
     * @return string
     */
    public static function save_questions_to_quiz($cmid, $qids) {
        global $USER, $DB, $CFG;

        require_once($CFG->libdir . '/questionlib.php');

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::save_questions_to_quiz_parameters(),
                array(
                        'cmid' => $cmid,
                        'qids' => $qids,
                )
        );

        $questions = json_decode($params['qids']);
        foreach ($questions as $qid) {
            try {
                $context = \context_module::instance($params['cmid']);
                $category = question_make_default_categories([$context]);

                $newquestionid = \community_sharequestion\duplicate_question::duplicate_single_question($qid, $category->id);
                \community_sharequestion\duplicate_question::copy_question_metadata($qid, $newquestionid);
                \community_sharequestion\duplicate_question::add_question_to_quiz($params['cmid'], $newquestionid);
            } catch (\Exception $e) {
                throw new \moodle_exception('error');
            }

            $DB->insert_record('community_sharequestion_shr', [
                    'type' => 'copy_to_quiz',
                    'qid' => $qid,
                    'useridfrom' => $USER->id,
                    'timecreated' => time(),
            ]);
        }

        $eventdata = array(
                'userid' => $USER->id,
                'sourcequestionids' => implode(',', $questions),
                'cmid' => $params['cmid'],
        );

        \community_sharequestion\event\question_to_quiz_copy::create_event($eventdata)->trigger();

        return json_encode(['result' => true]);
    }
}
