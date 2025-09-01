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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');

class course_oer {

    public $cache;
    public $key;
    public $revision;

    public function __construct() {
        global $DB;

        $this->cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_course_cache', 'courses');
        $this->revision = \community_oer\main_oer::get_oercacheversion();
        $this->key = 'data' . $this->revision;

        $count = $DB->count_records('community_oer_course');
        if ($count > count($this->get_courses_from_cache())) {
            $this->recalculate_data_in_cache();
        }
    }

    public function structure_cache() {
        return \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'course_structure');
    }

    public function structure_course_catalog() {
        $cache = $this->structure_cache();
        $key = 'structure' . $this->revision;

        if (($result = $cache->get($key)) === false) {
            $result = \community_oer\main_oer::structure_main_catalog();
            $cache->set($key, $result);
            return $result;
        } else {
            return $result;
        }
    }

    public function get_courses_and_sections_for_current_user() {
        global $DB, $USER;

        $mycourses = enrol_get_my_courses('*', 'id DESC');
        foreach ($mycourses as $key => $item) {
            $mycourses[$key]->lastaccess = 0;
        }

        // Sort courses by last access of current user.
        $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');
        foreach ($lastaccesscourses as $c) {
            if (isset($mycourses[$c->courseid])) {
                $mycourses[$c->courseid]->lastaccess = $c->timeaccess;
            }
        }
        // Sort by user's lastaccess to course.
        usort($mycourses, function($a, $b) {
            return $b->lastaccess - $a->lastaccess;
        });

        $structure = $this->structure_course_catalog();

        foreach ($structure as $item) {
            foreach ($item['courses'] as $course) {
                $coursesmaagar[] = $course->id;
            }
        }

        $result = array();
        $rolespermitted = array('editingteacher');
        foreach ($mycourses as $course) {

            $context = \context_course::instance($course->id);
            $roles = get_user_roles($context, $USER->id, true);
            $flagpermission = false;
            foreach ($roles as $role) {
                if (in_array($role->shortname, $rolespermitted)) {
                    $flagpermission = true;
                }
            }

            if (!in_array($course->id, $coursesmaagar) && $flagpermission) {
                $tmpcourse = new \StdClass;
                $tmpcourse->courseid = $course->id;
                $tmpcourse->coursename = $course->fullname;

                $sql = "SELECT cs.*
                        FROM {course} c
                        LEFT JOIN {course_sections} cs ON c.id=cs.course
                        WHERE cs.section!=0 AND c.id=?";
                $sections = $DB->get_records_sql($sql, array($course->id));
                $sections = array_values($sections);

                $tmpsections = [];
                foreach ($sections as $section) {
                    $sectionname = course_get_format($course)->get_section_name($section->section);
                    $tmpsections[] = ['sectionid' => $section->id, 'sectionname' => $sectionname];
                }

                if (!empty($tmpsections)) {
                    $tmpcourse->sections = $tmpsections;
                    $result['courses'][] = $tmpcourse;
                }
            }
        }

        return $result;
    }

    private function build_activities_data($cmids) {
        global $DB;

        if (empty($cmids)) {
            return false;
        }

        $query = "SELECT cm.id AS cmid, cm.course AS courseid, c.category AS catid, cm.instance AS instance,
                         cm.added AS cm_created, cm.visible AS visible, cm.module AS modid, m.name AS mod_type,
                         cs.id AS sectionid, cs.name AS section_name, cs.summary AS section_summary,
                         cs.sequence AS section_sequence, cs.section AS section_order,
                         GROUP_CONCAT(DISTINCT t.rawname SEPARATOR ',') AS tags
                    FROM {course_modules} cm
               LEFT JOIN {course} c ON (cm.course = c.id)
               LEFT JOIN {modules} m ON (cm.module = m.id)
               LEFT JOIN {course_sections} cs ON cm.section = cs.id
               LEFT JOIN {tag_instance} ti ON (ti.itemid = cm.id)
               LEFT JOIN {tag} t ON (ti.tagid = t.id)
               LEFT JOIN {competency_modulecomp} compm ON (cm.id = compm.cmid)
                   WHERE cm.deletioninprogress != 1
                         AND cm.id IN (" . implode(',', $cmids) . ")
                GROUP BY cm.id";

        $objs = $DB->get_records_sql($query, []);

        if (empty($objs)) {
            return false;
        }

        $data = [];
        foreach ($objs as $obj) {

            $obj->uniqueid = time() . $obj->cmid . $obj->courseid . $obj->catid . $obj->sectionid;

            // Mod details.
            $tmp = $DB->get_record($obj->mod_type, ['id' => $obj->instance]);
            $obj->mod_name = isset($tmp->name) ? $tmp->name : '';
            $obj->mod_intro = isset($tmp->intro) ? $tmp->intro : '';

            // Activity url.
            try {
                list($course, $cm) = get_course_and_cm_from_cmid($obj->cmid);
                $obj->urlactivity = isset($cm->url) ? $cm->url->out() : false;
            } catch (\moodle_exception $e) {
                $obj->urlactivity = false;
            }

            // Format date create.
            $obj->cm_created_format = date('d.m.Y', $obj->cm_created);

            $data[] = $obj;
        }

        return $data;
    }

    private function build_course_data($courseid, $subject) {
        global $DB, $CFG, $OUTPUT;

        $course = $DB->get_record('course', ['id' => $courseid]);

        // PTL-8368.
        if (empty($course)) {
            return false;
        }

        //if(empty($course) || $course->visible == 0) return false;

        $obj = new \stdClass();

        $pcourseid = $subject;
        $row = $DB->get_record('course', ['id' => $pcourseid]);

        $pcatid = isset($row->category) ? $row->category : 0;

        if (!$pcatid && !$pcourseid) {
            return false;
        }

        $obj->uniqueid = time() . $courseid . $pcatid . $pcourseid;
        $obj->cid = $courseid;
        $obj->catid = $pcatid;
        $obj->courseid = $pcourseid;
        $obj->sectionid = 0;

        $obj->category = $course->category;
        $obj->fullname = $course->fullname;
        $obj->shortname = $course->shortname;
        $obj->idnumber = $course->idnumber;

        // Sections.
        $sections = [];
        $format = \course_get_format($courseid);
        foreach ($DB->get_records('course_sections', ['course' => $courseid]) as $section) {
            if ($section->section != 0) {
                $sections[] = $format->get_section_name($section->section);
            }
        }
        $obj->sections = $sections;
        $obj->count_sections = count($sections);
        $obj->sections_search = implode(',', $sections);

        // Metadata details.
        $category = $DB->get_record('local_metadata_category', ['contextlevel' => CONTEXT_COURSE], '*', IGNORE_MULTIPLE);

        if (!empty($category)) {
            $query = "
                SELECT id, shortname
                FROM {local_metadata_field}
                WHERE contextlevel = ? AND categoryid = ?
                ORDER BY sortorder ASC
            ";

            $fields = $DB->get_records_sql($query, [CONTEXT_COURSE, $category->id]);
            foreach ($fields as $field) {
                $name = 'metadata_' . $field->shortname;
                $obj->$name = \local_metadata\mcontext::course()->get($obj->cid, $field->shortname);
            }
        }

        // Visible.
        $obj->visible = isset($obj->metadata_chidden) && $obj->metadata_chidden != 1 ? 1 : 0;

        // Course image.
        $metadataimagecourse = isset($obj->metadata_cimage) ? $obj->metadata_cimage : '';
        $context = \context_course::instance($courseid);
        $obj->imagecourse = \community_oer\main_oer::create_url_image($metadataimagecourse, $context, $obj->cid);

        // User details.
        $obj->users = [];
        $username = '';
        if (isset($obj->metadata_cuserid) && !empty($obj->metadata_cuserid)) {
            foreach (explode(',', $obj->metadata_cuserid) as $userid) {
                if ($user = $DB->get_record('user', ['id' => trim($userid)])) {
                    $tmp = new \StdClass();
                    $tmp->userid = $user->id;
                    $tmp->user_fname = $user->firstname;
                    $tmp->user_lname = $user->lastname;
                    $tmp->user_image = $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg';

                    $obj->users[] = $tmp;
                    $username .= ' ' . implode(' ', [$tmp->user_fname, $tmp->user_lname]);
                }
            }
        } else {
            // Default user.
            $tmp = new \StdClass();
            $tmp->userid = 0;
            $tmp->user_fname = get_string('defaultuser', 'community_oer');
            $tmp->user_lname = '';
            $tmp->user_image = $OUTPUT->image_url('avatar', 'community_oer')->out(false);

            $obj->users[] = $tmp;
            $username .= implode(' ', [$tmp->user_fname, $tmp->user_lname]);
        }

        $obj->username = $username;

        // Course url.
        $obj->urlcourse = $CFG->wwwroot . '/course/view.php?id=' . $obj->cid;

        // Format date create.
        $obj->c_shared_at_format = date('d.m.Y', $obj->metadata_cshared_at);

        // Course shared by users and count used course.
        $sql = "
            SELECT *
            FROM {community_sharecourse_shr}
            WHERE courseid = ? AND type = 'coursecopy'            
        ";

        $copycourses = $DB->get_records_sql($sql, [$obj->cid]);

        $coursesharedusers = [];
        foreach ($copycourses as $item) {
            $coursesharedusers[] = $item->useridfrom;
        }

        $coursesharedusers = array_unique($coursesharedusers);

        $obj->course_shared_users = json_encode($coursesharedusers);
        $obj->count_used_course = count($copycourses);

        // Prepare activity.
        $activities = $cmids = [];
        foreach ($DB->get_records('course_modules', ['course' => $courseid, 'visible' => 1]) as $cm) {
            $cmids[] = $cm->id;
        }

        if ($data = $this->build_activities_data($cmids)) {
            $activities = $data;
        }

        $obj->activities = $activities;

        return $obj;
    }

    public function course_recalculate_in_db($courseid) {
        global $DB;

        $res = false;
        $csubject = \local_metadata\mcontext::course()->get($courseid, 'csubject');
        if (!empty($csubject)) {
            foreach (explode(',', $csubject) as $subject) {

                $data = $this->build_course_data($courseid, $subject);
                if ($data == false) {
                    continue;
                }

                $row = $DB->get_record('community_oer_course', [
                        'cid' => $courseid,
                        'catid' => $data->catid,
                        'courseid' => $data->courseid,
                        'sectionid' => $data->sectionid,
                ]);
                if (empty($row)) {
                    $obj = new \StdClass();
                    $obj->uniqueid = $data->uniqueid;
                    $obj->cid = $data->cid;
                    $obj->catid = $data->catid;
                    $obj->courseid = $data->courseid;
                    $obj->sectionid = $data->sectionid;
                    $obj->recache = 0;
                    $obj->data = json_encode($data);
                    $obj->timecreated = time();
                    $obj->timemodified = time();

                    $DB->insert_record('community_oer_course', $obj);
                } else {
                    $row->uniqueid = $data->uniqueid;
                    $row->cid = $data->cid;
                    $row->catid = $data->catid;
                    $row->courseid = $data->courseid;
                    $row->sectionid = $data->sectionid;
                    $row->recache = 0;
                    $row->data = json_encode($data);
                    $row->timecreated = time();
                    $row->timemodified = time();

                    $DB->update_record('community_oer_course', $row);
                }

                $res = true;
            }
        }

        return $res;
    }

    public function recalculate_all_courses_in_db_cache() {
        global $DB;

        raise_memory_limit(MEMORY_UNLIMITED);

        $DB->execute("TRUNCATE TABLE {community_oer_course}");

        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        foreach ($DB->get_records('course') as $course) {
            if (!in_array($course->id, $courses) && $course->category != 0) {
                $this->course_recalculate_in_db($course->id);
            }
        }

        $this->recalculate_data_in_cache();

        return true;
    }

    public function build_standart_info_for_page() {
        $result = [];

        $result['dashboard'] = 'activity';

        // Side panel menu.
        $result['aside_menu'] = self::funcs()::build_menu();

        // Filters.
        $result['filters'] = self::funcs()::build_filters();

        // Sorting.
        $sorting = \community_oer\course_help::sorting_elements();

        // Set default.
        $default = get_config('community_oer', 'default_sort_course');
        foreach ($sorting as $key => $item) {
            $sorting[$key]['selected'] = ($item['value'] == $default) ? true : false;
        }
        $result['sorting'] = $sorting;

        return $result;
    }

    public function calculate_data_online($obj) {
        global $DB, $USER;

        foreach ($obj->data as $key => $item) {

            // Course copy disabled for user.
            $obj->data[$key]->course_shared_disabled = ($item->metadata_callowfullcopy == 1) ? false : true;

            // Check if user shared course.
            foreach ($item->users as $el) {
                if ($el->userid == $USER->id) {
                    $obj->data[$key]->course_shared_disabled = true;
                    break;
                }
            }

            $errorcategory = 1;
            if (!$obj->data[$key]->course_shared_disabled && isset($USER->idnumber)) {
                if ($DB->get_records('course_categories', ['idnumber' => $USER->idnumber])) {
                    $errorcategory = 0;
                }
            }

            $obj->data[$key]->error_category = is_siteadmin($USER) ? 0 : $errorcategory;

            // Course link.
            $courselink = new \moodle_url('/course/view.php', ['id' => $item->cid]);
            $obj->data[$key]->courselink = $courselink->out();

            // Course share for user.
            if (in_array($USER->id, json_decode($item->course_shared_users))) {
                $obj->data[$key]->course_shared = true;
            } else {
                $obj->data[$key]->course_shared = false;
            }

            // Show teacher reviews.
            $obj->data[$key]->showteacherrevies = true;
            $reviews = $DB->get_records('community_oer_reviews', array('objid' => $item->cid, 'reviewtype' => 'course'));
            $obj->data[$key]->countreview = count($reviews);

            // Url to social profile.
            foreach ($obj->data[$key]->users as $key2 => $item2) {
                $urltosocialprofile = "javascript:void(0)";
                $urltosocialtarget = false;
                if (isset($item2->userid) && get_user_preferences('community_social_enable', '', $item2->userid)) {
                    $urltosocialprofile = new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $item2->userid]);
                    $urltosocialprofile = $urltosocialprofile->out();
                    $urltosocialtarget = true;
                }
                $obj->data[$key]->users[$key2]->urltosocialprofile = $urltosocialprofile;
                $obj->data[$key]->users[$key2]->urltosocialtarget = $urltosocialtarget;
            }

            // Prepare metadata_csource.
            if (isset($item->metadata_csource) && !empty($item->metadata_csource)) {

                // Explode words.
                $w = [];
                foreach (explode(' ', $item->metadata_csource) as $word) {
                    if (!empty($word)) {
                        $w[] = $word;
                    }
                }

                // Convert url to link.
                foreach ($w as $num => $word) {
                    $match = [];
                    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $word, $match);

                    if (isset($match[0]) && !empty($match[0])) {
                        foreach ($match[0] as $url) {
                            $newurl = '<a href="' . $url . '" target="_blank">' . get_string('link', 'community_oer') . '</a>';
                            $word = str_replace($url, $newurl, $word);
                        }

                        $w[$num] = $word;
                    }
                }

                $maxlengthrow = 20;
                $row = '';
                $string = '<span class="tooltip-inner-span">' . get_string('base_on_activity', 'community_oer') . '</span>';
                $countchars = 0;
                foreach ($w as $word) {
                    $countchars += mb_strlen(strip_links($word)) + 1;

                    if ($countchars >= $maxlengthrow) {
                        $row .= ' ' . $word;
                        $string .= '<span class="tooltip-inner-span">' . $row . '</span>';
                        $row = '';
                        $countchars = 0;
                    } else {
                        $row .= ' ' . $word;
                    }
                }

                if (!empty($row)) {
                    $string .= '<span class="tooltip-inner-span">' . $row . '</span>';
                }

                $obj->data[$key]->metadata_csource = $string;
            }
        }

        return $obj;
    }

    public function get_total_elements($type, $value, $data) {

        switch ($type) {
            case 'category':
                $newcache = $this->query()->compare('catid', $value)->get();
                break;
            case 'course':
                $newcache = $this->query()->compare('courseid', $value)->get();
                break;
            case 'section':
                $newcache = $this->query()->compare('sectionid', $value)->get();
                break;
        }

        $obj = $this->query($newcache)->compare('visible', '1')->groupBy('cid');

        // Search.
        $flag = false;
        $flag2 = false;

        // Search in activities.
        $newcacheact = [];
        foreach ($obj->get() as $c) {
            foreach ($c->activities as $cm) {
                $cm->custom_uniqueid = $c->uniqueid;
                $newcacheact[$cm->uniqueid] = $cm;
            }
        }

        foreach ($data as $item) {
            if ($item->area == 'pillsearch') {

                $activity = new \community_oer\activity_oer;
                $act = $activity->query($newcacheact)->compare('visible', '1');

                if (!empty($item->value)) {
                    $obj = ($flag) ? $obj->orLikeLower('fullname', trim($item->value)) :
                            $obj->likeLower('fullname', trim($item->value));
                    $flag = true;

                    $obj = ($flag) ? $obj->orLikeLower('metadata_cdescription', trim($item->value)) :
                            $obj->likeLower('metadata_cdescription', trim($item->value));
                    $obj = ($flag) ? $obj->orLikeLower('username', trim($item->value)) :
                            $obj->likeLower('username', trim($item->value));
                    $obj = ($flag) ? $obj->orLikeLower('sections_search', trim($item->value)) :
                            $obj->likeLower('sections_search', trim($item->value));

                    // Activities.
                    $act = ($flag2) ? $act->orLikeLower('mod_name', trim($item->value)) :
                            $act->likeLower('mod_name', trim($item->value));
                    $flag2 = true;

                    foreach ($act->get() as $cm) {
                        $obj = ($flag) ? $obj->orCompare('uniqueid', trim($cm->custom_uniqueid)) :
                                $obj->compare('uniqueid', trim($cm->custom_uniqueid));
                        break;
                    }
                }
            }
        }

        return $obj->count();
    }

    // Cache rebuild.
    public function recalculate_data_in_cache() {
        global $DB;

        $result = [];
        foreach ($DB->get_records('community_oer_course') as $item) {
            $result[$item->uniqueid] = json_decode($item->data);
        }

        $this->cache->purge();
        $this->cache->set($this->key, $result);

        return true;
    }

    // Get courses from cache.
    public function get_courses_from_cache() {
        if (($datacache = $this->cache->get($this->key)) === false) {
            $datacache = [];
        }

        return $datacache;
    }

    // Update observer.
    public function update_observer($courseid) {
        global $DB;

        $DB->delete_records('community_oer_course', ['cid' => $courseid]);

        if ($this->course_recalculate_in_db($courseid)) {
            $this->recalculate_data_in_cache();
        }

        return true;
    }

    // Query in cache.
    public function query($data = -1) {
        if ($data == -1) {
            $data = $this->get_courses_from_cache();
        }

        $query = new \community_oer\object_query($data, 'uniqueid');
        return $query;
    }

    public static function funcs() {
        return new \community_oer\course_help();
    }
}

class course_help {

    public static function build_menu() {
        $course = new \community_oer\course_oer;
        $structure = $course->structure_course_catalog();
        foreach ($structure as $cat) {
            foreach ($cat['courses'] as $key => $objcourse) {
                $sections = [];

                $modinfo = get_fast_modinfo($objcourse->id);
                foreach ($modinfo->get_section_info_all() as $item) {
                    $data = $item->getIterator()->getArrayCopy();

                    if (!$data['visible']) {
                        continue;
                    }

                    if (!isset($data['parent'])) {
                        $data['parent'] = 0;
                    }

                    if ($data['sectionnum'] != 0 && $data['visible'] == 1 && $data['parent'] == 0) {

                        // If no courses in section.
                        //$courses = $course->query()->compare('sectionid', $data['id'])->compare('visible', '1')->get();
                        //if (!empty($courses)) {
                        //    $sectionname = course_get_format($objcourse)->get_section_name($data['sectionnum']);
                        //    $sections[] = ['sectionid' => $data['id'], 'section_name' => $sectionname];
                        //}

                        $sectionname = course_get_format($objcourse->id)->get_section_name($data['sectionnum']);
                        $sections[] = ['sectionid' => $data['id'], 'section_name' => $sectionname];
                    }
                }

                $cat['courses'][$key]->sections = $sections;
            }
        }

        return $structure;
    }

    public static function get_metadata_value($string) {
        if (!empty($string)) {
            $arr = explode('|', $string);
            return $arr[0];
        }

        return $string;
    }

    public static function get_metadata_parameter($string) {
        if (!empty($string)) {
            $arr = explode('|', $string);

            if (isset($arr[1]) && !empty($arr[1])) {
                return $arr[1];
            } else {
                return $arr[0];
            }
        }

        return $string;
    }

    public static function build_filters() {
        $result = [];

        list($filtertypes, $filtersingle, $filtermore) = self::prepare_data_for_filters();

        $group = 1400;

        // Filter singles.
        if (!empty($filtersingle)) {
            foreach ($filtersingle as $item) {

                if ($item['type'] != 'metadata') {
                    continue;
                }

                $result[] = [
                        'nav_title' => $item['name'],
                        'nav_id' => 'filternav' . $group,
                        'columns' => [
                                'title' => '',
                                'data' => self::rebuild_filter_metadata(
                                        \community_oer\main_oer::build_metadata_parameters(CONTEXT_COURSE, $item['value']),
                                        'metadata_' . $item['value'], $group
                                )
                        ]
                ];

                $group += 10;
            }
        }

        // Filter more.
        if (!empty($filtermore)) {
            $columns = [];

            foreach ($filtermore as $item) {
                if ($item['type'] == 'metadata') {
                    $columns[] = [
                            'title' => \community_oer\main_oer::get_metadata_name(CONTEXT_COURSE, $item['value']),
                            'data' => self::rebuild_filter_metadata(
                                    \community_oer\main_oer::build_metadata_parameters(CONTEXT_COURSE, $item['value']),
                                    'metadata_' . $item['value'], $group
                            )
                    ];

                    $group += 10;
                }
            }

            $arr = [];
            foreach ($filtermore as $item) {
                if ($item['type'] == 'mod_type') {

                    $arr['title'] = '';
                    $arr['data'][] = [
                            'uniqueid' => 'filter-' . $item['value'], 'value' => $item['value'],
                            'print_value' => '111',
                            'field' => 'mod_type', 'search' => 'equal', 'group' => '10000'
                    ];

                    if (count($arr['data']) == 4) {
                        $columns[] = $arr;
                        $arr['data'] = [];
                    }
                }
            }

            $columns[] = $arr;

            $fullwidth = count($columns) > 3 ? true : false;
            $result[] = [
                    'nav_title' => get_string('filter3title', 'community_oer'),
                    'nav_id' => 'filternav4',
                    'columns' => $columns,
                    'fullwidth' => $fullwidth
            ];
        }

        return $result;
    }

    public static function prepare_data_for_filters() {
        global $DB;

        $filtertypes = [];
        $filtersingle = [];
        $filtermore = [];

        // Build filters from metadata.
        $query = "
            SELECT * 
            FROM {local_metadata_field}
            WHERE contextlevel = ? AND datatype IN ('menu', 'multimenu', 'multiselect')
            ORDER BY sortorder ASC
        ";

        foreach ($DB->get_records_sql($query, [CONTEXT_COURSE]) as $item) {
            switch ($item->visible) {
                case PROFILE_VISIBLE_PRIVATE:
                    $filtersingle[] = ['type' => 'metadata', 'name' => $item->name, 'value' => $item->shortname];
                    break;
                case PROFILE_VISIBLE_ALL:
                    $filtermore[] = ['type' => 'metadata', 'name' => $item->name, 'value' => $item->shortname];
                    break;
            }
        }

        return [$filtertypes, $filtersingle, $filtermore];
    }

    public static function rebuild_filter_metadata($data, $field, $group) {
        foreach ($data as $key => $item) {
            $data[$key]['uniqueid'] = $field . $key;
            $data[$key]['field'] = $field;
            $data[$key]['search'] = isset($item['search']) ? $item['search'] : 'like';
            $data[$key]['print_value'] = isset($item['label']) ? $item['label'] : $item['value'];
            $data[$key]['group'] = $group;
        }

        return $data;
    }

    public static function get_course_shared($courseid) {

        $course = new \community_oer\course_oer;
        $data = $course->query()->compare('cid', $courseid)->get();

        if (empty($data)) {
            return false;
        }

        $data = array_values($data);
        return isset($data[0]) ? $data[0] : false;
    }

    public static function if_course_shared($courseid) {

        $obj = self::get_course_shared($courseid);
        return ($obj && $obj->visible == 1) ? true : false;
    }

    public static function sorting_elements() {
        return [
                0 => ['name' => get_string('sorting1', 'community_oer'), 'value' => '1'],
                1 => ['name' => get_string('sorting2', 'community_oer'), 'value' => '2'],
                2 => ['name' => get_string('sorting3', 'community_oer'), 'value' => '3'],
                3 => ['name' => get_string('sorting4', 'community_oer'), 'value' => '4'],
        ];
    }
}
