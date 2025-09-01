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
 * @package    block_pdc_external
 * @category   external
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/blocks/pdc/lib.php');

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class block_pdc_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function render_courses_block_parameters() {
        return new external_function_parameters(
                array(
                        'sort' => new external_value(PARAM_RAW, 'Sort'),
                )
        );
    }

    /**
     * Send private messages from the current USER to other users
     *
     * @param array $messages An array of message to send.
     * @return array
     * @since Moodle 2.2
     */
    public static function render_courses_block($sort) {
        global $OUTPUT, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $render = block_pdc_render_courses_content($sort);

        $data = new \StdClass();
        $data->content = $render;
        $data->content_empty = !empty($render) ? false : true;
        $data->pix_no_courses = $OUTPUT->image_url('courses', 'block_pdc');
        $content = $OUTPUT->render_from_template('block_pdc/content-courses', $data);

        $result = array();
        $result['content'] = $content;
        $result['status'] = true;

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function render_courses_block_returns() {
        return new external_single_structure([
                'content' => new external_value(PARAM_RAW, 'Content html', VALUE_OPTIONAL),
                'status' => new external_value(PARAM_BOOL, 'status: true if success')
        ]);
    }

}
