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
 * Event observers supported by this module
 *
 * @package    local_metadata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata;
use \stdClass;

class mcontext {

    private static function init() {
        $contextplugins = \core_component::get_plugin_list('metadatacontext');
        foreach($contextplugins as $plugin){
            require_once($plugin.'/classes/context_handler.php');
        }
    }

    public static function module() {
        return new maction(CONTEXT_MODULE);
    }

    public static function course() {
        return new maction(CONTEXT_COURSE);
    }

    public static function category() {
        return new maction(CONTEXT_COURSECAT);
    }

    public static function user() {
        return new maction(CONTEXT_USER);
    }

    public static function cohort() {
        self::init();
        return new maction(CONTEXT_COHORT);
    }

    public static function group() {
        self::init();
        return new maction(CONTEXT_GROUP);
    }

    public static function question() {
        self::init();
        return new maction(CONTEXT_QUESTION);
    }

    public static function section() {
        self::init();
        return new maction(CONTEXT_SECTION);
    }
}

class maction {
    private $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function get_contextid() {
        return $this->context;
    }

    public function get($instanceid, $fieldname = null) {
        global $DB;

        if($fieldname != null) {
            $field = $DB->get_record('local_metadata_field', array('shortname' => $fieldname, 'contextlevel' => $this->context));
            if (!empty($field)) {
                $row = $DB->get_record('local_metadata', array('instanceid' => $instanceid, 'fieldid' => $field->id));
                return !empty($row) ? $row->data : '';
            }
        }else{
            $sql = "
                SELECT lm.id, lmf.shortname, lm.data
                FROM {local_metadata} AS lm
                LEFT JOIN {local_metadata_field} AS lmf ON(lm.fieldid = lmf.id)
                WHERE lm.instanceid = ? AND lmf.contextlevel = ?            
            ";

            $data = $DB->get_records_sql($sql, [$instanceid, $this->context]);

            $result = new \StdClass();
            foreach($data as $item){
                $shortname = $item->shortname;
                $data = $item->data;

                $result->$shortname = $data;
            }

            return $result;
        }

        return false;
    }

    public function save($instanceid, $fieldname, $value, $dataformat = 0) {
        global $DB;

        if($value == null) return false;

        $field = $DB->get_record('local_metadata_field', array('shortname' => $fieldname, 'contextlevel' => $this->context));
        if(!empty($field)){
            $row = $DB->get_record('local_metadata', array('instanceid' => $instanceid, 'fieldid' => $field->id));
            if(!empty($row)){
                $row->data = $value;
                $row->dataformat = $dataformat;
                $DB->update_record('local_metadata', $row);
            }else {
                $DB->insert_record('local_metadata', array(
                    'instanceid' => $instanceid,
                    'fieldid' => $field->id,
                    'data' => $value,
                    'dataformat' => $dataformat,
                ));
            }
        }else{
            return false;
        }

        return true;
    }

    public function saveEmpty($instanceid, $fieldname) {
        global $DB;

        $field = $DB->get_record('local_metadata_field', array('shortname' => $fieldname, 'contextlevel' => $this->context));
        if(!empty($field)){
            $DB->delete_records('local_metadata', array('instanceid' => $instanceid, 'fieldid' => $field->id));
        }else{
            return false;
        }

        return true;
    }

    public function add_field() {
        return new add_field($this->context);
    }

    public function copy_all_metadata($sourseid, $targetid, $exclude = []) {
        global $DB, $USER;

        $ids = [];
        $fields = $DB->get_records('local_metadata_field', array('contextlevel' => $this->context));
        foreach($fields as $field){
            if(!in_array($field->shortname, $exclude)) {
                $ids[] = $field->id;
            }
        }

        if(!empty($ids)) {
            $query = "
                SELECT lm.*, lmf.shortname, lmf.datatype
                FROM {local_metadata} AS lm
                LEFT JOIN {local_metadata_field} AS lmf ON (lm.fieldid = lmf.id)
                WHERE instanceid = ? AND fieldid IN(".implode(',', $ids).")        
            ";

            $metadata = $DB->get_records_sql($query, [$sourseid]);

            foreach($metadata as $item){
                if($item->datatype == 'fileupload'){

                    // Update metadata.
                    $this->save($targetid, $item->shortname, $item->data, $item->dataformat);

                    $sql = "
                        SELECT *
                        FROM {files}
                        WHERE component = 'local_metadata' AND 'filename' != '.' AND itemid = ?
                    ";
                    $row = $DB->get_record_sql($sql, [$item->data]);

                    switch ($this->context) {
                        case CONTEXT_MODULE:
                            $context = \context_module::instance($targetid);
                            break;
                        case CONTEXT_COURSE:
                            $context = \context_course::instance($targetid);
                            break;
                        case CONTEXT_COURSECAT:
                            $context = \context_coursecat::instance($targetid);
                            break;
                        case CONTEXT_USER:
                            $context = \context_user::instance($targetid);
                            break;
                        default:
                            $context = null;
                    }

                    if(!empty($row) && $context != null) {
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($row->contextid, $row->component, $row->filearea, $row->itemid);

                        foreach ($files as $file) {
                            if ($file->is_valid_image()) {
                                $draftitemid = file_get_unused_draft_itemid();
                                $filerecord = array(
                                        'contextid' => $context->id,
                                        'component' => 'local_metadata',
                                        'filearea' => 'image',
                                        'itemid' => $draftitemid,
                                        'filepath' => $file->get_filepath(),
                                        'filename' => $file->get_filename(),
                                );

                                $content = $file->get_content();
                                $fs->create_file_from_string($filerecord, $content);

                                $this->save($targetid, $item->shortname, $draftitemid, $item->dataformat);
                            }
                        }
                    }
                }else {
                    $this->save($targetid, $item->shortname, $item->data, $item->dataformat);
                }
            }
        }
    }

    public function getFields() {
        global $DB;

        $fields = $DB->get_records('local_metadata_field', array('contextlevel' => $this->context));

        return $fields;
    }

    public function getField($shortname) {
        global $DB;

        $field = $DB->get_record('local_metadata_field', array(
            'contextlevel' => $this->context,
            'shortname' => $shortname,
        ));

        return $field;
    }
}

class add_field {
    private $context;
    private $metadata_category;
    private $sortorder;

    public function __construct($context) {
        global $DB;

        $this->context = $context;

        if ($lmc = $DB->get_record('local_metadata_category', array('contextlevel' => $this->context))) {
            $this->metadata_category = $lmc->id;
        }else{
            $this->metadata_category = $DB->insert_record('local_metadata_category', array(
                    'contextlevel' => $this->context,
                    'name' => 'שדות אחרים',
                    'sortorder' => 1,
            ));
        }

        $query = "
            SELECT MAX(sortorder) AS maxsort
            FROM {local_metadata_field}
            WHERE categoryid= ?
            ";
        $obj = $DB->get_record_sql($query, [$this->metadata_category]);

        $this->sortorder = 0;
        if(isset($obj->maxsort) && !empty($obj->maxsort)){
            $this->sortorder = $obj->maxsort;
        }
    }

    public function checkbox($shortname, $label, $params = []) {
        global $DB;

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'checkbox',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : 0,
                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function text($shortname, $label, $params = []) {
        global $DB;

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'text',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,
                    'param1' => 30,
                    'param2' => 2048,
                    'param3' => 0,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function menu($shortname, $label, $params = []) {
        global $DB;

        $param1 = '';
        if(isset($params['param1'])){
            if(is_array($params['param1'])){
                $param1 = implode("\n", $params['param1']);
            }else{
                $param1 = $params['param1'];
            }
        }

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'menu',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,

                    'param1' => $param1,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function multimenu($shortname, $label, $params = []) {
        global $DB;

        $param1 = '';
        if(isset($params['param1'])){
            if(is_array($params['param1'])){
                $param1 = implode("\n", $params['param1']);
            }else{
                $param1 = $params['param1'];
            }
        }

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'multimenu',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,

                    'param1' => $param1,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function fileupload($shortname, $label, $params = []) {
        global $DB;

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'fileupload',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function textarea($shortname, $label, $params = []) {
        global $DB;

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'textarea',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

    public function datetime($shortname, $label, $params = []) {
        global $DB;

        $lmf = $DB->get_record('local_metadata_field', array('shortname' => $shortname, 'categoryid' => $this->metadata_category));
        if (empty($lmf)) {
            $DB->insert_record('local_metadata_field', array(
                    'contextlevel' => $this->context,
                    'shortname' => $shortname,
                    'name' => $label,
                    'datatype' => 'datetime',
                    'categoryid' => $this->metadata_category,
                    'sortorder' => $this->sortorder + 1,

                    'defaultdata' => isset($params['defaultdata']) ? $params['defaultdata'] : '',
                    'description' => isset($params['description']) ? $params['description'] : '',
                    'descriptionformat' => 1,

                    'required' => isset($params['required']) ? $params['required'] : 0,
                    'locked' => isset($params['locked']) ? $params['locked'] : 0,
                    'visible' => isset($params['visible']) ? $params['visible'] : 0,
                    'signup' => isset($params['signup']) ? $params['signup'] : 0,
            ));
        }
    }

}