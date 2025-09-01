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

require_once($CFG->dirroot . '/cohort/lib.php');

class main_oer {

    public static function get_tabs_by_user() {
        global $USER;

        if (!self::check_if_user_admin_or_teacher()) {
            return [];
        }

        // Check if admin.
        $isadmin = false;
        foreach (get_admins() as $admin) {
            if ($USER->id == $admin->id) {
                $isadmin = true;
                break;
            }
        }

        if ($isadmin) {
            return ['activity', 'question', 'sequence', 'course'];
        }

        // Cohorts.
        $tabs = [];
        $usercohorts = cohort_get_user_cohorts($USER->id);
        foreach ($usercohorts as $cohort) {
            if ($cohort->id == get_config('community_oer', 'oer_tab_activities')) {
                $tabs[] = 'activity';
                break;
            }
        }

        foreach ($usercohorts as $cohort) {
            if ($cohort->id == get_config('community_oer', 'oer_tab_questions')) {
                $tabs[] = 'question';
                break;
            }
        }

        foreach ($usercohorts as $cohort) {
            if ($cohort->id == get_config('community_oer', 'oer_tab_sequences')) {
                $tabs[] = 'sequence';
                break;
            }
        }

        foreach ($usercohorts as $cohort) {
            if ($cohort->id == get_config('community_oer', 'oer_tab_courses')) {
                $tabs[] = 'course';
                break;
            }
        }

        return $tabs;
    }

    public static function get_main_menu($default) {
        $menu = [];

        if (in_array('activity', self::get_tabs_by_user())) {
            $menu[] = [
                    'name' => get_string('menuactivity', 'community_oer'),
                    'area' => 'activity',
                    'active' => false,
            ];
        }

        if (in_array('question', self::get_tabs_by_user())) {
            $menu[] = [
                    'name' => get_string('menuquestion', 'community_oer'),
                    'area' => 'question',
                    'active' => false,
            ];
        }

        if (in_array('sequence', self::get_tabs_by_user())) {
            $menu[] = [
                    'name' => get_string('menusequence', 'community_oer'),
                    'area' => 'sequence',
                    'active' => false,
            ];
        }

        if (in_array('course', self::get_tabs_by_user())) {
            $menu[] = [
                    'name' => get_string('menucourse', 'community_oer'),
                    'area' => 'course',
                    'active' => false,
            ];
        }

        foreach ($menu as $key => $item) {
            if ($item['area'] == $default) {
                $menu[$key]['active'] = true;
            }
        }

        return ['menu' => $menu];
    }

    public static function check_if_user_admin_or_teacher() {
        global $USER, $DB, $CFG;

        if (isloggedin()) {
            if (!empty($CFG->defaultcohortscourserequest)) {
                $permitedcohorts = explode(',', $CFG->defaultcohortscourserequest);
                if ($permitedcohorts) {
                    require_once($CFG->dirroot . '/cohort/lib.php');
                    $cohorts = cohort_get_user_cohorts($USER->id);
                    foreach ($cohorts as $cohort) {
                        if (in_array($cohort->idnumber, $permitedcohorts)) {
                            return true;
                        }
                    }
                }
            }

            // Check if admin.
            foreach (get_admins() as $admin) {
                if ($USER->id == $admin->id) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function get_oer_category() {
        $categoryid = get_config('local_community', 'catalogcategoryid');
        return $categoryid !== null && is_numeric($categoryid) ? $categoryid : null;
    }

    public static function get_instancename() {
        global $CFG;
        return isset($CFG->instancename) ? $CFG->instancename : 'physics';
    }

    public static function structure_main_catalog($recache = false) {
        global $DB;

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'main_structure');
        $cachekey = 'structure' . self::get_oercacheversion();

        if ($recache) {
            $cache->delete($cachekey);
        }

        if (($result = $cache->get($cachekey)) === false) {

            $result = [];

            $catid = self::get_oer_category();
            $categories = $DB->get_records_sql("
                        SELECT * FROM {course_categories} 
                        WHERE path LIKE('%/" . $catid . "/%')
                        ORDER BY sortorder ASC 
                    ");

            if (!empty($categories)) {
                foreach ($categories as $cat) {

                    if ($cat->visible == 0) {
                        continue;
                    }

                    $tmp = array();
                    $tmp['cat_id'] = $cat->id;
                    $tmp['cat_name'] = $cat->name;

                    // Get category image.
                    $tmp['default_category_image'] = self::category_image_default($cat->id);

                    $sql = "
                        SELECT c.id, c.fullname, c.shortname, COUNT(c.id) AS count_activities
                        FROM {course} c                        
                        WHERE c.category=? AND c.visible=1
                        GROUP BY c.id
                        ORDER BY c.sortorder ASC
                    ";

                    $courses = array_values($DB->get_records_sql($sql, [$cat->id]));
                    foreach ($courses as $key => $course) {
                        $courses[$key]->default_course_image = self::course_image_default($course->id);
                    }

                    $tmp['courses'] = $courses;

                    if (!empty($tmp['courses'])) {
                        $result[] = $tmp;
                    }
                }
            }

            $cache->set($cachekey, $result);
            return $result;
        } else {
            return $result;
        }
    }

    public static function get_main_structure_elements() {
        global $DB;

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'main_structure_elements');
        $cachekey = 'oercatalog' . self::get_oercacheversion();

        if (($result = $cache->get($cachekey)) === false) {
            $rescategories = [];
            $rescourses = [];
            $resactivities = [];

            $catid = self::get_oer_category();
            if ($catid == null) {
                $cache->set($cachekey, [$rescategories, $rescourses, $resactivities]);
                return array($rescategories, $rescourses, $resactivities);
            }

            $categories = $DB->get_records_sql("
                        SELECT * FROM {course_categories} 
                        WHERE path LIKE('%/" . $catid . "/%')
                        ORDER BY sortorder ASC 
                    ");
            foreach ($categories as $cat) {
                $rescategories[] = $cat->id;

                $courses = $DB->get_records('course', ['category' => $cat->id]);
                foreach ($courses as $course) {
                    $rescourses[] = $course->id;

                    foreach ($DB->get_records('course_modules', ['course' => $course->id]) as $cm) {
                        $resactivities[] = $cm->id;
                    }

                    //$modinfo = get_fast_modinfo($course);
                    //foreach ($modinfo->get_section_info_all() as $item) {
                    //    $data = $item->getIterator()->getArrayCopy();
                    //
                    //    if (!isset($data['parent'])) {
                    //        $data['parent'] = 0;
                    //    }
                    //
                    //    if ($data['visible'] == 1 && $data['parent'] == 0) {
                    //        $activities = $DB->get_records('course_modules', ['section' => $data['id']]);
                    //        foreach ($activities as $activity) {
                    //            $resactivities[] = $activity->id;
                    //        }
                    //    }
                    //}
                }
            }

            $cache->set($cachekey, [$rescategories, $rescourses, $resactivities]);

            return array($rescategories, $rescourses, $resactivities);
        } else {
            return array($result[0], $result[1], $result[2]);
        }
    }

    public static function get_repository_mids() {

        $res = [];
        list($categories, $courses, $activities) = self::get_main_structure_elements();

        foreach ($activities as $cmid) {
            $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
            if (!empty($mid)) {
                $res[] = $mid;
            }
        }
        return $res;
    }

    public static function recache_main_structure_elements() {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'main_structure_elements');
        $cache->purge();

        return true;
    }

    public static function build_metadata_parameters($contextlevel, $fieldname) {
        global $DB;

        $field = $DB->get_record('local_metadata_field', ['contextlevel' => $contextlevel, 'shortname' => $fieldname]);
        $result = [];

        if(!empty($field) && $field->datatype === 'multiselect') {
            $res = preg_split('/\R/', $field->param1);
            foreach (array_unique($res) as $str) {
                list($key, $vals) = explode(':', $str);
                $arrmenu = [];
                foreach (explode('|',$vals) as $langval) {
                    list($language, $value) = explode('=', $langval);
                    $arrmenu[$language] = $value;
                }

                // Check current language on system for display.
                if(!$lang = get_parent_language()){
                    $lang = current_language();
                }

                $label = isset($arrmenu[$lang]) ? $arrmenu[$lang] : $arrmenu['en'];
                $search = $field->param2 == 1 ? 'inArray' : 'like';

                $result[] = ['uniqueid' => $fieldname.$arrmenu['en'], 'label' => $label, 'value' => $key, 'search' => $search];
            }

            return $result;
        }

        if(!empty($field) && in_array($field->datatype, ['menu', 'multimenu'])){
            $res = preg_split('/\R/', $field->param1);
            foreach(array_unique($res) as $key => $str){
                $arr = explode('|', $str);

                if(isset($arr[1]) && !empty($arr[1])) $icon = $arr[1];
                else $icon = '';

                //$result[$arr_str[0]] = $icon;
                $result[] = ['uniqueid' => $fieldname.$key, 'label' => $arr[0], 'value' => $arr[0], 'search' => 'like'];
            }

            return $result;
        }

        return false;
    }

    public static function get_metadata_name($contextlevel, $fieldname) {
        global $DB;

        $field = $DB->get_record('local_metadata_field', ['contextlevel' => $contextlevel, 'shortname' => $fieldname]);

        return $field->name;
    }

    public static function create_url_image($itemid, $context, $courseid = 0) {
        global $DB, $OUTPUT, $CFG;

        try {
            $course = get_course($courseid);
        } catch (\Exception $e) {
            $course = null;
        }

        // Default image.
        if ($course != null) {
            if (!$image = self::course_image_default($courseid)) {
                if (!$image = self::category_image_default($course->category)) {
                    $image = $OUTPUT->image_url('default-square', 'community_oer')->out(false);
                }
            }

            if (is_object($image)) {
                $image = $image->__toString();
            }
        } else {
            $image = $OUTPUT->image_url('default-square', 'community_oer')->out(false);
        }

        if (empty($itemid)) {
            return $image;
        }

        $arr = explode('/', $itemid);

        if (count($arr) == 1) {

            $sql = "SELECT * 
                    FROM {files} 
                    WHERE component = 'local_metadata' AND filearea = 'image' AND filename != '.' 
                          AND itemid = ? AND contextid = ?
                   ";

            $files = $DB->get_records_sql($sql, [$itemid, $context->id]);
            $files = array_values($files);

            $file = isset($files[0]) && !empty($files[0]) ? $files[0] : false;
            if ($file) {
                // Check if file exists.
                $fs = get_file_storage();
                $fileorigin = $fs->get_file($file->contextid, 'local_metadata', 'image', $itemid, '/', $file->filename);

                if (self::if_file_present_in_file_system($fileorigin)) {
                    $contents = $fileorigin->get_content();

                    if (!empty($contents)) {
                        /*$image = moodle_url::make_pluginfile_url($file_origin->get_contextid(), $file_origin->get_component(), $file_origin->get_filearea(), $file_origin->get_itemid(), $file_origin->get_filepath(), $file_origin->get_filename());*/
                        $image = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/local_metadata/image/' . $itemid . '/' .
                                $file->filename;
                    }
                }
            }
        }

        return $image;
    }

    public static function category_image_default($catid) {
        global $DB, $OUTPUT;

        $image = false;

        // Get category image.
        $data = \local_metadata\mcontext::category()->get($catid, 'imagecategory');

        // Default image.
        //$image = $OUTPUT->image_url('default-category', 'community_oer')->out(false);

        if (!empty($data)) {
            $sql = "
                    SELECT *
                    FROM {files}
                    WHERE component = 'local_metadata' AND filename != '.' AND itemid = ?                
                ";

            $file = $DB->get_record_sql($sql, array($data));

            if (!empty($file)) {
                $image = \moodle_url::make_pluginfile_url($file->contextid, $file->component,
                        $file->filearea, $file->itemid, $file->filepath, $file->filename, false);
            }
        }

        return $image;
    }

    public static function course_image_default($courseid) {
        global $DB;

        $image = false;

        // Get course image.
        $data = \local_metadata\mcontext::course()->get($courseid, 'imageoerdefault');

        if (!empty($data)) {
            $sql = "
                    SELECT *
                    FROM {files}
                    WHERE component = 'local_metadata' AND filename != '.' AND itemid = ?                
                ";

            $file = $DB->get_record_sql($sql, array($data));

            if (!empty($file)) {
                $image = \moodle_url::make_pluginfile_url($file->contextid, $file->component,
                        $file->filearea, $file->itemid, $file->filepath, $file->filename, false);
            }
        }

        return $image;
    }

    public static function if_file_present_in_file_system($file) {
        global $CFG;

        if ($file) {
            $contenthash = $file->get_contenthash();

            // Build real path for file.
            $l1 = $contenthash[0] . $contenthash[1];
            $l2 = $contenthash[2] . $contenthash[3];

            $path = $CFG->dataroot . "/filedir/$l1/$l2/" . $contenthash;

            return file_exists($path);
        }

        return false;
    }

    public static function total_elements_of_plugins($type, $value, $data) {

        $activity = new \community_oer\activity_oer;
        $question = new \community_oer\question_oer;
        $sequence = new \community_oer\sequence_oer;
        $course = new \community_oer\course_oer;

        return [
                $activity->get_total_elements($type, $value, $data),
                $question->get_total_elements($type, $value, $data),
                $sequence->get_total_elements($type, $value, $data),
                $course->get_total_elements($type, $value, $data),
        ];
    }

    public static function purge_structure() {

        self::structure_main_catalog(true);
        self::recache_main_structure_elements();

        $activity = new \community_oer\activity_oer();
        $activity->structure_cache()->purge();

        $question = new \community_oer\question_oer();
        $question->structure_cache()->purge();

        $sequence = new \community_oer\sequence_oer();
        $sequence->structure_cache()->purge();

        $course = new \community_oer\course_oer;
        $course->structure_cache()->purge();
    }

    public static function set_oercacheversion() {
        set_config('oercacheversion', time(), 'community_oer');
    }

    public static function get_oercacheversion() {
        return get_config('community_oer', 'oercacheversion');
    }

    public static function if_activity_in_research_mode($cmid) {
        global $USER, $DB;

        $sql = "
            SELECT cm.id, cm.course, m.name as module
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(m.id = cm.module)
            WHERE cm.id = ?        
        ";

        $mod = $DB->get_record_sql($sql, [$cmid]);

        if (!empty($mod) && $mod->module !== 'quiz') {
            return false;
        }

        $admins = [];
        foreach (get_admins() as $admin) {
            $admins[] = $admin->id;
        }

        if (in_array($USER->id, $admins)) {
            return false;
        }

        if (\local_metadata\mcontext::module()->get($cmid, 'cresearch') == 1) {
            return true;
        }

        return false;
    }

    public static function is_activity_in_research($cmid) {
        $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
        $cresearch = \local_metadata\mcontext::module()->get($mid, 'cresearch');
        return $cresearch;
    }
}
