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
 * @package    community_sharesequence
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/course/lib.php');

class community_sharesequence_external extends external_api {

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

                            $relevantsections[] = $tmpsection;
                        }
                    }
                }

                if (empty($relevantsections)) {
                    continue;
                }

                if (course_get_format($course->id)->get_format() != 'flexsections') {
                    continue;
                }

                $tmpcorses = [];
                $tmpcorses['course_id'] = $course->id;
                $tmpcorses['course_name'] = $course->fullname;
                $tmpcorses['competencies'] = [];

                $tmpcorses['sections'] = $relevantsections;

                // TODO Competency disabled.
                //                $coursecompetencies = core_competency\api::list_course_competencies($course->id);
                //                foreach ($coursecompetencies as $key => $comp) {
                //                    $cca = [
                //                        'competency_id' => $comp['competency']->get('id'),
                //                        'competency_name' => $comp['competency']->get('shortname')
                //                    ];
                //
                //                    $tmpcorses['competencies'][] = $cca;
                //                }

                // TODO Need to remove.
                //                $tmpcorses['competencies'] = [
                //                    ['competency_id' => 1, 'competency_name' => 'comp1'], ['competency_id' => 2, 'competency_name' => 'comp2',],
                //                    ['competency_id' => 3, 'competency_name' => 'comp3'], ['competency_id' => 4, 'competency_name' => 'comp4',],
                //                    ['competency_id' => 5, 'competency_name' => 'comp5'], ['competency_id' => 6, 'competency_name' => 'comp6',],
                //                    ['competency_id' => 7, 'competency_name' => 'comp7'], ['competency_id' => 8, 'competency_name' => 'comp8',],
                //                    ['competency_id' => 9, 'competency_name' => 'comp9'], ['competency_id' => 10, 'competency_name' => 'comp10',],
                //                ];

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
    public static function submit_sequence_page_1_parameters() {

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
    public static function submit_sequence_page_1_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'upload result'),
                        'validation' => new external_value(PARAM_BOOL, 'upload result'),
                        'errors' => new external_value(PARAM_RAW, 'upload result'),
                )
        );
    }

    /**
     * Submit sequence page 1
     *
     * @param string $data
     * @return array
     */
    public static function submit_sequence_page_1($data) {
        global $USER, $DB, $CFG;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::submit_sequence_page_1_parameters(),
                array(
                        'data' => $data,
                )
        );

        require_once($CFG->dirroot . '/local/community/plugins/sharesequence/classes/sharesequence.php');

        $data = (array) json_decode($params['data']);
        $sharesequence = new sharesequence();
        $sharesequence->prepare_active_fields();

        // Prepare data.
        foreach ($sharesequence->get_active_fields() as $field) {
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
        foreach ($sharesequence->get_active_fields() as $field) {
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
                if (isset($data[$field->shortname]) && isset($data[$field->shortname . '_checkbox'])) {
                    if (empty($data[$field->shortname]) && $data[$field->shortname . '_checkbox'] == 'true') {
                        $errors[] = $field->shortname;
                    }
                }
            }

            // If select section empty.
            if ($field->datatype == 'selectsections') {
                if (empty($data['selected_sections'])) {
                    $errors[] = 'selected_sections';
                }

                // TODO Competency disabled.
                // If select competency empty.
                //                if(!empty($data['selected_sections'])){
                //                    foreach($data['selected_sections'] as $item){
                //                        $coursecompetencies = core_competency\api::list_course_competencies($item->course_id);
                //                        if(!empty($coursecompetencies)){
                //                            $flag = true;
                //                            foreach($data['selected_competencies'] as $comp){
                //                                if($comp->section_id == $item->section_id){
                //                                    $flag = false;
                //                                }
                //                            }
                //
                //                            if($flag) $errors[] = 'selected_competencies';
                //                        }
                //                    }
                //                }
            }
        }

        $result = true;

        $errors = array_filter($errors);
        $errors = array_values($errors);

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
    public static function get_data_for_section_parameters() {
        return new external_function_parameters(
                array(
                        'sectionid' => new external_value(PARAM_INT, 'sectionid'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function get_data_for_section_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'data of section'),
                )
        );
    }

    /**
     * Obtain data for teacher colleagues
     *
     * @param int $sectionid
     * @return array
     */
    public static function get_data_for_section($sectionid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(
                self::get_data_for_section_parameters(),
                array(
                        'sectionid' => $sectionid,
                )
        );

        list($activities, $sections) = \community_sharesequence\funcs::get_structure_section($params['sectionid']);

        $result = [
                'activities' => $activities,
                'sections' => $sections,
                'empty_section' => count($activities) == 0 && count($sections) == 0 ? true : false,
                'uniqueid' => time(),
        ];

        $content = array(
                'data' => json_encode($result)
        );

        return $content;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_sequence_page_2_parameters() {

        return new external_function_parameters(
                array(
                        'activities' => new external_value(PARAM_RAW, 'activities data'),
                        'data' => new external_value(PARAM_RAW, 'upload data'),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function submit_sequence_page_2_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'upload result'),
                )
        );
    }

    /**
     * Submit sequence page 2
     *
     * @param string $data
     * @return array
     */
    public static function submit_sequence_page_2($activities, $data) {
        global $USER, $DB, $CFG;

        require_once($CFG->dirroot . '/local/community/plugins/sharesequence/classes/sharesequence.php');

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::submit_sequence_page_2_parameters(),
                array(
                        'activities' => $activities,
                        'data' => $data,
                )
        );

        $result = false;
        $activities = json_decode($params['activities']);
        $data = json_decode(json_decode($params['data']), true);

        if (!empty($activities) && !empty($data)) {
            $result = true;

            foreach ($data['selected_sections'] as $place) {
                $newsection = \community_sharesequence\funcs::create_sub_section($place['section_id'], $data['sequencename']);

                // Save metadata.
                $sharesequence = new sharesequence();
                $sharesequence->prepare_active_fields();

                foreach ($sharesequence->get_active_fields() as $field) {
                    switch ($field->datatype) {
                        case 'originality':
                            if (isset($data[$field->shortname . '_checkbox']) && isset($data[$field->shortname])) {
                                if ($data[$field->shortname . '_checkbox'] == 'false' || empty(trim($data[$field->shortname]))) {
                                    $data[$field->shortname] = '';
                                }
                            }
                            break;
                        case 'textarea':
                            foreach ($data as $key => $item) {
                                if (strpos($key, $field->shortname) !== false && strpos($key, '[text]') !== false) {
                                    $data[$field->shortname] = (!empty(trim(strip_tags($item)))) ? trim($item) : '';
                                }
                            }
                            break;
                        case 'fileupload':

                            $fs = get_file_storage();
                            $context = \context_system::instance();

                            foreach ($data as $key => $item) {
                                if ($key == $field->shortname) {
                                    if (isset($data[$field->shortname]) && !empty($data[$field->shortname])) {
                                        $itemid = trim($data[$field->shortname]);

                                        $sql = "
                                            SELECT *
                                            FROM {files}
                                            WHERE filename != '.' AND component = 'user' AND filearea = 'draft' AND itemid = ?            
                                        ";

                                        $draft = $DB->get_record_sql($sql, array($itemid));
                                        if (!empty($draft)) {

                                            // Get previus file and delete.
                                            $sql = "
                                                SELECT *
                                                FROM {files}
                                                WHERE filename != '.' AND component = 'local_metadata' AND filearea = 'image' AND itemid = ?
                                            ";
                                            $prevfiles = $DB->get_records_sql($sql, array($itemid));
                                            foreach ($prevfiles as $file) {
                                                $fs->delete_area_files($file->contextid, $file->component, $file->filearea,
                                                        $file->itemid);
                                            }

                                            // Create file.
                                            $draft->contextid = $context->id;
                                            $draft->component = 'local_metadata';
                                            $draft->filearea = 'image';

                                            $fs->create_file_from_storedfile($draft, $draft->id);
                                        }
                                    }
                                }
                            }
                            break;
                        case 'multimenu':
                            foreach ($data as $key => $item) {
                                if ($key == $field->shortname) {
                                    $arr = explode(',', $data[$field->shortname]);
                                    $data[$field->shortname] = json_encode($arr, JSON_UNESCAPED_UNICODE);
                                }
                            }
                            break;

                        case 'multiselect':
                            if ($field->multiselecttype == 'multi') {
                                foreach ($data as $key => $item) {
                                    if ($key == $field->shortname) {
                                        $arr = explode(',', $data[$field->shortname]);
                                        $data[$field->shortname] = json_encode($arr, JSON_UNESCAPED_UNICODE);
                                    }
                                }
                            }
                            break;
                    }
                }

                foreach ($sharesequence->get_active_fields() as $field) {

                    if (isset($data[$field->shortname])) {
                        $dataformat = $field->datatype == 'textarea' ? 1 : 0;
                        \local_metadata\mcontext::section()->save(
                                $newsection->id,
                                $field->shortname,
                                $data[$field->shortname],
                                $dataformat);
                    }
                }

                // Add userid.
                \local_metadata\mcontext::section()->save($newsection->id, 'suserid', $USER->id);

                // Add created_at.
                \local_metadata\mcontext::section()->save($newsection->id, 'screated_at', time());

                // Add ID.
                \local_metadata\mcontext::section()->save($newsection->id, 'sID', $newsection->id);

                // Add task.
                \community_sharesequence\funcs::add_task('createsequence', $USER->id, $newsection->id, $activities);
            }
        }

        return ['result' => $result];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function check_availability_parameters() {

        return new external_function_parameters(
                array(
                        'activities' => new external_value(PARAM_RAW, 'activities data')
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function check_availability_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_RAW, 'result'),
                )
        );
    }

    /**
     * Submit sequence page 2
     *
     * @param string $data
     * @return array
     */
    public static function check_availability($activities) {
        global $USER, $DB, $CFG;

        require_once($CFG->dirroot . '/local/community/plugins/sharesequence/classes/sharesequence.php');

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $params = self::validate_parameters(self::check_availability_parameters(),
                array(
                        'activities' => $activities,
                )
        );

        // Availability.
        $arrcmids = $datacmids = [];
        $data = json_decode($params['activities']);
        foreach ($data as $obj) {
            $datacmids[] = $obj->cmid;
            $row = $DB->get_record('course_modules', array('id' => $obj->cmid));

            if (!empty($row->availability)) {
                \community_sharesequence\funcs::get_notpresent_availability(json_decode($row->availability), $arrcmids);
            }
        }

        // Check if present current activities in $arrcmids.
        foreach ($arrcmids as $key => $cmid) {
            if (in_array($cmid, $datacmids)) {
                unset($arrcmids[$key]);
            }
        }

        if ($arrcmids) {
            $activities = [];
            foreach ($arrcmids as $cmid) {
                list($course, $cm) = get_course_and_cm_from_cmid($cmid);
                $activities[] = $cm->name;
            }

            $result = [
                    'status' => true,
                    'activities' => $activities,
            ];
        } else {
            $result = [
                    'status' => false,
                    'activities' => [],
            ];
        }

        return ['result' => json_encode($result)];
    }
}
