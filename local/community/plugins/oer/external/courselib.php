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

class community_oer_course_external extends external_api {

    public static function get_course_instance_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    public static function get_course_instance() {
        $context = \context_system::instance();
        self::validate_context($context);

        $course = new \community_oer\course_oer();

        return json_encode($course->build_standart_info_for_page());
    }

    public static function get_course_instance_returns() {
        return new external_value(PARAM_RAW, 'The html of oer course');
    }

    public static function get_course_blocks_parameters() {
        return new external_function_parameters(
                array(
                        'presets' => new external_value(PARAM_RAW, 'Json preset parameters'),
                )
        );
    }

    public static function get_course_blocks($presets) {
        global $DB, $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_course_blocks_parameters(),
                array(
                        'presets' => $presets,
                )
        );

        $data = json_decode($params['presets']);
        $event = [];

        $course = new \community_oer\course_oer;

        // Get data by catid, courseid, sectionid.
        foreach ($data as $item) {
            if ($item->area == 'sidemenu') {
                switch ($item->action) {
                    case 'category':
                        $newcache = $course->query()->compare('catid', $item->value)->get();
                        break;
                    case 'course':
                        $newcache = $course->query()->compare('courseid', $item->value)->get();
                        break;
                    case 'section':
                        $newcache = $course->query()->compare('sectionid', $item->value)->get();
                        break;
                }

                list($activitytotal, $questiontotal, $sequencetotal, $coursetotal) =
                        \community_oer\main_oer::total_elements_of_plugins($item->action, $item->value, $data);

                $event[$item->area] = [$item->action => $item->value];
            }
        }

        $obj = $course->query($newcache)->compare('visible', '1')->groupBy('cid');

        // Get by filter.
        $groups = [];
        foreach ($data as $item) {
            if ($item->area == 'filters') {
                $groups[] = $item->group;
            }
        }

        $groups = array_unique($groups);
        ksort($groups);

        $obj = $course->query($obj->get());
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

            $obj = $course->query($obj->get());
        }

        // Calculate data online.
        $obj = $course->calculate_data_online($obj);

        // Search.

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
                    $flag = $flag2 = false;

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

                    $event['search'][] = $item->value;

                    $obj = $course->query($obj->get());
                }
            }
        }

        // Sorting.
        foreach ($data as $item) {
            if ($item->area == 'sorting') {
                switch ($item->value) {
                    case 1:
                        $obj = $obj->orderNumber('userid', 'asc');
                        $event[$item->area] = 'userid';
                        break;
                    case 2:
                        $obj = $obj->orderNumber('count_used_course', 'desc');
                        $event[$item->area] = 'count_used_course';
                        break;
                    case 3:
                        $obj = $obj->orderNumber('metadata_cshared_at', 'desc');
                        $event[$item->area] = 'metadata_cshared_at';
                        break;
                    case 4:
                        $obj = $obj->orderString('fullname', 'asc');
                        $event[$item->area] = 'fullname';
                        break;
                    default:
                        $obj = $obj->orderNumber('cid', 'asc');
                }
            }
        }

        // Pagination.
        $itemsonpage = get_config('community_oer', 'course_items_on_page');
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
                'template_type' => 'course'
        ];

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'data' => $event,
        );
        \community_oer\event\oer_course_filter::create_event($eventdata)->trigger();

        return json_encode($result);
    }

    public static function get_course_blocks_returns() {
        return new external_value(PARAM_RAW, 'The blocks settings');
    }
}
