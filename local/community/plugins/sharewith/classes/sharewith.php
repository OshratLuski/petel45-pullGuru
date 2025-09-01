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
 * The community_sharewith Exceptions.
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use mod_quiz\quiz_settings;

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/classFunctionHelp.php');

class sharewith {
    private $modulemetadata;
    private $metadatafields;
    private $activityid;
    private $courseid;

    public function __construct() {
        global $DB;

        $this->modulemetadata = \local_metadata\mcontext::module()->get_contextid();

        $query = "SELECT shortname, name, datatype, name as title, description, required, defaultdata, param1 as data, param2
            FROM {local_metadata_field}
            WHERE contextlevel = ? AND signup != 0
            ORDER BY sortorder ASC";

        $result = $DB->get_records_sql($query, [$this->modulemetadata]);

        $this->metadatafields = $result;
    }

    public function prepare_active_fields() {

        $result = $this->metadatafields;

        foreach ($result as $key => $item) {

            // Change originality type.
            if ($item->shortname == 'originality') {
                $result[$key]->datatype = 'originality';
            }

            // Change durationactivity type.
            if ($item->shortname == 'durationactivity') {
                $result[$key]->datatype = 'durationactivity';
            }

            // Change levelactivity type.
            if ($item->shortname == 'levelactivity') {
                $result[$key]->datatype = 'levelactivity';
            }

            // Add unique.
            $result[$key]->unique = time();

            // Add description direction.
            $result[$key]->description_dir = right_to_left() ? 'left' : 'right';

            // Multiselect.
            if ($item->datatype == 'multiselect') {
                if ($item->param2 == 1) {
                    $item->multiselecttype = 'multi';
                } else {
                    $item->multiselecttype = 'single';
                }
            }
        }

        // Remove sourceurl.
        foreach ($result as $key => $item) {
            if ($item->shortname == 'sourceurl') {
                unset($result[$key]);
            }
        }

        // Remove teacherremarks.
        $teacherremarks = null;
        foreach ($result as $key => $item) {
            if ($item->shortname == 'teacherremarks') {
                $teacherremarks = $item;
                unset($result[$key]);
            }
        }

        if ($teacherremarks != null) {
            //$id = $this->get_saved_metadata_activityid('ID');
            //if (!empty($id) && is_number($id)) {
            //    $default = $this->get_saved_metadata_activityid('teacherremarks', $id);
            //} else {
            //    $default = $this->get_saved_metadata_activityid('teacherremarks');
            //}

            $default = $this->get_saved_metadata_activityid('teacherremarks');

            $teacherremarks->required = 1;
            $teacherremarks->defaultdata = $default;
        }

        // Activity title.
        $activityinfo = $this->get_activity_info();

        $activitytitle = new \StdClass();
        $activitytitle->shortname = 'activitytitle';
        $activitytitle->name = 'Name of activity';
        $activitytitle->datatype = 'text';
        $activitytitle->title = get_string('item_name', 'community_sharewith');
        $activitytitle->required = 1;
        $activitytitle->defaultdata = $activityinfo->name;
        $activitytitle->data = '';

        // Select sections.
        $selectsections = new \StdClass();
        $selectsections->shortname = 'selectsections';
        $selectsections->name = 'Select sections';
        $selectsections->datatype = 'selectsections';
        $selectsections->title = get_string('theme_of_the_activity', 'community_sharewith');
        $selectsections->required = 1;
        $selectsections->defaultdata = '';
        $selectsections->data = '';

        $firstelements = [];
        $firstelements[] = $activitytitle;
        if ($teacherremarks != null) {
            $firstelements[] = $teacherremarks;
        }
        $firstelements[] = $selectsections;

        $result = array_values(array_merge($firstelements, $result));

        // Tags.
        //$tags = new \StdClass();
        //$tags->shortname = 'tags';
        //$tags->name = 'Tags';
        //$tags->datatype = 'tags';
        //$tags->title = get_string('tag_item', 'community_sharewith');
        //$tags->required = false;
        //$tags->defaultdata = '';
        //$tags->data = '';
        //
        //$result = array_values(array_merge($result, [$tags]));

        foreach ($result as $key => $item) {
            $result[$key]->data_formated = $this->prepare_content_metadata($item);
        }

        // Generate title.
        foreach ($result as $key => $item) {
            switch ($item->shortname) {

                case 'responsibility':
                    $result[$key]->title = false;
                    $result[$key]->format_checkbox = true;
                    $result[$key]->format_radio = false;
                    break;

                case 'durationactivity':
                    $result[$key]->defaultdata = 0;
                    $result[$key]->count = count($item->data_formated) - 1;
                    break;

                case 'levelactivity':
                    $result[$key]->element_0 = isset($item->data_formated[0]) ? $item->data_formated[0] : false;
                    $result[$key]->element_1 = isset($item->data_formated[1]) ? $item->data_formated[1] : false;
                    $result[$key]->element_2 = isset($item->data_formated[2]) ? $item->data_formated[2] : false;
                    break;
            }

            $result[$key]->data_formated = $this->prepare_content_metadata($item);
        }

        $this->metadatafields = $result;
    }

    public function get_required_fields() {
        $res = [];

        foreach ($this->metadatafields as $item) {
            if ($item->required == 1) {
                $res[] = $item;
            }
        }

        return $res;
    }

    public function get_not_required_fields() {
        $res = [];

        foreach ($this->metadatafields as $item) {
            if ($item->required == 0) {
                $res[] = $item;
            }
        }

        return $res;
    }

    public function get_active_fields() {
        return $this->metadatafields;
    }

    public function setactivityid($activityid, $courseid = 0) {
        $this->activityid = $activityid;
        $this->courseid = $courseid;
    }

    public function check_sharewith_error() {

        if (empty($this->metadatafields)) {
            return get_string('error');
        }

        if ($this->courseid != 0) {
            if (!$this->check_quiz_category()) {
                return get_string('error_quiz_category', 'community_sharewith');
            }

        }

        if ($this->courseid == 0) {
            return get_string('error_courseid', 'community_sharewith');
        }

        return '';
    }

    public function get_activity_info() {
        global $DB;

        // Get info activity and mod.
        $query = '
            SELECT cm.course, cm.module, cm.instance, m.name
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON (cm.module=m.id)
            WHERE cm.id=?';

        $activity = $DB->get_record_sql($query, [$this->activityid]);

        $modinfo = $DB->get_record_sql('SELECT * FROM {' . $activity->name . '} WHERE id=?', [$activity->instance]);

        return $modinfo;
    }

    public function prepare_content_metadata($obj) {

        $result = [];
        if(in_array($obj->datatype, ['multiselect'])) {
            $data = array_filter(explode("\n", $obj->data));
            foreach ($data as $str) {
                // ID value for separator.
                $idvalue = explode(':', $str);

                // Lang values separator.
                preg_match_all("/([^|=]+)=([^|=]+)/", end($idvalue), $r);
                $arrlang = array_combine($r[1], $r[2]);

                if (empty($obj->defaultdata)) {
                    $checked = false;
                } else {
                    $checked = $obj->defaultdata == $idvalue[0] ? true : false;
                }

                // Check current language on system for display.
                if(!$lang = get_parent_language()){
                    $lang = current_language();
                }

                $result[] = array(
                        'metadata_name' => isset($arrlang[$lang]) ? $arrlang[$lang] : $arrlang['en'],
                        'metadata_icon' => '',
                        'metadata_value' => $idvalue[0],
                        'metadata_checked' => $checked,
                        'metadata_id' => $obj->shortname . $idvalue[0] . time(),
                );
            }
        }

        if (in_array($obj->datatype, ['menu', 'multimenu', 'durationactivity', 'levelactivity'])) {
            $res = preg_split('/\R/', $obj->data);
            $res = array_unique($res);

            if (!empty($res)) {
                $countchecked = 0;
                foreach ($res as $str) {
                    $arrstr = explode('|', $str);

                    if (isset($arrstr[1]) && !empty($arrstr[1])) {
                        $icon = 'involve__button-image--' . $arrstr[1];
                    } else {
                        $icon = '';
                    }

                    if (empty($obj->defaultdata)) {
                        $checked = false;
                    } else {
                        $checked = $obj->defaultdata == $str ? true : false;
                    }

                    $result[] = array(
                            'metadata_name' => $arrstr[0],
                            'metadata_icon' => $icon,
                            'metadata_value' => $str,
                            'metadata_checked' => $checked,
                            'metadata_id' => $obj->shortname . $countchecked . time(),
                    );

                    $countchecked++;
                }
            }
        }

        if (in_array($obj->datatype, ['checkbox', 'text', 'textarea', 'fileupload', 'selectsections', 'originality', 'tags'])) {
            $result = array(
                    'metadata_name' => $obj->shortname,
                    'metadata_icon' => '',
                    'metadata_value' => '',
                    'metadata_checked' => false,
                    'metadata_id' => $obj->shortname . time(),
            );
        }

        return $result;
    }

    public function get_sections_by_course($courseid) {
        global $DB;

        $result = array();
        $counter = 1;

        $obj = $DB->get_records_sql("SELECT * FROM {course_sections} WHERE course=? AND section!=0", [$courseid]);
        $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id=?", [$courseid]);

        if (!empty($obj)) {
            foreach ($obj as $item) {
                $arr = array();
                $arr['section_id'] = $item->section;
                $arr['section_name'] =
                        (empty($item->name)) ? get_string('sectionname', 'format_' . $course->format) . ' ' . $counter :
                                $item->name;

                $result[] = $arr;
                $counter++;
            }
        }

        return $result;
    }

    public function get_saved_metadata_activityid($shortname, $activityid = null) {

        if ($activityid == null) {
            $activityid = $this->activityid;
        }

        $result = '';
        if (!empty($activityid) && !empty($shortname)) {
            $result = \local_metadata\mcontext::module()->get($activityid, $shortname);
        }

        return $result;
    }

    public function add_specific_data() {
        global $DB;

        $data = array();

        // Get mod activity.
        $query = '
        SELECT m.name AS name
        FROM {modules} AS m
        LEFT JOIN {course_modules} AS cm ON (m.id=cm.module)
        WHERE cm.id=?';
        $mod = $DB->get_record_sql($query, [$this->activityid]);

        // Check availability.
        $flagifavailability = false;
        $row = $DB->get_record('course_modules', array('id' => $this->activityid, 'deletioninprogress' => 0));
        if (!empty($row) && !empty($row->availability)) {

            $obj = json_decode($row->availability);
            if (isset($obj->c) && !empty($obj->c)) {
                foreach ($obj->c as $key => $item) {
                    if ($item->type == 'completion') {
                        $flagifavailability = true;
                    }
                }
            }
        }

        $data['ifavailability'] = $flagifavailability;

        // Check glossary.
        $flagifglossary = false;
        if ($mod->name == 'glossary') {
            $flagifglossary = true;
        }
        $data['ifglossary'] = $flagifglossary;

        // Check database.
        $flagifdatabase = false;
        if ($mod->name == 'data') {
            $flagifdatabase = true;
        }
        $data['ifdatabase'] = $flagifdatabase;

        return $data;
    }

    public function check_quiz_category($cmid = null) {
        global $DB;

        // EC-219 Removed check after adding "fix question flow".
        return true;
        /////////////////////////////////////////

        $activityid = $cmid ? $cmid : $this->activityid;

        // Get mod activity.
        $query = '
            SELECT m.name AS module_name, cm.* 
            FROM {modules} m
            LEFT JOIN {course_modules} cm ON (m.id=cm.module)
            WHERE cm.id=?';
        $obj = $DB->get_record_sql($query, [$activityid]);

        // If mod quiz.
        if ($obj->module_name == 'quiz') {

            // Get questions by activity.
            $quiz = quiz_settings::create($obj->instance);
            $quiz->preload_questions();
            $quiz->load_questions();

            $contextraw = $DB->get_record('context', array('contextlevel' => $this->modulemetadata, 'instanceid' => $activityid));
            if (!empty($contextraw)) {
                $pathes = explode("/", $contextraw->path);
                $path = end($pathes);
                $categorydefault = $DB->get_records('question_categories', array('contextid' => $path), 'sortorder DESC');
                if (!empty($categorydefault)) {
                    $result = true;
                    foreach ($quiz->get_questions() as $question) {
                        if (!key_exists($question->category, $categorydefault)) {
                            $result = false;
                        }
                    }
                    return $result;
                }
            }
        }

        return true;
    }

    public function savemanyactivitytomaagar($post) {

        if (empty($post['selected_sections'])) {
            return false;
        }

        // Prepare data.
        foreach ($post['selected_sections'] as $key => $item) {
            $competencies = [];
            foreach ($post['selected_competencies'] as $comp) {
                if ($item->section_id == $comp->section_id) {
                    $competencies[] = $comp->competency_id;
                }
            }

            $post['selected_sections'][$key]->competencies = $competencies;
        }

        $basesection = null;
        $linksectionids = [];

        foreach ($post['selected_sections'] as $key => $item) {

            if ($key == 0 || !empty($item->competencies)) {
                $basesection = $item;
            }

            $linksectionids[] = $item->section_id;
        }

        if ($basesection != null) {
            $post['courseid'] = $basesection->course_id;
            $post['sectionid'] = $basesection->section_id;
            $post['competencies'] = implode(',', $basesection->competencies);
            $post['linksectionids'] = implode(',', $linksectionids);
        } else {
            return false;
        }

        if (!$this->saveactivitytomaagar($post)) {
            return false;
        };

        return true;
    }

    public function saveactivitytomaagar($post) {
        global $DB, $USER;

        $fromactivityid = $post['activityid'];
        $courseid = $post['courseid'];
        $sectionid = $post['sectionid'];

        $sourcecm = $DB->get_record('course_modules', array('id' => $fromactivityid));
        $sourcecourseid = $sourcecm->course;

        // Check ID value in metadata.
        $func = new functionHelp();
        $func->community_sharewith_check_metadata_id($fromactivityid);

        // Warning select.
        $warningselect = isset($post['warningselect']) ? $post['warningselect'] : 0;

        $metadata = [
                'notification' => 'banksharing',
                'ifavailability' => $post['ifavailability'],
                'ifglossary' => $post['ifglossary'],
                'ifdatabase' => $post['ifdatabase'],
                'newactivitycompetencies' => $post['competencies'],
                'linksectionids' => $post['linksectionids'],
                'metadata' => [
                        'activitytitle' => $post['activitytitle'],
                        'warningselect' => $warningselect,
                        'data' => json_encode($post),
                ],
        ];
        $metadata = json_encode($metadata);

        $result = \community_sharewith\funcs::add_task('activitycopy', $USER->id, $USER->id, $sourcecourseid, $courseid, null,
                $sectionid, null, $fromactivityid, $metadata, $post['chain']);

        return $result;
    }
}
