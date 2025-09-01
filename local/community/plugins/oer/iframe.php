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
 * Local plugin "OER catalog" - Library
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

if (!\community_oer\main_oer::check_if_user_admin_or_teacher()) {
    throw new \moodle_exception('No permission');
}

$tabs = \community_oer\main_oer::get_tabs_by_user();
if (empty($tabs)) {
    throw new \moodle_exception('No permission');
}

$navdraweropen = get_user_preferences('drawer-open-nav') == 'true' ? "true" : "false";
set_user_preference('drawer-open-nav', "false");

$PAGE->set_url('/local/community/plugins/oer/index.php', []);

$strname = get_string('pluginname', 'community_oer');
$PAGE->navbar->add($strname);
$PAGE->set_title($strname);

echo $OUTPUT->header();

// Default tab.
$plugin = optional_param('plugin', '', PARAM_RAW);
if (in_array($plugin, $tabs)) {
    $default = $plugin;
} else {
    $default = $tabs[0];
}

// Set settings of iframe.
$default = 'activity';
$data = \community_oer\main_oer::get_main_menu($default);
$data['iframe'] = true;

echo $OUTPUT->render_from_template('community_oer/main', $data);

$categoryid = optional_param('categoryid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$sectionid = optional_param('sectionid', 0, PARAM_INT);

if ($categoryid || $courseid || $sectionid) {

    if ($categoryid) {
        $type = 'category';
        $elementid = $categoryid;
    }

    if ($courseid) {
        $type = 'course';
        $elementid = $courseid;
    }

    if ($sectionid) {
        $type = 'section';
        $elementid = $sectionid;
    }
} else {
    $type = 'category';

    $activity = new \community_oer\activity_oer;
    $structure = $activity->structure_activity_catalog();

    $elementid = $structure[0]['cat_id'];
}

$defaultfilters = [];
$defaultfilters['activity'] = [];
$defaultfilters['course'] = [];
$defaultfilters['question'] = [];
$defaultfilters['sequence'] = [];

$defaultlangactivity = get_config('community_oer', 'default_lang_activity');
if (!empty($defaultlangactivity) && $defaultlangactivity != 'empty') {
    $defaultfilters['activity'][] = ['filter' => 'metadata_language', 'value' => $defaultlangactivity];
}

$PAGE->requires->js_call_amd('community_oer/main', 'init', [$default, $type, intval($elementid), $defaultfilters]);

$PAGE->requires->js_amd_inline("
            require(['jquery'], function($) {
                $('.navbar-petel').hide();
                $('#page-footer').hide();
                $('#page').css('margin-top', '0px');                              
            });
        ");

echo $OUTPUT->footer();
