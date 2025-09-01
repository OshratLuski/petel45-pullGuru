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

namespace format_flexsections\output\courseformat;

use core_courseformat\external\get_state;
use course_modinfo;
use stdClass;
use html_writer;

require_once $CFG->dirroot . "/course/format/flexsections/locallib.php";

/**
 * Render a course content.
 *
 * @package   format_flexsections
 * @copyright 2022 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends \core_courseformat\output\local\content {

    /** @var \format_flexsections the course format class */
    protected $format;

    /** @var bool Flexsections format has add section. */
    protected $hasaddsection = true;

    /**
     * Template name for this exporter
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_flexsections/local/content';
    }

    /**
     * Override the parent export_for_template, for Moodle 4.4 only
     *
     * This function is almost identical to the
     * \core_courseformat\output\local\content::export_for_template() from Moodle 4.3
     * except for the $data->sectionreturn being null instead of 0 (otherwise JS does not work)
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template_override(\renderer_base $output): stdClass {
        global $PAGE;

        $format = $this->format;

        // Most formats uses section 0 as a separate section so we remove from the list.
        $sections = $this->export_sections($output);
        $initialsection = '';
        if (!empty($sections)) {
            $initialsection = array_shift($sections);
        }

        $data = (object)[
            'title' => $format->page_title(), // This method should be in the course_format class.
            'initialsection' => $initialsection,
            'sections' => $sections,
            'format' => $format->get_format(),
            'sectionreturn' => null,
        ];

        // The single section format has extra navigation.
        $singlesectionnum = $this->format->get_section_number();
        if ($singlesectionnum) {
            if (!$PAGE->theme->usescourseindex) {
                $sectionnavigation = new $this->sectionnavigationclass($format, $singlesectionnum);
                $data->sectionnavigation = $sectionnavigation->export_for_template($output);

                $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
                $data->sectionselector = $sectionselector->export_for_template($output);
            }
            $data->hasnavigation = true;
            $data->singlesection = array_shift($data->sections);
            $data->sectionreturn = $singlesectionnum;
        }

        if ($this->hasaddsection) {
            $addsection = new $this->addsectionclass($format);
            $data->numsections = $addsection->export_for_template($output);
        }

        if ($format->show_editor()) {
            $bulkedittools = new $this->bulkedittoolsclass($format);
            $data->bulkedittools = $bulkedittools->export_for_template($output);
        }

        return $data;
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE, $OUTPUT, $COURSE, $DB, $USER, $CFG;

        if ((int)($CFG->branch) >= 404) {
            $data = $this->export_for_template_override($output);
        } else {
            $data = parent::export_for_template($output);
        }

        // If we are on course view page for particular section.
        if ($this->format->get_viewed_section()) {
            // Do not display the "General" section when on a page of another section.
            if ($this->format->get_format_option('section0') == FORMAT_FLEXSECTIONS_SECTION0_COURSEPAGE) {
                if (isset($data->initialsection) && $data->initialsection->num == 0) {
                    $data->initialsection = null;
                }
            }

            // Add 'back to parent' control.
            $section = $this->format->get_section($this->format->get_viewed_section());
            if ($section->parent) {
                $sr = $this->format->find_collapsed_parent($section->parent);
                $url = $this->format->get_view_url($section->section, ['sr' => $sr]);
                $data->backtosection = [
                    'url' => $url->out(false),
                    'sectionname' => $this->format->get_section_name($section->parent),
                ];
            } else {
                $sr = 0;
                $url = $this->format->get_view_url($section->section, ['sr' => $sr]);
                $context = \context_course::instance($this->format->get_courseid());
                $data->backtocourse = [
                    'url' => $url->out(false),
                    'coursename' => format_string($this->format->get_course()->fullname, true, ['context' => $context]),
                ];
            }

            // Hide add section link below page content.
            $data->numsections = false;
        }

        // On the course main page, display this section as a card unless the
        // On the course main page, display this section as a card unless the
        // user is currently editing the page. Section #0 should never be
        // displayed as a card.
        //$issinglesectionpage = $this->format->get_section_number() != 0;
        $data->showascard = !$PAGE->user_is_editing() && ($this->format->get_format_option('sectionviewoption') == FORMAT_FLEXSECTIONS_SECTIONVIEW_CARDS);

        $courseimage = \core_course\external\course_summary_exporter::get_course_image($this->format->get_course());
        if (!$courseimage) {

            // Default image by instance.
            $imagename = 'course/' . $CFG->instancename;
            $courseimage = $OUTPUT->image_url($imagename, 'format_flexsections')->out(false);
        }
        $data->courseimageurl = $courseimage;

        // Upload course image.
        $showuploadcourseimage = false;
        if ($PAGE->user_is_editing() && $this->format->get_courseid() > 1 &&
                (substr($PAGE->pagetype, 0, strlen('course-view')) === 'course-view')) {
            $showuploadcourseimage = true;
            $PAGE->requires->js_call_amd('format_flexsections/uploadimage', 'course');
            $data->sesskey  = sesskey();
            $data->courseid = $this->format->get_courseid();

            $context = \context_course::instance($this->format->get_courseid());
            $data->contextid = $context->id;
        }
        $data->showuploadcourseimage = $showuploadcourseimage;

        $selfenrolenable = false;
        foreach (enrol_get_instances($PAGE->course->id, false) as $enrol) {
            if ($enrol->enrol == 'self' && $enrol->status == ENROL_INSTANCE_ENABLED) {
                $selfenrolenable = true;
            }
        }

        // Enrolkey.
        $enrolkeybtn = false;
        if ($PAGE->user_allowed_editing() && $PAGE->pagelayout === 'course' && $selfenrolenable) {
            $instances = $DB->get_records('enrol', array('courseid' => $PAGE->course->id, 'enrol' => 'self'));
            foreach ($instances as $instance) {

                if (!empty($instance->password)) {

                    $enrolurl = $CFG->wwwroot . '/enrol/self/enrolwithkey.php?enrolkey=' . $instance->password;

                    // QR Code and Link.
                    $size = 120;
                    $margin= 10;
                    $outputimage = new \theme_petel\qr_code($size, $margin);

                    try {
                        $base64string = $outputimage->create_image(
                                $enrolurl,
                                [255,255, 255, 127],[43, 43, 43]
                        );
                    }catch (\Exception $exception){
                        $base64string = '';
                    }

                    $title          = get_string('studentsenrolkey', 'theme_petel', $instance->password);
                    $icon           = html_writer::tag('i', '', array('class' =>'fa-solid fa-circle-info mr-2'));
                    $enrolkeybtn    = html_writer::tag('button', $icon . $title, array('id' => 'enrolkeybtn', 'class' => 'btn btn-sm btn-outline-secondary px-3', 'aria-label' => $title));
                    $contextheader = null;
                    $PAGE->requires->js_call_amd('format_flexsections/enrolkey', 'init_dialog',
                            array($contextheader, $instance->password, $base64string));
                    $PAGE->requires->strings_for_js(array('getcoursekeytitle', 'getkey', 'cancel'), 'theme_petel');
                }
            }
        }
        $data->enrolkeybtn = $enrolkeybtn;

        // Course completion.
        $coursecontext = \context_course::instance($this->format->get_course()->id);
        if ($this->format->get_format_option('showprogress') == FORMAT_FLEXSECTIONS_SHOWPROGRESS_SHOW) {
            if (!has_capability('moodle/course:viewhiddensections', $coursecontext)) {
                $coursecompletion = $this->get_course_completion($this->format->get_course()->id);
                $data->coursecompletion = $coursecompletion;
                $progressformat = $this->format->get_format_option('progressformat');
                $progressmode = $this->format->get_format_option('progressmode');
                $iscomplete = $coursecompletion['total'] == $coursecompletion['completed'];
                $data->showpercentage = !$iscomplete && $progressformat == FORMAT_FLEXSECTIONS_PROGRESSFORMAT_PERCENTAGE;
                $data->modecircle = !$iscomplete && $progressmode == FORMAT_FLEXSECTIONS_PROGRESSMODE_CIRCLE;
                $data->showcount = !$iscomplete && $progressformat == FORMAT_FLEXSECTIONS_PROGRESSFORMAT_COUNT;
            }
        }

        // Course links.
        $iscoursepage = preg_match("/course-view/", $PAGE->pagetype);
        $data->courselinks = $iscoursepage ? \theme_petel\output\core_renderer::course_links() : '';

        // Course search.
        $data->coursesearch = $iscoursepage ? \theme_petel\output\core_renderer::course_search() : '';;

        // Button disable shared course.
        if (has_capability('community/sharecourse:coursecopy', \context_course::instance($COURSE->id), $USER->id)){

            $availabletocohort = get_config('community_sharecourse', 'availabletocohort');
            require_once($CFG->dirroot.'/cohort/lib.php');

            $flagcourse = cohort_is_member($availabletocohort, $USER->id) ? true : false;

            // Check if admin.
            $isadmin = false;
            foreach (get_admins() as $admin) {
                if ($USER->id == $admin->id) {
                    $isadmin = true;
                    break;
                }
            }

            // Button disable share course.
            if(\community_oer\course_oer::funcs()::if_course_shared($COURSE->id) && ($flagcourse || $isadmin)) {
                $url = 'javascript:void(0)';
                $title = get_string('buttonshare', 'community_sharecourse');

                $html = html_writer::link($url,
                        '<span>' . get_string('buttonsharedcourse', 'community_sharecourse') . '<i class="fa-light fa-check"></i></span>',
                        array('class' => 'btn-disable-share-course btn btn-warning ml-2 mr-auto', 'role' => 'button', 'title' => $title));

                $data->coursesharedbutton = $iscoursepage ? $html : '';
            }
        }

        // Show single copy section.
        $roles = get_user_roles($coursecontext, $USER->id, false);
        $teachercolleague = false;
        foreach ($roles as $role) {
            if ($role->shortname == 'teachercolleague' || $role->shortname == 'teachertraining') {
                $teachercolleague = true;
            }
        }

        $data->copysectionenable = has_capability('moodle/course:update', $coursecontext, $USER->id) || $teachercolleague? true : false;

        if (isset($data->singlesection)) {
            $modinfo = $this->format->get_modinfo();
            $headerclass = $this->format->get_output_classname('content\\section\\header');
            $sectionheader = new $headerclass($this->format, $modinfo->get_section_info($data->singlesection->num));
            $data->singlesection->header = $sectionheader->export_for_template($output);
        }

        // Show cards in editable mode and card.
        $data->showsimplecards = $PAGE->user_is_editing() &&
                ($this->format->get_format_option('sectionviewoption') == FORMAT_FLEXSECTIONS_SECTIONVIEW_CARDS) && !isset($data->singlesection);
        if($data->showsimplecards){
            // Update section images.
            $PAGE->requires->js_call_amd('format_flexsections/uploadimage', 'multiSections');
        }

        // Show collapse button.
        if ($this->format->get_format_option('sectionviewoption') == FORMAT_FLEXSECTIONS_SECTIONVIEW_CARDS) {
            $data->showcollapsebutton = $PAGE->user_is_editing();
        }

        if ($this->format->get_format_option('sectionviewoption') == FORMAT_FLEXSECTIONS_SECTIONSVIEW_LIST) {
            $data->showcollapsebutton = true;
        }

        $data->showsingleuploadsection = $PAGE->user_is_editing() && isset($data->singlesection);
        if($data->showsingleuploadsection){
            $PAGE->requires->js_call_amd('format_flexsections/uploadimage', 'singleSection');
        }

        // Student status area.
        $data->showsectionstatus = false;
        if(isset($data->singlesection)){
            $PAGE->requires->js_call_amd('format_flexsections/sectionstatus', 'init', [$data->singlesection->id]);
            $data->showsectionstatus = true;
        }

        $data->allowedediting = has_capability('moodle/course:viewhiddensections', $coursecontext);

        $data->accordion = $this->format->get_accordion_setting() ? 1 : '';
        $data->mainsection = $this->format->get_viewed_section();

        return $data;
    }

    public function get_course_completion($courseid) {
        global $DB, $USER;

        // Get the course modules in the course.
        $courseModules = $DB->get_records('course_modules', ['course' => $courseid]);

        if (empty($courseModules)) {
            // No modules found in the course.
            return [
                    'total' => 0,
                    'completed' => 0,
                    'percentage' => 0,
                    'iscomplete' => false
            ];
        }

        $courseModuleIds = array_keys($courseModules);
        $completedModules = $DB->count_records_select('course_modules_completion',
                'coursemoduleid IN (' . implode(',', $courseModuleIds) . ') AND userid = :userid',
                ['userid' => $USER->id]
        );

        $totalModules = count($courseModules);
        $percentage = round(($completedModules / $totalModules) * 100);
        $isComplete = ($completedModules == $totalModules);

        //TODO: add correct names
        $rad1 =  round(100 - $percentage);
        $rad2 =  round(100 -  $rad1);

        return [
                'total' => $totalModules,
                'completed' => $completedModules,
                'percentage' => $percentage,
                'iscomplete' => $isComplete,
                'rad1' => $rad1,
                'rad2' => $rad2,
        ];
    }

    /**
     * Export sections array data.
     *
     * TODO: this is an exact copy of the parent function because get_sections_to_display() is private
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function export_sections(\renderer_base $output): array {

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections = [];
        $stealthsections = [];
        $numsections = $format->get_last_section_number();
        foreach ($this->get_sections_to_display($modinfo) as $sectionnum => $thissection) {
            // The course/view.php check the section existence but the output can be called
            // from other parts so we need to check it.
            if (!$thissection) {
                throw new \moodle_exception('unknowncoursesection', 'error',
                    course_get_url($course), format_string($course->fullname));
            }

            $section = new $this->sectionclass($format, $thissection);

            if ($sectionnum > $numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                if (!empty($modinfo->sections[$sectionnum])) {
                    $stealthsections[] = $section->export_for_template($output);
                }
                continue;
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            $sections[] = $section->export_for_template($output);
        }
        if (!empty($stealthsections)) {
            $sections = array_merge($sections, $stealthsections);
        }
        return $sections;
    }

    /**
     * Return an array of sections to display.
     *
     * This method is used to differentiate between display a specific section
     * or a list of them.
     *
     * @param course_modinfo $modinfo the current course modinfo object
     * @return \section_info[] an array of section_info to display
     */
    private function get_sections_to_display(course_modinfo $modinfo): array {
        $viewedsection = $this->format->get_viewed_section();
        return array_values(array_filter($modinfo->get_section_info_all(), function($s) use ($viewedsection) {
            return (!$s->section) ||
                (!$viewedsection && !$s->parent && $this->format->is_section_visible($s)) ||
                ($viewedsection && $s->section == $viewedsection);
        }));
    }
}
