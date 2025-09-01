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
 * The community_sharecourse Exceptions.
 *
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharecourse;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/datalib.php');

class sharecourse {
    private $modulemetadata;
    private $metadatafields;

    public function __construct() {
        global $DB;

        $this->modulemetadata = \local_metadata\mcontext::course()->get_contextid();

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
            if ($item->shortname == 'cfullname') {
                unset($result[$key]);
            }

            if ($item->shortname == 'cdescription') {
                unset($result[$key]);
            }
        }

        // Name of course.
        $coursename = new \StdClass();
        $coursename->shortname = 'cfullname';
        $coursename->name = 'Course name';
        $coursename->datatype = 'text';
        $coursename->title = get_string('coursenameinput', 'community_sharecourse');
        $coursename->required = 1;
        $coursename->defaultdata = '';
        $coursename->data = 30;
        $coursename->description = '';

        // Description of course.
        $coursedescription = new \StdClass();
        $coursedescription->shortname = 'cdescription';
        $coursedescription->name = 'Course description';
        $coursedescription->datatype = 'textarea';
        $coursedescription->title = get_string('coursedescriptioninput', 'community_sharecourse');
        $coursedescription->required = 1;
        $coursedescription->defaultdata = '';
        $coursedescription->data = '';
        $coursedescription->description = get_string('coursedescriptionlabel', 'community_sharecourse');

        // Select courses.
        $selectcourses = new \StdClass();
        $selectcourses->shortname = 'selectcourses';
        $selectcourses->name = 'Select courses';
        $selectcourses->datatype = 'selectcourses';
        $selectcourses->title = get_string('theme_of_the_course', 'community_sharecourse');
        $selectcourses->required = 1;
        $selectcourses->defaultdata = '';

        $hierarchy = [];

        $menu = \community_oer\main_oer::structure_main_catalog();
        foreach ($menu as $category) {
            $children = [];
            foreach ($category['courses'] as $course) {
                $children[] = [
                        'text' => str_replace('"', '\"', $course->fullname),
                        'checked' => false,
                        'courseid' => $course->id,
                ];
            }

            $catname = str_replace('"', '\"', $category['cat_name']);
            $hierarchy[] = [
                    'text' => $catname,
                    'checked' => false,
                    'catid' => $category['cat_id'],
                    'children' => $children
            ];
        }

        $selectcourses->hierarchy = $hierarchy;

        $firstelements = [];
        $firstelements[] = $coursename;
        $firstelements[] = $coursedescription;
        $firstelements[] = $selectcourses;
        $result = array_values(array_merge($firstelements, $result));

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

        if (in_array($obj->datatype, ['checkbox', 'text', 'textarea', 'fileupload', 'selectcourses', 'originality', 'tags'])) {
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

    private function save_course_metadata($currentcourseid, $subject, $data) {
        global $USER, $DB;

        if (!empty($data)) {

            // Save metadata.
            $this->prepare_active_fields();

            foreach ($this->get_active_fields() as $field) {
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
                        $context = \context_course::instance($currentcourseid);

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

            foreach ($this->get_active_fields() as $item) {
                if (isset($data[$item->shortname])) {
                    \local_metadata\mcontext::course()->save($currentcourseid, $item->shortname, $data[$item->shortname]);
                }
            }

            // Check course for duplicate in oercatalog and remove old course from oercatalog.
            $cid = \local_metadata\mcontext::course()->get($currentcourseid, 'cID');
            $cuserid = \local_metadata\mcontext::course()->get($currentcourseid, 'cuserid');
            $obj = \community_oer\course_oer::funcs()::get_course_shared($cid);

            if (!empty($obj) && $cuserid == $obj->userid && $cuserid == $USER->id) {
                \local_metadata\mcontext::course()->saveEmpty($cid, 'csubject');
            }

            // Add subject.
            \local_metadata\mcontext::course()->save($currentcourseid, 'csubject', $subject);

            // Add cuserid.
            \local_metadata\mcontext::course()->save($currentcourseid, 'cuserid', $USER->id);

            // Add cshared_at.
            \local_metadata\mcontext::course()->save($currentcourseid, 'cshared_at', time());

            // Add chidden.
            $visible = get_config('community_sharecourse', 'oercoursesharevisible');
            if ($visible == 1) {
                \local_metadata\mcontext::course()->save($currentcourseid, 'chidden', 0);
            } else {
                \local_metadata\mcontext::course()->save($currentcourseid, 'chidden', 1);
            }

            // Cache data module course.
            $course = new \community_oer\course_oer;
            $course->course_recalculate_in_db($currentcourseid);
            $course->recalculate_data_in_cache();
        }

        return true;
    }

    public function save_many_courses_tocatalog($post) {
        global $DB, $USER, $CFG;

        if (empty($post['selected_courses'])) {
            return false;
        }

        $currentcourseid = $post['courseid'];
        $typeshare = $post['typeshare'];

        // Create course.
        if ($typeshare == 1) {
            if (!empty($USER->idnumber)) {
                $cat = $DB->get_record('course_categories', ['idnumber' => $USER->idnumber]);
                if ($cat) {
                    $post['typeshare'] = 0;
                    \community_sharecourse\funcs::add_task('coursecopy_share', $USER->id, $currentcourseid, $cat->id, $post);
                }
            }
            return true;
        }

        $courses = [];
        foreach ($post['selected_courses'] as $item) {
            if (!is_numeric($item->course_id)) {
                continue;
            }
            $courses[] = trim($item->course_id);
        }

        // Get subject.
        $tmp = \local_metadata\mcontext::course()->get($currentcourseid, 'csubject');
        $arr = (empty(trim($tmp))) ? [] : explode(',', trim($tmp));

        $arr = array_merge($arr, $courses);
        $arr = array_unique($arr);

        $subject = implode(',', $arr);
        $this->save_course_metadata($currentcourseid, $subject, $post);

        // Enrol users.
        $this->enrol_course($currentcourseid);

        // Update metadata cID.
        \local_metadata\mcontext::course()->save($currentcourseid, 'cID', $currentcourseid);

        $DB->insert_record('community_sharecourse_shr', [
                'type' => 'upload_to_catalog',
                'courseid' => $currentcourseid,
                'subject' => $subject,
                'useridfrom' => $USER->id,
                'timecreated' => time(),
        ]);

        // Event.
        $eventdata = array(
                'userid' => $USER->id,
                'currentcourseid' => $currentcourseid,
                'subject' => $subject,
        );
        \community_sharecourse\event\course_share::create_event($currentcourseid, $eventdata)->trigger();

        // Send notifications and mails.
        $customdata = array();
        $customdata['custom'] = true;
        $customdata['custom_html_only'] = true;

        $userfrom = $USER;
        $currentcourse = get_course($currentcourseid);

        $a = new \stdClass();
        $a->user = $userfrom->firstname . ' ' . $userfrom->lastname;
        $a->coursename = $currentcourse->fullname;
        $a->url = $CFG->wwwroot . '/course/view.php?id=' . $currentcourseid;

        $htmlmessage = get_string('notificationmessage', 'community_sharecourse', $a);
        $smallmessage = $htmlmessage;

        $subjectmail = get_string('subjectmail', 'community_sharecourse', $a);

        $strmails = get_config('local_community', 'adminmails');
        foreach (explode(',', $strmails) as $email) {

            $userto = $DB->get_record('user', ['email' => trim($email)]);

            if (empty($userto)) {
                continue;
            }

            // Notification.
            $objinsert = new \stdClass();
            $objinsert->useridfrom = $userfrom->id;
            $objinsert->useridto = $userto->id;
            $objinsert->subject = $smallmessage;
            $objinsert->fullmessage = $smallmessage;
            $objinsert->fullmessageformat = 2;
            $objinsert->fullmessagehtml = $htmlmessage;
            $objinsert->smallmessage = $smallmessage;
            $objinsert->component = 'community_oer';
            $objinsert->eventtype = 'custom_html_only';
            $objinsert->timecreated = time();
            $objinsert->customdata = json_encode($customdata);

            $notificationid = $DB->insert_record('notifications', $objinsert);

            $objinsert = new \stdClass();
            $objinsert->notificationid = $notificationid;
            $DB->insert_record('message_petel_notifications', $objinsert);

            // Mail.
            email_to_user($userto, $userfrom, $subjectmail, $smallmessage, $htmlmessage);
        }

        return true;
    }

    public function enrol_course($courseid) {
        global $DB;

        $course = get_course($courseid);

        $roleid = get_config('community_sharecourse', 'oercoursecohortrole');
        if (is_int($roleid) && $roleid <= 0) {
            return false;
        }

        $cohortid = get_config('community_sharecourse', 'oercoursecohort');
        if (is_int($cohortid) && $cohortid <= 0) {
            return false;
        }

        if (!enrol_is_enabled('cohort')) {
            return false;
        }

        $row = $DB->get_record('enrol', [
                'enrol' => 'cohort',
                'courseid' => $course->id,
                'customint1' => $cohortid,
                'roleid' => $roleid,
        ]);

        if (!empty($row)) {
            return false;
        }

        $plugin = enrol_get_plugin('cohort');

        $instance = (object) $plugin->get_instance_defaults();
        $instance->id = null;
        $instance->courseid = $course->id;
        $instance->status = ENROL_INSTANCE_ENABLED;

        $fields = [];
        $fields['name'] = get_string('enrolname', 'community_sharecourse');
        $fields['customint1'] = $cohortid;
        $fields['roleid'] = $roleid;
        $fields['customint2'] = 0;
        $fields['type'] = 'cohort';

        $plugin->add_instance($course, $fields);

        return true;
    }

    public function unenrol_course($courseid) {
        global $DB;

        $roleid = get_config('community_sharecourse', 'oercoursecohortrole');
        if (is_int($roleid) && $roleid <= 0) {
            return false;
        }

        $cohortid = get_config('community_sharecourse', 'oercoursecohort');
        if (is_int($cohortid) && $cohortid <= 0) {
            return false;
        }

        if (!enrol_is_enabled('cohort')) {
            return false;
        }

        $plugin = enrol_get_plugin('cohort');

        $instance = $DB->get_record('enrol', [
                'enrol' => 'cohort',
                'courseid' => $courseid,
                'roleid' => $roleid,
                'customint1' => $cohortid,
        ]);

        if ($instance) {
            $plugin->delete_instance($instance);
        }

        return true;
    }
}
