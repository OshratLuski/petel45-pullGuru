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
 * External course API
 *
 * @package    block_oer_items_external
 * @category   external
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class block_oer_items_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function render_activity_block_parameters() {
        return new external_function_parameters(
                array(
                        'courseids' => new external_value(PARAM_TEXT, 'Course ids'),
                        'language' => new external_value(PARAM_RAW, 'Language'),
                )
        );
    }

    /**
     * Send private messages from the current USER to other users
     *
     * @param array $messages An array of message to send.
     * @return string
     * @since Moodle 2.2
     */
    public static function render_activity_block($courseids, $language) {
        global $OUTPUT, $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $courses = array();
        if (!empty($courseids)) {
            $courses = json_decode($courseids);
        }

        $DB->delete_records('block_oer_items', array('userid' => $USER->id));

        foreach ($courses as $courseid) {
            $DB->insert_record('block_oer_items', array(
                    'userid' => $USER->id,
                    'courseid' => $courseid,
                    'timemodified' => time()
            ));
        }

        $data = self::items_render_activities_content($language, $courses);

        return json_encode([
                'data' => $data,
                'pix_no_courses' => $OUTPUT->image_url('courses', 'block_oer_items')->out()
        ]);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function render_activity_block_returns() {
        return new external_value(PARAM_RAW, 'The blocks settings');
    }

    private static function items_render_activities_content($language, $courseids = []) {
        global $DB;

        $html = '';

        // Check courseids and language.
        if (empty($courseids) || empty($language)) {
            return $html;
        }

        $courseids = array_unique($courseids);

        // All courses.
        if (isset($courseids[0]) && $courseids[0] == 0) {
            $menumaagar = \community_oer\main_oer::structure_main_catalog();
            $courseids = [];
            foreach ($menumaagar as $obj) {
                foreach ($obj['courses'] as $course) {
                    $courseids[] = $course->id;
                }
            }
        }

        $activity = new \community_oer\activity_oer();

        $obj = $activity->query();
        foreach ($courseids as $key => $courseid) {

            if ($key == 0) {
                $obj = $obj->compare('courseid', $courseid);
            } else {
                $obj = $obj->orCompare('courseid', $courseid);
            }

            // Language.
            if ($language != 'all') {
                $obj = $obj->like('metadata_language', $language);
            }
        }

        $obj = $obj->compare('visible', '1')->groupBy('cmid')->groupBy('mod_name')->orderString('cm_created', 'desc');

        // Check version date.
        $range = get_config('block_oer_items', 'range');

        if (!empty($range) && $range > 0) {
            $daterange = date("YmdHi", strtotime("-".$range." month"));
            $arr = $obj->get();
            foreach ($arr as $unique => $item) {
                if ($item->metadata_version <= $daterange) {
                    unset($arr[$unique]);
                }
            }

            $obj = $activity->query($arr);
        }

        // Limit 10 items.
        $obj = $obj->limit(1, 10);

        $data = [];
        foreach ($courseids as $courseid) {
            $course = $DB->get_record('course', array('id' => $courseid));
            $group = $obj->compare('courseid', $course->id);
            $group = $activity->calculate_data_online($group, 'oercatalog');

            if (!empty($course) && count($group->get()) > 0) {
                $tmp = new \StdClass();
                $tmp->title = '<br><h2>' . $course->fullname . '</h2>';
                $tmp->blocks = array_values($group->get());
                $data[] = $tmp;
            }
        }

        return $data;
    }
}
