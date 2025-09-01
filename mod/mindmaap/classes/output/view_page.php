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
 * Class containing data for view page
 *
 * @package    mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_mindmaap\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use mod_mindmaap\api;

/**
 * Class containing data for view page
 *
 * @copyright   2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_page implements renderable, templatable {

    /**
     * Class containing data for view page
     */


    /** @var \stdClass module instance. */
    private $moduleinstance;

    /** @var \stdClass course module. */
    private $cm;

    /**
     * Construct this renderable.
     *
     * @param \stdClass activity id
     */
    public function __construct($moduleinstance, $cm) {
        $this->moduleinstance = $moduleinstance;
        $this->cm = $cm;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $OUTPUT;

        $data = new \stdClass();

        $config = get_config('mod_mindmaap');

        $mindmaap = new api($config->token, $config->url);

        //Guest hack
        if (empty($USER->lastname)) {
            $USER->lastname = $USER->firstname;
        }

        if (isguestuser()) {
            $USER->lastname = $USER->firstname;
            $USER->email = "guest".$USER->id."@lms.com";
        }

        // Create mindmaap.
        $user = $mindmaap->registeruser(
            $USER->email,
            $USER->firstname,
            $USER->lastname,
            [$this->cm->id]
        );

        $data->intro = '';

        $url = $user['url'] . "&lang=" . current_language();
        $sessionurl = $mindmaap->getsessionurl($user['url_param'], $user['mindmap']['token']);

        $type = $this->moduleinstance->type;

        $data->$type = true;
        $data->url = $url;
        $data->sessionurl = $sessionurl;
        $data->name = format_string($this->moduleinstance->name);

        return $data;
    }
}
