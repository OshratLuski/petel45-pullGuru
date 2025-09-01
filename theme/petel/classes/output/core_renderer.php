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
 * Core renderer.
 *
 * @package    theme_petel
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel\output;

use core\output\html_writer;
use custom_menu;
use stdClass;
use moodle_url;
use action_menu;
use context_course;
use pix_icon;
use theme_petel\utility;
use theme_petel\output\core_renderer_toolbox;
use user_picture;
use popup_action;
use action_menu_link_secondary;
use action_menu_filler;
use core_userfeedback;


defined('MOODLE_INTERNAL') || die;

/**
 * The configuration for theme_petel is defined here.
 *
 * @package     theme_petel
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost_union\output\core_renderer {

    /**
     * Create a navbar switch for toggling editing mode.
     *
     * @return string Html containing the edit switch
     */
    public function edit_switch() {
        if ($this->page->user_allowed_editing() && \community_oer\main_oer::check_if_user_admin_or_teacher()) {

            $temp = (object) [
                    'legacyseturl' => (new moodle_url('/editmode.php'))->out(false),
                    'pagecontextid' => $this->page->context->id,
                    'pageurl' => $this->page->url,
                    'sesskey' => sesskey(),
            ];
            if ($this->page->user_is_editing()) {
                $temp->checked = true;
            }
            return $this->render_from_template('core/editswitch', $temp);
        }
    }

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method produces makes use of the YUI3 menunav widget
     * and requires very specific html elements and classes.
     *
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            // UI display proposed language and not current lang.
            $uiswitchtolang = 'Eng';
            if (isset($langs[$currentlang]) && $currentlang === 'en') {
                $tmpstrhebrew = new \lang_string('thislanguage', 'langconfig', null, 'he');
                $uiswitchtolang = mb_substr($tmpstrhebrew, 0, 3);
            } else {
                $uiswitchtolang = 'Eng';
            }
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            // Display "Hebrew" instead of "Hebrew for schools" (hack)
            //$strmenutext = new \lang_string('iso6391', 'langconfig', null, get_parent_language());
            $this->language = $menu->add($uiswitchtolang, new moodle_url('#'), $currentlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }

        return $content;
    }

    /**
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     *
     * @param string $custommenuitems
     * @return string
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /*
 * This renders the navbar.
 * Uses bootstrap compatible html.
 */
// TODO use core navbar function from moodle 4.1 remove after QA
    public function navbar_old(): string {
        global $PAGE;

        foreach ($this->page->navbar->get_items() as $key => $item) {
            if ($key < 3 && $PAGE->course->id > 1 && $PAGE->pagelayout != 'course') {
                $this->page->navbar->get_items()[$key]->is_visible = false;
            } else {
                if ($this->page->navbar->get_items()[$key]->type == \navigation_node::TYPE_CATEGORY && !is_siteadmin()) {
                    $this->page->navbar->get_items()[$key]->is_visible = false;
                } else {
                    $this->page->navbar->get_items()[$key]->is_visible = true;
                }
            }

            $arr = $this->page->navbar->get_items();
            if ($item == end($arr)) {
                $this->page->navbar->get_items()[$key]->action = false;
            }
        }
        return $this->render_from_template('core/navbar', $this->page->navbar);
    }

    public function navbar_petel() {
        global $PAGE;

        foreach ($this->page->navbar->get_items() as $key => $item) {
            if ($key < 3 && $PAGE->course->id > 1 && $PAGE->pagelayout != 'course') {
                $this->page->navbar->get_items()[$key]->is_visible = false;
            } else {
                if ($this->page->navbar->get_items()[$key]->type == \navigation_node::TYPE_CATEGORY && !is_siteadmin()) {
                    $this->page->navbar->get_items()[$key]->is_visible = false;
                } else {
                    $this->page->navbar->get_items()[$key]->is_visible = true;
                }
            }

            $arr = $this->page->navbar->get_items();
            if ($item == end($arr)) {
                $this->page->navbar->get_items()[$key]->action = false;
            }
        }
        return $this->render_from_template('theme_petel/navbar_custom', $this->page->navbar);
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE, $COURSE, $DB, $OUTPUT, $CFG, $USER;
        $PAGE->requires->js_call_amd('theme_petel/quiz_scroll_action');

        $iscoursepage = preg_match("/course-view/", $PAGE->pagetype);

        $header = new stdClass();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($PAGE->layout_options['nonavbar']);
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        // $header->courselinks = $iscoursepage ? $this->course_links() : '';
        $header->coursesearch = $iscoursepage ? $this->course_search() : '';
        $header->iscoursepage = $iscoursepage;
        $header->courseid = $COURSE->id;
        $header->headeractions = $this->page->get_header_actions();

        $header->headeractionsenable = $PAGE->pagelayout == 'course' ? false : true;

        $header->navbar = $this->navbar();

        if (\theme_petel\funcs::getbacktocourse()) {
            $url = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $PAGE->course->id));
            //PTL-5929 go back to previous section in course
            $url .= '#section-'.$PAGE->cm->sectionnum;
            $text = get_string('back_to_course', 'theme_petel');
            $header->backtocourse = html_writer::tag('a', $text, array('class' => 'btn  btn-default back-to-course-btn ml-auto', 'href' => $url));
        }

        $petelutility = new utility();
        $courseimage = $petelutility->get_course_image($COURSE);
        $header->courseimage = $courseimage;

        // TODO: find better deccission
        $header->ismodquiz = false;
        // if(($PAGE->pagetype == 'mod-quiz-attempt' || $PAGE->pagetype ==  'mod-quiz-review') && is_siteadmin()){
        //     // $header->ismodquiz = true;
        //     return '<br>';
        // }

        return $this->render_from_template('core/full_header', $header);
    }

    /**
     * Renders the "breadcrumb" for all pages in boost union.
     *
     * This renderer function is copied and modified from /theme/boost/classes/output/core_renderer.php
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_petel\petelnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    public static function course_links() {
        global $CFG, $COURSE, $OUTPUT, $USER, $DB, $PAGE;
        $html = '';
        $enablereviews = get_config('community_oer', 'enablereviews');
        $reviewrequests = false;
        if ($enablereviews == 1) {
            $reviewrequests = \community_oer\reviews_oer::count_review_oncourse($COURSE->id);
        }
        $btnclass = 'mx-1 btn btn-sm btn-primary quicklinks ';
        if ($COURSE->id > 1) {
            // if ($reviewrequests) {
            //     $title = get_string('give_feedback', 'community_oer');
            //     $text = html_writer::tag('span', $reviewrequests, array('id' => 'reviewOnCourseCounter', 'class' => ''));
            //     $icon = $OUTPUT->pix_icon('t/message', $title, 'moodle', array('class' => 'm-1'));
            //     $attr = array(
            //         'id' => 'reviewOnCourse',
            //         'class' => $btnclass,
            //         'title' => $title,
            //         'role'=>'button',
            //         'data-handler' => 'askForReviewOnCourse',
            //         'data-courseid' => $COURSE->id
            //     );
            //     $html .= html_writer::tag('button', $icon.' '.$text, $attr);
            // }

            // if ($COURSE->showgrades && has_capability('gradereport/grader:view', \context_course::instance($COURSE->id))) {
            //     $url = new moodle_url('/grade/report/index.php', array('id' => $COURSE->id));
            //     $title = get_string('grades', 'core');
            //     $icon = $OUTPUT->pix_icon('t/grades', '', 'moodle', array('class' => 'm-1'));
            //     $html .= html_writer::link($url, $icon, array('class' => $btnclass,
            //         'role'=>'button', 'title' => $title));
            // }
            if (has_capability('community/sharecourse:coursecopy', \context_course::instance($COURSE->id))){

                $flagcourse = false;
                $roles = get_user_roles(\context_course::instance($COURSE->id), $USER->id, false);
                foreach ($roles as $role) {
                    if ($role->shortname === 'editingteacher') {
                        $flagcourse = true;
                    }
                }

                // Check if admin.
                $isadmin = is_siteadmin();

                if($flagcourse || $isadmin) {
                    // Button share course.
                    $url = 'javascript:void(0)';
                    $title = get_string('buttonshare', 'community_sharecourse');
                    $icon = html_writer::tag('i', '', array('class' =>'fa-light fa-arrow-up-right-from-square'));
                    $html .= html_writer::link($url, $icon, array('class' => $btnclass . ' btn-share-course',
                        'role' => 'button', 'title' => $title));
                }
            }

            if (has_capability('community/sharesequence:sequencecopy', \context_course::instance($COURSE->id))){
                $availabletocohort = get_config('community_sharesequence', 'availabletocohort');

                require_once($CFG->dirroot.'/cohort/lib.php');
                $flagsequence = cohort_is_member($availabletocohort, $USER->id) ? true : false;

                // Check if admin.
                $isadmin = false;
                foreach (get_admins() as $admin) {
                    if ($USER->id == $admin->id) {
                        $isadmin = true;
                        break;
                    }
                }

                if($flagsequence || $isadmin) {
                    $url = 'javascript:void(0)';
                    $title = get_string('buttonshare', 'community_sharesequence');
                    $icon = html_writer::tag('i', '', array('class' =>'fa-light fa-share-all'));
                    $html .= html_writer::link($url, $icon, array('class' => $btnclass . ' btn-share-sequence',
                        'role' => 'button', 'title' => $title));

                    $context = \context_course::instance($COURSE->id);
                    $data = ['courseid' => $COURSE->id, 'coursecontext' => $context->id];
                    $PAGE->requires->js_call_amd('community_sharesequence/main', 'init', [$data]);
                }
            }

            // if (has_capability('moodle/course:viewparticipants', \context_course::instance($COURSE->id))) {
            //     $url = new moodle_url('/user/index.php', array('id' => $COURSE->id));
            //     $title = get_string('participants', 'core');
            //     $icon = $OUTPUT->pix_icon('i/cohort', '', 'moodle', array('class' => 'm-1'));
            //     $html .= html_writer::link($url, $icon, array('class' => $btnclass.' participants ',
            //         'role' => 'button', 'title' => $title));
            // }

            // $ccs = \core_competency\api::count_competencies_in_course($COURSE->id);
            // if ($ccs > 0) {
            //     $url = new moodle_url('/admin/tool/lp/coursecompetencies.php', array('courseid' => $COURSE->id));
            //     $title = get_string('competencies', 'core_competency');
            //     $icon = $OUTPUT->pix_icon('t/approve', '', 'moodle', array('class' => 'm-1'));
            //     $html .= html_writer::link($url, $icon, array('class' => $btnclass,
            //         'role'=>'button', 'title' => $title));
            // }

            // $coursebadges = count(badges_get_badges(BADGE_TYPE_COURSE, $COURSE->id, '', '' , 0, 0));
            // if ($coursebadges > 0) {
            //     $url = new moodle_url('/badges/view.php', array('type' => 2, 'id' => $COURSE->id));
            //     $title = get_string('badges', 'core');
            //     $icon = $OUTPUT->pix_icon('i/badge', '', 'moodle', array('class' => 'm-1'));
            //     $html .= html_writer::link($url, $icon, array('class' => $btnclass,
            //         'role'=>'button', 'title' => $title));
            // }

            //Diagnostic
            $pluginmanager = \core_plugin_manager::instance();
            $plugininfo = $pluginmanager->get_plugin_info('local_diagnostic');
            if (isset($plugininfo) && $plugininfo->is_installed_and_upgraded()) {
                require_once($CFG->dirroot . '/cohort/lib.php');
                $cohorts = cohort_get_user_cohorts($USER->id);
                $allowedcohorts = get_config('local_diagnostic', 'cohorts')
                    ? explode(',', get_config('local_diagnostic', 'cohorts'))
                    : [];
                if (array_intersect(array_keys($cohorts), $allowedcohorts)) {
                    $url = 'javascript:void(0)';
                    $colors = \theme_petel\utility::get_instance_colors();
                    $PAGE->requires->js_call_amd('local_diagnostic/main', 'init', ['.popup-trigger', $COURSE->id, [$CFG->wwwroot, $colors]]);
                    $title = get_string('analytics', 'local_diagnostic');
                    $icon = html_writer::tag('i', '', array('class' =>'fa-light fa-chart-network'));
                    $html .= html_writer::tag('a', $icon, array('href' => $url, 'class' => $btnclass . ' popup-trigger',
                        'role'=>'button', 'title' => $title));
                }
            }
        }

        return $html;
    }

    public static function course_search() {
        global $COURSE, $OUTPUT;

        $html = '';
        if ($COURSE->id > 1) {
            $data = new stdClass();
            $data->courseid = $COURSE->id;
            $html .= $OUTPUT->render_from_template('theme_petel/core/course_search', $data);
        }

        return $html;
    }

    /**
     * Get the logo URL.
     *
     * @return string
     */
    public function get_theme_logo() {
        global $OUTPUT;

        $output = $OUTPUT->image_url('header/logo_weizmann', 'theme');

        return $output;
    }

    /**
     * Allow plugins to provide some content to be rendered in the navbar.
     * The plugin must define a PLUGIN_render_navbar_output function that returns
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function navbar_plugin_output() {
        global $CFG, $OUTPUT;

        $output = '';

        // Give subsystems an opportunity to inject extra html content. The callback
        // must always return a string containing valid html.
        foreach (\core_component::get_core_subsystems() as $name => $path) {
            if ($path) {
                $output .= component_callback($name, 'render_navbar_output', [$this], '');
            }
        }

        // Add site administration button.
        if (is_siteadmin()) {
            $url = new moodle_url('/admin/search.php');
            $attr = [
                'class' => 'nav-admin-search-icon  d-flex align-items-center justify-content-center pr-2 pr-md-3',
                'title' => get_string('siteadminquicklink', 'theme_petel'),
                'role' => 'button',
               /*  'data-toggle' => 'tooltip', */
                'data-placement' => 'bottom',
                'title' => get_string('siteadminquicklink', 'theme_petel')
            ];
            $icon = html_writer::tag('i', '', array('class' => 'fa-light fa-wrench nav-link'));
            $output .= html_writer::link($url, $icon, $attr);
        }

        if ($pluginsfunction = get_plugins_with_function('render_navbar_output')) {
           if (isset($pluginsfunction['local'])) {
               $local = $pluginsfunction['local'];
               unset($pluginsfunction['local']);
               array_unshift($pluginsfunction, $local);
           }
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $name => $pluginfunction) {
                    // TODO update logic here !!!
                    if (!in_array($name, $CFG->list_navbar_plugin_output_custom) || $name == 'oer' || $name == 'sharewith') {
                        $output .= $pluginfunction($this);
                    }
                }
            }
        }

        return $output;
    }

    public function get_theme_background() {
        global $PAGE, $OUTPUT;
        $loginbgimage = $PAGE->theme->setting_file_url('backgroundimage', 'backgroundimage');
        if (empty($loginbgimage)) {
            $loginbgimage = $OUTPUT->image_url('login_page_background', 'theme');
        }
        return $loginbgimage;
    }

        /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE, $PAGE, $OUTPUT, $SESSION;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);

        $context->instancename = get_string('instancename_' . $CFG->instancename, 'theme_petel');

        $instancetype = \theme_petel\funcs::get_instance_type();
        $context->instance_type = $instancetype;
        $context->is_petel = $instancetype === 'petel';
        $context->is_wiz = $instancetype === 'wiz';

        $context->policies = theme_petel_get_policies();

        $context->langmenu = $this->custom_menu();

        // Get logo from admin.
        $petellogo = '';
        $logo = get_config('core_admin', 'logo');
        if (!empty($logo)) {

            $maxwidth = 600;
            $maxheight = 600;

            $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

            $petellogo = moodle_url::make_pluginfile_url(\context_system::instance()->id, 'core_admin', 'logo', $filepath,
                theme_get_revision(), $logo);
        }
        $context->petellogo = $petellogo;

        if($CFG->instancename!= 'computerscience') {
            $instancename = get_string('instancename_' . $CFG->instancename, 'theme_petel');
            $context->ministry_statement_title = get_string('ministry_statement_title', 'theme_petel', $instancename);
            $context->ministry_statement_text = get_string('ministry_statement_text', 'theme_petel', $instancename);
        }

        if (isset($CFG->recaptchav2enable) && $CFG->recaptchav2enable && isset($CFG->recaptchav2sitekey) && $SESSION->failedloginattempts >= $CFG->failedlogins_without_recaptcha) {
            $context->sitekeycaptchav2 = $CFG->recaptchav2sitekey;
        }

        if (\theme_petel\funcs::get_instance_type() === 'petel') {
            $context->login_icon = $this->image_url('owl_clean', 'theme_petel')->out();
        } else {
            $context->login_icon = $this->image_url('user', 'theme_petel')->out();
        }

        return $this->render_from_template('core/loginform', $context);
    }

    public function region_main_settings_menu() {

        return null;
    }

    public function standard_head_html() {
        global $PAGE;

        // Add smartselect js and css call.
        $PAGE->requires->js_amd_inline('require(["jquery", "core_form/select2"], function($) {
            $(".smartselect2 .custom-select").select2({dropdownAutoWidth: true})
        });');
        $PAGE->requires->css('/lib/form/css/select2.min.css');

        return parent::standard_head_html();
    }

    protected function render_user_picture(user_picture $userpicture) {
        global $USER, $PAGE;

        $climode = defined('CLI_SCRIPT') && CLI_SCRIPT;
        if ($climode) {

            $user = $userpicture->user;
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $this->page->context);

            if ($userpicture->alttext) {
                if (!empty($user->imagealt)) {
                    $alt = $user->imagealt;
                } else {
                    $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
                }
            } else {
                $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
            }
            if (empty($userpicture->size)) {
                $size = 35;
            } else if ($userpicture->size === true or $userpicture->size == 1) {
                $size = 100;
            } else {
                $size = $userpicture->size;
            }

            $class = $userpicture->class;

            if ($user->picture == 0) {
                $class .= ' defaultuserpic';
            }

            $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src' => $src, 'class' => $class, 'width' => $size, 'height' => $size);
        if (!$userpicture->visibletoscreenreaders) {
            $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
            $attributes['aria-hidden'] = 'true';
        }

            if (!empty($alt)) {
                $attributes['alt'] = $alt;
                $attributes['title'] = $alt;
            }

            // Get the image html output first.
            $output = html_writer::empty_tag('img', $attributes);

            // Show fullname together with the picture when desired.
            if ($userpicture->includefullname) {
                $output .= fullname($userpicture->user, $canviewfullnames);
            }

            // Then wrap it in link if needed.
            if (!$userpicture->link) {
                return $output;
            }

            if (empty($userpicture->courseid)) {
                $courseid = $this->page->course->id;
            } else {
                $courseid = $userpicture->courseid;
            }

            if ($courseid == SITEID) {
                $url = new moodle_url('/user/profile.php', array('id' => $user->id));
            } else {
                $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
            }

            $attributes = array('href' => $url, 'class' => 'd-inline-block aabtn');
            if (!$userpicture->visibletoscreenreaders) {
                $attributes['tabindex'] = '-1';
                $attributes['aria-hidden'] = 'true';
            }

            if ($userpicture->popup) {
                $id = html_writer::random_id('userpicture');
                $attributes['id'] = $id;
                $this->add_action_handler(new popup_action('click', $url), $id);
            }

            return html_writer::tag('a', $output, $attributes);

        } else {

            $user = $userpicture->user;
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $this->page->context);

            if ($userpicture->alttext) {
                if (!empty($user->imagealt)) {
                    $alt = $user->imagealt;
                } else {
                    $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
                }
            } else {
                $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
            }

            if (empty($userpicture->size)) {
                $size = 35;
            } else if ($userpicture->size === true or $userpicture->size == 1) {
                $size = 100;
            } else {
                $size = $userpicture->size;
            }

            $class = $userpicture->class;

            $src = $userpicture->get_url($this->page, $this);

            $attributes = array('src' => $src, 'class' => $class, 'width' => $size, 'height' => $size);
            if (!$userpicture->visibletoscreenreaders) {
                $alt = $alt = get_string('pictureof', '', fullname($user, $canviewfullnames));
                $attributes['aria-hidden'] = 'true';
            }

            if (!empty($alt)) {
                $attributes['alt'] = $alt;
                $attributes['title'] = $alt;
            }

            // Get the image html output first.
            if ($user->picture == 0) {

                // $icon = new pix_icon('i/groupv', 'alt', 'moodle', ['class' => ' defaultuserpic']);
                // $output = $this->render($icon);
                $icon =  html_writer::tag('i', '', array('class' => 'fa-light fa-circle-user defaultuserpic '));
                $output = $icon;

            }else {
                $output = html_writer::empty_tag('img', $attributes);
            }

            // Show fullname together with the picture when desired.
            if ($userpicture->includefullname) {
                $output .= fullname($userpicture->user, $canviewfullnames);
            }

            // Then wrap it in link if needed.
            if (!$userpicture->link) {
                return $output;
            }

            if (empty($userpicture->courseid)) {
                $courseid = $this->page->course->id;
            } else {
                $courseid = $userpicture->courseid;
            }

            if ($courseid == SITEID) {
                $url = new moodle_url('/user/profile.php', array('id' => $user->id));
            } else {
                $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
            }

            $attributes = array('href' => $url, 'class' => 'd-inline-flex align-items-center aabtn');
            if (!$userpicture->visibletoscreenreaders) {
                $attributes['tabindex'] = '-1';
                $attributes['aria-hidden'] = 'true';
            }

            if ($userpicture->popup) {
                $id = html_writer::random_id('userpicture');
                $attributes['id'] = $id;
                $this->add_action_handler(new popup_action('click', $url), $id);
            }

            if ((user_has_role_assignment($USER->id, 3 /* editingteacher */, $PAGE->context->id)
                || user_has_role_assignment($USER->id, 1 /* manager */, $PAGE->context->id)
                || array_key_exists($USER->id, get_admins())
                || has_capability('report/roster:resetpassword', $PAGE->context))
                && ($userpicture->link && $size >= 35)
            ) {
                $actions = $this->user_action_menu_actions($user->id, $courseid, $attributes);
            } else {
                return html_writer::tag('a', $output, $attributes);
            }

            $returnstr = '';

            $returnstr .= html_writer::span(
                $output,
                $attributes['class'],
                ['title' => fullname($userpicture->user, $canviewfullnames), 'data-toggle' => 'tooltip']
            );

            $divider = new action_menu_filler();

            $am = new action_menu();
            $am->set_menu_trigger(
                $returnstr
            );
            $am->set_action_label(get_string('usermenu').' '.fullname($user, $canviewfullnames));

            // Deprecated.
            //$am->set_alignment(action_menu::TR, action_menu::BR);
            $am->set_menu_left();

            $am->set_nowrap_on_items();
            $navitemcount = count($actions);
            $idx = 0;
            foreach ($actions as $key => $value) {
                // Process this as a link item.
                $pix = null;
                if (isset($value->pix) && !empty($value->pix)) {
                    $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                    $value->title = html_writer::img(
                        $value->imgsrc,
                        $value->title,
                        array('class' => 'iconsmall')
                    ) . $value->title;
                }

                $al = new action_menu_link_secondary(
                    new moodle_url($key),
                    $pix,
                    $value,
                    array('class' => 'icon')
                );
                if (!empty($value->titleidentifier)) {
                    $al->attributes['data-title'] = $value->titleidentifier;
                }
                $am->add($al);

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }

            if (right_to_left() == 'rtl') {
                $html = str_replace('dropdown-menu-left', 'dropdown-menu-right', $this->render($am));
            } else {
                $html = $this->render($am);
            }

            return html_writer::div(
                $html
            );
        }
    }

    private function user_action_menu_actions($userid, $courseid, $attributes) {
        global $USER, $DB, $COURSE;

        $edit = '';
        $actions = array();

        // Action URLs
        if ($COURSE->id != SITEID) {
            $courseid = $COURSE->id;
        }

        // View user's profile
        //if (is_siteadmin()) {
        // Allow teachers and admin to view user profile.
            if ($courseid == SITEID) {
                $url = new moodle_url('/user/profile.php', array('id' => $userid));
            } else {
                $url = new moodle_url('/user/view.php', array('id' => $userid, 'course' => $courseid));
            }
            $actions[$url->out(false)] = get_string('user_viewprofile', 'theme_petel');
        //}

        $context = context_course::instance($COURSE->id);
        $viewresetpass = false;
        $editroles = array('teacher', 'editingteacher', 'manager');

        if (!empty(get_user_roles($context, $userid)) && !empty(get_user_roles($context, $USER->id))) {
            $role = array_values(get_user_roles($context, $userid))[0];
            $myrole = array_values(get_user_roles($context, $USER->id))[0];

            if ($myrole->shortname === 'student') {
                $viewresetpass = false;
            }
            if (in_array($myrole->shortname, $editroles)) {
                if ($role->shortname === 'student') {
                    $viewresetpass = true;
                } else {
                    $viewresetpass = false;
                }
            }
        }

        // Reset user's password to original password (stored in user.url profile field)
        if ($USER->id === $userid || has_capability('report/roster:resetpassword', $context)) {
            $viewresetpass = true;
        }

        if (is_siteadmin() || $viewresetpass) {
            $resetpasswordurl = new moodle_url(
                '/report/roster/resetpassword.php',
                array('userid' => $userid, 'courseid' => $COURSE->id, 'sesskey' => sesskey(), 'layout' => 'embedded')
            );
            $actions[$resetpasswordurl->out(false)] = get_string('resetpassword', 'theme_petel');
        }

        if (is_siteadmin() || $viewresetpass) {
            $user = $DB->get_record('user', ['id' => $userid]);
            if ($user->phone1 !== '') {
                $mobile = '972' . ltrim($user->phone1, '0');
                $mobile = str_replace('-', '', $mobile);
                $sendwhatsappurl = new moodle_url('https://wa.me/' . $mobile);
                $actions[$sendwhatsappurl->out(false)] = get_string('sendwhatsapp', 'theme_petel');
            }
        }

        // Login as ...  התחבר כתלמיד זה
        $coursecontext = context_course::instance($courseid);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $coursecontext)) {
            $url = new moodle_url('/course/loginas.php', array('id' => $courseid, 'user' => $userid, 'sesskey' => sesskey()));
            $actions[$url->out(false)] = get_string('user_loginas', 'theme_petel');
        }

        // View user's complete report  דוח קורס מלא
        if (is_siteadmin() || $viewresetpass) {
            $url = new moodle_url(
                '/report/outline/user.php',
                array('id' => $userid, 'course' => $courseid, 'mode' => 'complete')
            );
            $actions[$url->out(false)] = get_string('user_completereport', 'theme_petel');
        }

        // View user's outline report   דוח צפיה בקורס
        if (is_siteadmin() || $viewresetpass) {
            $url = new moodle_url(
                '/report/outline/user.php',
                array('id' => $userid, 'course' => $courseid, 'mode' => 'outline')
            );
            $actions[$url->out(false)] = get_string('user_outlinereport', 'theme_petel');
        }

        // Edit user's profile
        // $url = new moodle_url('/user/editadvanced.php', array('id' => $userid, 'course'=>$courseid));
        // $actions[$url->out(false)] = get_string('user_editprofile','theme_petel');

        // Completion enabled in course? Display user's link to completion report.
        // $coursecompletion = $DB->get_field('course', 'enablecompletion', array('id' => $courseid));
        // if (!empty($CFG->enablecompletion) AND $coursecompletion) {
        //     $url = new moodle_url('/blocks/completionstatus/details.php', array('user' => $userid, 'course'=>$courseid));
        //     $actions[$url->out(false)] = get_string('user_coursecompletion','theme_petel');
        // }

        // All user's mdl_log HITS   ניטור פעילות
        if (is_siteadmin()) {
            $url = new moodle_url('/report/log/user.php', array('id' => $userid, 'course' => $courseid, 'mode' => 'all'));
            $actions[$url->out(false)] = get_string('user_courselogs', 'theme_petel');
        }

        // User's grades in course ID
        // $url = new moodle_url('/grade/report/user/index.php', array('userid' => $userid, 'id'=>$courseid));
        // $actions[$url->out(false)] = get_string('user_coursegrades','theme_petel');

        // Send private message  שליחת מסר
        if ($USER->id != $userid) {
            $url = new moodle_url('/message/index.php', array('id' => $userid));
            $actions[$url->out(false)] = get_string('user_sendmessage', 'theme_petel');
        }

        return $actions;
    }

    public function activity_navigation() {
        // PTL-9374 Disable bottom of page quiz/activity navigation.
        return '';
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        $options = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // Add module URL (as key) and name (as value) to the activity list array.

            $options[get_section_name(get_fast_modinfo($course->id)->get_section_info($module->sectionnum)->course, get_fast_modinfo($course->id)->get_section_info($module->sectionnum))][$linkurl->out(false)] = $modname;
        }

        $activitylist = [$options];

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }

    /**
     * The standard tags (typically performance information and validation links,
     * if we are in developer debug mode) that should be output in the footer area
     * of the page. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_footer_html() {
        global $CFG, $SCRIPT, $PAGE;
        $output = '';
        if (during_initial_install()) {
            // Debugging info can not work before install is finished,
            // in any case we do not want any links during installation!
            return $output;
        }

        // Give plugins an opportunity to add any footer elements.
        // The callback must always return a string containing valid html footer content.
        $pluginswithfunction = get_plugins_with_function('standard_footer_html', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        $policies = theme_petel_get_policies();
        foreach ($policies as $policie) {
            $output .= html_writer::div(html_writer::link($policie->url, $policie->name,
                    ['target' => "_blank", 'rel' => 'nofollow']), 'tool_dataprivacy');
        }

        if (core_userfeedback::can_give_feedback()) {
            $output .= html_writer::div(
                    $this->render_from_template('core/userfeedback_footer_link',
                            ['url' => core_userfeedback::make_link()->out(false)])
            );
        }

        // This function is normally called from a layout.php file in {@link core_renderer::header()}
        // but some of the content won't be known until later, so we return a placeholder
        // for now. This will be replaced with the real content in {@link core_renderer::footer()}.
        $output .= $this->unique_performance_info_token;
        if ($this->page->devicetypeinuse == 'legacy') {
            // The legacy theme is in use print the notification
            $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class' => 'legacythemeinuse'));
        }

        // Get links to switch device types (only shown for users not on a default device)
        $output .= $this->theme_switch_links();

        if (!empty($CFG->debugpageinfo)) {
            $output .= '<div class="performanceinfo pageinfo">' . get_string('pageinfodebugsummary', 'core_admin',
                            $this->page->debug_summary()) . '</div>';
        }
        if (debugging(null, DEBUG_DEVELOPER) and
                has_capability('moodle/site:config', \context_system::instance())) {  // Only in developer mode
            // Add link to profiling report if necessary
            if (function_exists('profiling_is_running') && profiling_is_running()) {
                $txt = get_string('profiledscript', 'admin');
                $title = get_string('profiledscriptview', 'admin');
                $url = $CFG->wwwroot . '/admin/tool/profiling/index.php?script=' . urlencode($SCRIPT);
                $link = '<a title="' . $title . '" href="' . $url . '">' . $txt . '</a>';
                $output .= '<div class="profilingfooter">' . $link . '</div>';
            }
            $purgeurl = new moodle_url('/admin/purgecaches.php', array('confirm' => 1,
                    'sesskey' => sesskey(), 'returnurl' => $this->page->url->out_as_local_url(false)));
            $output .= '<div class="purgecaches">' .
                    html_writer::link($purgeurl, get_string('purgecaches', 'admin')) . '</div>';
        }
        if (!empty($CFG->debugvalidators)) {
            // NOTE: this is not a nice hack, $this->page->url is not always accurate and
            // $FULLME neither, it is not a bug if it fails. --skodak.
            $output .= '<div class="validators"><ul class="list-unstyled ml-1">
              <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=' . urlencode(qualified_me()) . '">Validate HTML</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=' . urlencode(qualified_me()) . '">Section 508 Check</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=' .
                    urlencode(qualified_me()) . '">WCAG 1 (2,3) Check</a></li>
            </ul></div>';
        }

        // Revert all links to new format.
        $linkarray = [];
        if(preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>/i', $output, $matches, PREG_SET_ORDER)){
            foreach ($matches as $match) {
                array_push($linkarray, [$match[1], $match[2]]);
            }
        }

        $arr = [];
        foreach ($linkarray as $item) {
            if (isset($item[0]) && isset($item[1])) {
                $arr[] = '<a href="'.$item[0].'" class="footer-link text-white mb-3">'.$item[1].'</a>';
            }
        }

        $output = implode('<span class="footer-link-divider mb-3"></span>', $arr);

        return $output;
    }

    public function footer_ip_html() {

        $serverip = $_SERVER['SERVER_ADDR'];
        // Split the IP address by dots
        $ipparts = explode('.', $serverip);
        // Get the last number
        $lastpart = end($ipparts);

        $output = "<!-- ASG WEB ID = $lastpart -->";

        return $output;
    }
}
