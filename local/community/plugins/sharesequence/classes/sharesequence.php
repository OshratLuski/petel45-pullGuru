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
 * The community_sharesequence Exceptions.
 *
 * @package    community_sharesequence
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');

class sharesequence {
    private $modulemetadata;
    private $metadatafields;

    public function __construct() {
        global $DB;

        $this->modulemetadata = \local_metadata\mcontext::section()->get_contextid();

        $query = "
            SELECT shortname, name, datatype, name as title, description, required, defaultdata, param1 as data, param2  
            FROM {local_metadata_field}
            WHERE contextlevel = ? AND signup != 0
            ORDER BY sortorder ASC
        ";

        $result = $DB->get_records_sql($query, [$this->modulemetadata]);

        $this->metadatafields = $result;
    }

    public function prepare_active_fields() {

        $result = $this->metadatafields;

        foreach ($result as $key => $item) {

            // Change originality type.
            if ($item->shortname == 'soriginality') {
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

            if ($item->shortname == 'sequencedescription') {
                unset($result[$key]);
            }
        }

        // Name of sequence.
        $sequencename = new \StdClass();
        $sequencename->shortname = 'sequencename';
        $sequencename->name = 'Sequence name';
        $sequencename->datatype = 'text';
        $sequencename->title = get_string('sequencenameinput', 'community_sharesequence');
        $sequencename->required = 1;
        $sequencename->defaultdata = '';
        $sequencename->data = 30;
        $sequencename->description = '';

        // Name of sequence.
        $sequencedescription = new \StdClass();
        $sequencedescription->shortname = 'sequencedescription';
        $sequencedescription->name = 'Sequence description';
        $sequencedescription->datatype = 'textarea';
        $sequencedescription->title = get_string('sequencedescriptioninput', 'community_sharesequence');
        $sequencedescription->required = 1;
        $sequencedescription->defaultdata = '';
        $sequencedescription->data = '';
        $sequencedescription->description = get_string('sequencedescriptionlabel', 'community_sharesequence');

        // Select sections.
        $selectsections = new \StdClass();
        $selectsections->shortname = 'selectsections';
        $selectsections->name = 'Select sections';
        $selectsections->datatype = 'selectsections';
        $selectsections->title = get_string('theme_of_the_question', 'community_sharesequence');
        $selectsections->required = 1;
        $selectsections->defaultdata = '';
        $selectsections->data = '';

        $firstelements = [];
        //$firstelements[] = $activitytitle;
        //if($teacherremarks != null) $firstelements[] = $teacherremarks;

        $firstelements[] = $sequencename;
        $firstelements[] = $sequencedescription;
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
                        //$checked = $countchecked == 0 ? true : false;
                        $checked = false;
                    } else {
                        $checked = $obj->defaultdata == $str ? true : false;
                    }

                    $tmp = array(
                            'metadata_name' => $arrstr[0],
                            'metadata_icon' => $icon,
                            'metadata_value' => $str,
                            'metadata_checked' => $checked,
                            'metadata_id' => $obj->shortname . $countchecked . time(),
                    );
                    $result[] = $tmp;

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

                $arrtmp = array();
                $arrtmp['section_id'] = $item->section;
                $arrtmp['section_name'] =
                        (empty($item->name)) ? get_string('sectionname', 'format_' . $course->format) . ' ' . $counter :
                                $item->name;

                $result[] = $arrtmp;
                $counter++;
            }
        }

        return $result;
    }

    public function save_many_questions_tocatalog($post) {

        if (empty($post['selected_sections'])) {
            return false;
        }

        foreach ($post['selected_sections'] as $key => $item) {
            if (!is_numeric($item->course_id) || !is_numeric($item->section_id)) {
                return false;
            }
            $post['courseid'] = $item->course_id;
            $post['sectionid'] = $item->section_id;

            $competencies = [];
            foreach ($post['selected_competencies'] as $comp) {
                if ($item->section_id == $comp->section_id) {
                    $competencies[] = $comp->competency_id;
                }
            }

            $post['competencies'] = implode(',', $competencies);

            if (!$this->save_questions_tocatalog($post)) {
                return false;
            }
        }
        return true;
    }

    public function save_questions_tocatalog($post) {
        global $DB, $USER;

        $questions = json_decode($post['selected_questions']);
        $sectionid = $post['sectionid'];
        $competencies = $post['competencies'];

        // Prepare metadata.
        $metadata = ['competencies' => $competencies];
        foreach ($this->get_active_fields() as $item) {
            if (isset($post[$item->shortname])) {
                $metadata[$item->shortname] = $post[$item->shortname];
            }
        }

        // Set quserid and qhidden in metadata.
        $metadata['quserid'] = $USER->id;
        $metadata['qhidden'] = 1;

        foreach ($questions as $qid) {
            $DB->insert_record('community_sharesequence_task', [
                    'type' => 'upload_to_catalog',
                    'sourcequestionid' => $qid,
                    'targetsectionid' => $sectionid,
                    'targetuserid' => $USER->id,
                    'metadata' => json_encode($metadata),
                    'status' => 0,
                    'error' => '',
                    'timemodified' => time(),
            ]);

            $DB->insert_record('community_sharesequence_shr', [
                    'type' => 'upload_to_catalog',
                    'qid' => $qid,
                    'useridfrom' => $USER->id,
                    'timecreated' => time(),
            ]);
        }

        return true;
    }
}
