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
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once(__DIR__ . '/lib.php');

use core\output\html_writer;

require_login();

$PAGE->set_context(context_system::instance());

$navdraweropen = get_user_preferences('drawer-open-nav') == 'true' ? "true" : "false";
set_user_preference('drawer-open-nav', "false");

$strname = get_string('pluginname', 'community_social');
$PAGE->set_url('/local/community/plugins/social/index.php', array());
$PAGE->set_title($strname);

// Check index template.
$userid = optional_param('id', null, PARAM_INT);
$userid = community_social\social::get_relevant_userid($userid);

$socialenable = optional_param('socialenable', null, PARAM_RAW);
switch ($socialenable) {
    case '1':
        set_user_preferences(array('community_social_enable' => 1));

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($userid);

        // Save Moodle Log.
        $eventdata = ['userid' => $userid];
        \community_social\event\social_enable::create_event($userid, $eventdata)->trigger();
        break;
    case '0':
        set_user_preferences(array('community_social_enable' => 0));

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($userid);

        // Save Moodle Log.
        $eventdata = ['userid' => $userid];
        \community_social\event\social_disable::create_event($userid, $eventdata)->trigger();
        break;
}

$isvisited = get_user_preferences('community_social_enable');

if ($isvisited && \community_social\funcs::has_permission($userid)) {
    $urltogo = new moodle_url('/local/community/plugins/social/teachers.php',
            array_filter(array('id' => $userid), '\community_social\funcs::filter_userid'));
    redirect($urltogo);
}

$PAGE->requires->js_call_amd('community_social/init', 'init');

echo $OUTPUT->header();
echo html_writer::start_div('social');

$data = array(
        'social_return' => false,
        'social_enable' => true,
        'link' => new moodle_url('/local/community/plugins/social/index.php',
                array_filter(array('id' => $userid, 'socialenable' => 1), '\community_social\funcs::filter_userid'))
);

if (!\community_social\funcs::has_permission($userid)) {
    echo $OUTPUT->render_from_template('community_social/no-permission', $data);
} else {
    echo $OUTPUT->render_from_template('community_social/welcomevisited', $data);
}

echo html_writer::end_div();

set_user_preference('drawer-open-nav', $navdraweropen);

echo $OUTPUT->footer();
