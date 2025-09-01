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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    community_social
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

use core\output\html_writer;

require_login();

$PAGE->set_context(context_system::instance());

$navdraweropen = get_user_preferences('drawer-open-nav') == 'true' ? "true" : "false";
set_user_preference('drawer-open-nav', "false");

$strname = get_string('pluginname', 'community_social');
$PAGE->set_url('/local/community/plugins/social/profile.php', array('id' => $USER->id));
$PAGE->set_title($strname);

// Check if user active.
$isvisited = get_user_preferences('community_social_enable');
$userid = optional_param('id', null, PARAM_INT);

if (!$isvisited) {
    $urltogo = new moodle_url('/local/community/plugins/social/index.php',
            array_filter(array('id' => $userid), '\community_social\funcs::filter_userid'));
    redirect($urltogo);
}

$data = array(
        'social_enable' => new moodle_url('/local/community/plugins/social/index.php',
                array_filter(array('id' => $userid, 'socialenable' => 1), '\community_social\funcs::filter_userid')),
        'social_disable' => new moodle_url('/local/community/plugins/social/index.php',
                array_filter(array('id' => $userid, 'socialenable' => 0), '\community_social\funcs::filter_userid'))
);

$userid = community_social\social::get_relevant_userid($userid);

// Save Moodle Log.
$eventdata = [
        'userid' => $USER->id,
        'targetuserid' => $userid,
];
\community_social\event\social_profile_view::create_event($USER->id, $eventdata)->trigger();

$PAGE->requires->js_call_amd('community_social/init', 'init', [$userid, $USER->id]);

echo $OUTPUT->header();
echo html_writer::start_div('social');

if (!$DB->get_record('user', array('id' => $userid))) {
    echo $OUTPUT->render_from_template('community_social/wrong-user', $data);
} else if (!\community_social\funcs::has_permission($userid)) {
    echo $OUTPUT->render_from_template('community_social/no-permission', $data);
} else {

    $social = new \community_social\social();
    $data = $social->getSingleDataUser($userid);

    if (!empty($data)) {
        // Prepare oer activities block.
        foreach ($data->oercatalog_activities as $item) {
            $data->oercatalog_activities['blocks'][] = $item;

            // Update counter community_oer_wht_new.
            \community_oer\activity_oer::funcs()::whats_new_update_counter($item->cmid);
        }

        $data->oercatalog_activities_enable = !empty($data->oercatalog_activities) ? true : false;

        // Prepare oer courses block.
        $oercourses = $data->oercatalog_courses;
        if (!empty($oercourses)) {
            $data->oercatalog_courses['blocks'] = $oercourses;
            $data->count_oercatalog_courses = count($oercourses);
            $data->oercatalog_courses_enable = true;
        }
    }

    $data->card_empty = false;
    if ($data->colleagues_count == 0 && $data->followers_count == 0 && $data->shared_items_count == 0) {
        $data->card_empty = true;
    }

    echo $OUTPUT->render_from_template('community_social/profile', $data);
}

echo html_writer::end_div();

echo $OUTPUT->footer();

set_user_preference('drawer-open-nav', $navdraweropen);
