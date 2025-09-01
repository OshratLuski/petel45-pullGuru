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
 * Plugin administration pages are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Standard callback to add menu item to course-level navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param context $context The context of the course
 */
/*
function local_redmine_extend_navigation_course(navigation_node $navigation, stdClass $course, context $context) {
    global $CFG;

    $CFG->customusermenuitems = $CFG->customusermenuitems.'myissues,local_redmine|/local/redmine/search_issues.php|t/preferences';
}
*/

/**
 * This function extends the course navigation with the report items
 *
 * @param stdClass $returnobject The navigation node to extend
 * @param stdClass $user
 * @param stdClass $context
 * @param stdClass $course The course to object for the report
 */
// Disabled, after we added full support menu functionality to main toolbar.
/*
function local_redmine_extend_navigation_menuuser($returnobject, $user, $context, $course) {
    global $CFG, $DB, $USER;

    // Only site (cohort) teachers can see this link.
    if (!empty($CFG->siteteachers)) {
        $teachers_cohort = $CFG->siteteachers;//'teachers';
        $sql = "SELECT *
            FROM {cohort_members} cm
            JOIN {cohort} c ON c.id = cm.cohortid
            WHERE c.idnumber=? AND cm.userid=?";
        $isteacher = $DB->get_records_sql($sql, [$teachers_cohort, $USER->id]);

        if ($isteacher) {
            $usermenuitem = new stdClass();
            $usermenuitem->itemtype = 'link';
            $usermenuitem->url = new moodle_url('/local/redmine/search_issues.php', array(
                //'fullusername' => 'שם של מורה',
            ));
            $usermenuitem->pix = "t/preferences";
            $usermenuitem->title = get_string('myissues', 'local_redmine');
            $usermenuitem->titleidentifier = 'myissues,local_redmine';
            return $usermenuitem;
        }
    }
}
*/

function local_redmine_render_navbar_output() {
    global $PAGE, $DB, $USER, $COURSE, $CFG;

    $output = '';

    if(isloggedin()) {
        $access  = 0;

        if (get_config('local_redmine', 'redminestatus') && \local_petel\funcs::user_admin_or_teacher()) {
            $access = 2;
        } else if ($PAGE->context->contextlevel == CONTEXT_COURSE || $PAGE->context->contextlevel == CONTEXT_MODULE) {

            $themenames = \core_component::get_plugin_list('theme');
            if (isset($themenames['petel'])) {
                if (\theme_petel\funcs::add_role_class_to_body() == 'role-student') {
                    $access = 3;
                }
            }
        }

        switch ($access) {
            case '1': // Admin.
            case '2': // Teacher or editingteacher.
                $title = get_string('support', 'local_redmine');

                $output = '
                    <div class="dropdown d-flex align-items-center pr-2 pr-md-3" title="'. $title .'" aria-label="'. $title .'">
                        <a class="support-btn nav-link dropdown-toggle" href="#" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-light fa-headset"></i>
                        <div id="issues_counter_user" style="display: none;" class="count-container " data-region="count-container">0</div> 
                        </a>
                        
                        <div class="dropdown-menu support-dropdown-menu" aria-labelledby="dropdownMenuButton">                                                        
                            <a class="dropdown-item" href="javascript:void(0)" id="support-btn"> ' . get_string('support_menu_newappeal', 'local_redmine') . ' </a>
                            
                            <a class="dropdown-item " id="issues-btn" href=" '. new moodle_url('/local/redmine/index.php') . ' "> <span class="d-block">'
                        . get_string('support_menu_myappeals', 'local_redmine') .'</span>
                                <div class="attention-info-block align-items-center" id="issues_counter_user_block" style="display: none;">
                                    <p class="attention-info-text mb-0 mr-1">
                                    <span id="issues_counter_user_span"></span>
                                    '. get_string('support_menu_activeissues', 'local_redmine') .'
                                    </p>
                                    <i class="far fa-alarm-exclamation ml-auto"></i>
                                </div>
                            </a>
                            
                            
                            <a class="dropdown-item " target="_blank" href="https://stwww1.weizmann.ac.il/petel/instructions" >' . get_string('support_menu_petelguides', 'local_redmine') . ' </a>
                        </div>
                    </div>
                ';
                break;
            case '3': // Student on course.
                $title = get_string('support', 'local_redmine');
                $output .= html_writer::start_tag('div',array('class' => 'd-flex align-items-center pr-3'));
                $output .= html_writer::start_tag('a', array(
                        'href'  => '#',
                        'class' => 'support-btn-student fa-light fa-question nav-link',
                        'title' => $title,
                        'data-courseid' => $COURSE->id,
                        'id'    => 'support-btn-student',
                        'role'  => 'button'));
                $output .= html_writer::end_tag('a');
                $output .= html_writer::end_tag('div');

                break;
        }

        $PAGE->requires->js_call_amd('local_redmine/support', 'init', []);
    }

    return $output;
}

/**
 * Serves the files from the hvp file areas
 *
 * @package mod_hvp
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the newmodule's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 * @return true|false Success
 */
function local_redmine_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {

    $filename = array_pop($args);
    $itemid = array_shift($args);
    $filepath = '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_redmine', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // No such file.
    }

    if ($file->is_valid_image()) {
        $forcedownload = true;
    }

    // Totara: use allowxss option to prevent application/x-javascript mimetype
    // from being converted to application/x-forcedownload.
    $options['allowxss'] = '1';

    send_stored_file($file, 86400, 0, $forcedownload, $options);

    return true;
}