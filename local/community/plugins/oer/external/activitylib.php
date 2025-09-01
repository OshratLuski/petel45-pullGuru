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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

class community_oer_activity_external extends external_api {

    public static function get_activity_instance_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    public static function get_activity_instance() {
        $context = \context_system::instance();
        self::validate_context($context);

        $activity = new \community_oer\activity_oer;

        return json_encode($activity->build_standart_info_for_page());
    }

    public static function get_activity_instance_returns() {
        return new external_value(PARAM_RAW, 'The html of copy questiions to quiz');
    }

    public static function get_activity_blocks_parameters() {
        return new external_function_parameters(
                array(
                        'presets' => new external_value(PARAM_RAW, 'Json preset parameters'),
                )
        );
    }

    public static function get_activity_blocks($presets) {
        global $DB, $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_activity_blocks_parameters(),
                array(
                        'presets' => $presets,
                )
        );

        $data = json_decode($params['presets']);
        $event = [];

        $activity = new \community_oer\activity_oer;

        // Get data by catid, courseid, sectionid.
        foreach ($data as $item) {
            if ($item->area == 'sidemenu') {
                switch ($item->action) {
                    case 'category':
                        $newcache = $activity->query()->compare('catid', $item->value)->get();
                        break;
                    case 'course':
                        $newcache = $activity->query()->compare('courseid', $item->value)->get();
                        break;
                    case 'section':
                        $newcache = $activity->query()->compare('sectionid', $item->value)->get();
                        break;
                }

                list($activitytotal, $questiontotal, $sequencetotal, $coursetotal) =
                        \community_oer\main_oer::total_elements_of_plugins($item->action, $item->value, $data);

                $event[$item->area] = [$item->action => $item->value];
            }
        }

        $obj = $activity->query($newcache)->compare('visible', '1');

        // Get by filter.
        $groups = [];
        foreach ($data as $item) {
            if ($item->area == 'filters') {
                $groups[] = $item->group;
            }
        }

        $groups = array_unique($groups);
        ksort($groups);

        $obj = $activity->query($obj->get());
        foreach ($groups as $group) {
            $flag = 0;
            foreach ($data as $item) {
                if ($item->area == 'filters' && $item->group == $group) {
                    if ($flag == 0) {
                        switch ($item->search) {
                            case 'equal':
                                $obj = $obj->compare($item->action, $item->value);
                                break;
                            case 'like':
                                $obj = $obj->like($item->action, $item->value);
                                break;
                            case 'notIn':
                                $obj = $obj->notIn($item->action, $item->value);
                                break;
                            case 'inArray':
                                $obj = $obj->inArray($item->action, $item->value);
                                break;
                            default:
                                $obj = $obj->like($item->action, $item->value);
                        }
                    } else {
                        switch ($item->search) {
                            case 'equal':
                                $obj = $obj->orCompare($item->action, $item->value);
                                break;
                            case 'like':
                                $obj = $obj->orLike($item->action, $item->value);
                                break;
                            case 'notIn':
                                $obj = $obj->orNotIn($item->action, $item->value);
                                break;
                            case 'inArray':
                                $obj = $obj->orInArray($item->action, $item->value);
                                break;
                            default:
                                $obj = $obj->orLike($item->action, $item->value);
                        }
                    }

                    $flag++;
                    $event[$item->area][] = [$item->action => $item->value];
                }
            }

            $obj = $activity->query($obj->get());
        }

        // Calculate data online.
        $obj = $activity->calculate_data_online($obj, 'oercatalog');

        // Search.
        foreach ($data as $item) {
            if ($item->area == 'pillsearch') {
                if (!empty($item->value)) {
                    $flag = false;

                    $obj = ($flag) ? $obj->orLikeLower('mod_name', trim($item->value)) :
                            $obj->likeLower('mod_name', trim($item->value));
                    $flag = true;

                    $obj = ($flag) ? $obj->orLikeLower('metadata_teacherremarks', trim($item->value)) :
                            $obj->likeLower('metadata_teacherremarks', trim($item->value));
                    $obj = ($flag) ? $obj->orLikeLower('username', trim($item->value)) :
                            $obj->likeLower('username', trim($item->value));

                    $event['search'][] = $item->value;

                    $obj = $activity->query($obj->get());
                }
            }
        }

        // Group by.
        $obj = $obj->orderNumber('cmid', 'asc')->groupBy('mod_name');

        // Sorting.
        foreach ($data as $item) {
            if ($item->area == 'sorting') {
                switch ($item->value) {
                    case 1:
                        $obj = $obj->orderString('username', 'asc');
                        $event[$item->area] = 'username';
                        break;
                    case 2:
                        $obj = $obj->orderNumber('count_used_activity', 'desc');
                        $event[$item->area] = 'count_used_activity';
                        break;
                    case 3:
                        $obj = $obj->orderNumber('cm_updated', 'desc');
                        $event[$item->area] = 'activity_updated';
                        break;
                    case 4:
                        $obj = $obj->orderString('mod_name', 'asc');
                        $event[$item->area] = 'mod_name';
                        break;
                    case 5:
                        $obj = $obj->orderString('order', 'asc');
                        $event[$item->area] = 'order';
                        break;
                    case 6:
                        $obj = $obj->orderString('count_student_response', 'desc');
                        $event[$item->area] = 'count_student_response';
                        break;
                }
            }
        }

        // Pagination.
        $itemsonpage = get_config('community_oer', 'activity_items_on_page');
        $totalblocks = $obj->count();

        $pagination = [];
        if ($itemsonpage) {

            $pages = intdiv($totalblocks, $itemsonpage);
            if ($totalblocks % $itemsonpage > 0) {
                $pages += 1;
            }

            $itemvalue = 1;
            foreach ($data as $item) {
                if ($item->area == 'paging') {
                    switch ($item->value) {
                        case 'previus':
                            $itemvalue = ($item->page > 1) ? $item->page - 1 : $item->page;
                            break;
                        case 'next':
                            $itemvalue = ($item->page < $pages) ? $item->page + 1 : $item->page;
                            break;
                        default:
                            if ($item->value > $pages) {
                                $item->value = 1;
                            }
                            $itemvalue = $item->value;
                    }
                }
            }

            for ($i = 1; $i <= $pages; $i++) {
                $pagination[] = [
                        'value' => $i,
                        'active' => ($i == $itemvalue) ? true : false,
                        'show' => true
                ];
            }

            $start = ($itemsonpage * $itemvalue) - $itemsonpage + 1;
            $obj = $obj->limit($start, $itemsonpage);
        }

        // Rebuild paging.
        $maxpaginationrow = 20;
        if (count($pagination) > $maxpaginationrow) {
            $activekey = 0;
            foreach ($pagination as $key => $page) {
                $pagination[$key]['show'] = false;

                if ($page['active'] == 1) {
                    $activekey = $key;
                }
            }

            $i = 1;
            $pagination[$activekey]['show'] = true;
            do {
                // Left side.
                if (isset($pagination[$activekey - $i])) {
                    $pagination[$activekey - $i]['show'] = true;
                    $maxpaginationrow--;
                }

                // Right side.
                if (isset($pagination[$activekey + $i])) {
                    $pagination[$activekey + $i]['show'] = true;
                    $maxpaginationrow--;
                }

                if (!isset($pagination[$activekey + $i]) && !isset($pagination[$activekey - $i])) {
                    $maxpaginationrow--;
                }

                $i++;
            } while ($maxpaginationrow > 0);
        }

        $prevpaginationdisable = false;
        $firstkey = array_key_first($pagination);
        if (isset($pagination[$firstkey]) && !empty($pagination[$firstkey]) && $pagination[$firstkey]['active'] == 1) {
            $prevpaginationdisable = true;
        }

        $nextpaginationdisable = false;
        $lastkey = array_key_last($pagination);
        if (isset($pagination[$lastkey]) && !empty($pagination[$lastkey]) && $pagination[$lastkey]['active'] == 1) {
            $nextpaginationdisable = true;
        }

        $result = [
                'blocks' => array_values($obj->get()),
                'total_blocks' => $totalblocks,
                'activity_total_all_blocks' => $activitytotal,
                'question_total_all_blocks' => $questiontotal,
                'sequence_total_all_blocks' => $sequencetotal,
                'course_total_all_blocks' => $coursetotal,
                'pagination' => $pagination,
                'default_page' => $itemvalue,
                'enable_pagination' => !empty($pagination) && count($pagination) > 1 ? true : false,
                'prev_pagination_disable' => $prevpaginationdisable,
                'next_pagination_disable' => $nextpaginationdisable,
                'template_type' => 'activity'
        ];

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'data' => $event,
        );
        \community_oer\event\oer_activity_filter::create_event($eventdata)->trigger();

        return json_encode($result);
    }

    public static function get_activity_blocks_returns() {
        return new external_value(PARAM_RAW, 'The blocks settings');
    }

    public static function get_my_courses_and_sections_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    public static function get_my_courses_and_sections() {
        $context = \context_system::instance();
        self::validate_context($context);

        $activity = new \community_oer\activity_oer;

        return json_encode($activity->get_courses_and_sections_for_current_user());
    }

    public static function get_my_courses_and_sections_returns() {
        return new external_value(PARAM_RAW, 'My corses and sections');
    }

    public static function copy_activity_to_section_parameters() {
        return new external_function_parameters(
                array(
                        'sectionid' => new external_value(PARAM_INT, 'Section id'),
                        'cmid' => new external_value(PARAM_INT, 'Course module id'),
                        'referer' => new external_value(PARAM_RAW, 'Referer'),
                )
        );
    }

    public static function copy_activity_to_section($sectionid, $cmid, $referer) {
        global $DB, $USER, $CFG;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::copy_activity_to_section_parameters(),
                array(
                        'sectionid' => $sectionid,
                        'cmid' => $cmid,
                        'referer' => $referer,
                )
        );

        $cmid = $params['cmid'];
        $section = $DB->get_record('course_sections', array('id' => $params['sectionid']));

        $metadata = array("notification" => 'bankdownload');

        // Posible values of referer: 'oercatalog', 'view', 'social'.
        $metadata['referer'] = $params['referer'];
        $metadata['activitysequence'] = 0;
        $metadata = json_encode($metadata);

        $activity = new \community_oer\activity_oer;
        \community_oer\activity_oer::funcs()::check_metadata_id($cmid);

        \community_sharewith\funcs::add_task('activitycopy', $USER->id, $USER->id, null, $section->course, null,
                $section->id, null, $cmid, $metadata);

        return '';
    }

    public static function copy_activity_to_section_returns() {
        return new external_value(PARAM_RAW, 'Status');
    }

    public static function activity_get_single_page_parameters() {
        return new external_function_parameters(
                array(
                        'cmid' => new external_value(PARAM_INT, 'Course module id'),
                )
        );
    }

    public static function activity_get_single_page($cmid) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::activity_get_single_page_parameters(),
                array(
                        'cmid' => $cmid,
                )
        );

        $activity = new \community_oer\activity_oer;
        $res['blocks'][0] = $activity->single_cmid_render_data($params['cmid'], 'view');

        return json_encode($res);
    }

    public static function activity_get_single_page_returns() {
        return new external_value(PARAM_RAW, 'Data');
    }
}
