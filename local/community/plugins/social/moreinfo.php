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
// Replace newmodule with the name of your module and remove this line.
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



echo $OUTPUT->header();
echo html_writer::start_div('social');

$data = array(
        'social_return' => true,
        'social_enable' => false,
        'link' => new moodle_url('/local/community/plugins/social/teachers.php', [])
);

echo $OUTPUT->render_from_template('community_social/welcomevisited', $data);

echo html_writer::end_div();

set_user_preference('drawer-open-nav', $navdraweropen);

echo $OUTPUT->footer();
