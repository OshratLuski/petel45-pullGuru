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
 * A drawer based layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2021 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$extraclasses[] = \theme_petel\funcs::add_role_class_to_body();
$extraclasses[] = $uicontentgender = \theme_petel\funcs::add_user_profile_uigender_to_body();

$datacourseid = '';
if (!empty($PAGE->course->id)) {
    $datacourseid = 'data-courseid="' . $PAGE->course->id . '"';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {

    // Custom navigation.
    theme_petel\custom_navigation::secondary_navigation();

    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    // PTL-9578.
    // $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
    // PTL-9578.
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);

    // PTL-9713. Hide drawer by default for student on quiz.
    if ( in_array($PAGE->pagetype, [
        'mod-quiz-attempt',
        'mod-quiz-review',
        'mod-quiz-view',
        'mod-quiz-report',
        'mod-quiz-summary',
    ])) {
        $courseindexopen = false;
    }

}

$primary = new theme_petel\navigation\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;

$headercontent = $header->export_for_template($renderer);

$buttonvloseblockdrawer = true;

// Open block drawer on page my. PTL-9459.
if ($PAGE->pagetype == 'my-index') {
    $forceblockdraweropen = true;
    $buttonvloseblockdrawer = false;
}

// Close left block for quiz reports and question.
if ( substr($PAGE->pagetype, 0, 9) == 'mod-quiz-' || substr($PAGE->pagetype, 0, 14) == 'question-type-') {
    //$blockdraweropen = false;
    $hasblocks = false;
}

if (in_array($PAGE->pagetype, ['mod-quiz-attempt', 'mod-quiz-summary', 'mod-quiz-review'])) {
    $hasblocks = true;
}

$abouturl = get_config('theme_petel', 'abouturl') != '' ? get_config('theme_petel', 'abouturl') : 'https://petel.weizmann.ac.il/';
$policies = theme_petel_get_policies();

if ($PAGE->pagelayout == 'base') {
    $buttonvloseblockdrawer = false;
    $forceblockdraweropen = false;
}

$footerdisabled = $PAGE->pagetype == 'grade-report-grader-index' ? true : false;

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'datacourseid' => $datacourseid,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    'abouturl' => $abouturl,
    'policies' => $policies,
    'buttonvloseblockdrawer' => $buttonvloseblockdrawer,
    'uicontentgender' => $uicontentgender,
    'footerdisabled' => $footerdisabled,
];

echo $OUTPUT->render_from_template('theme_boost/drawers', $templatecontext);

// EC-497.
$p = $PAGE->url;
$p->remove_all_params();

if ($p->out_as_local_url() == '/question/bank/editquestion/question.php') {
    if (strpos($PAGE->url->get_param('returnurl'), '/mod/quiz/attempt.php') !== false) {
        if ($cmid = optional_param('cmid', 0, PARAM_INT)) {

            $url = new \moodle_url('/mod/quiz/startattempt.php', ['cmid' => $cmid, 'sesskey' => sesskey(), 'forcenew' => 1]);

            $PAGE->requires->js_amd_inline('
                require(["jquery"], function($) {
                    let obj = $("input[name='."'returnurl'".']");
                    obj.val("'.$url->out_as_local_url().'");                                        
                });
            ');
        }
    }
}
