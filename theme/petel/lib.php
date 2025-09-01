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
 * Theme functions.
 *
 * @package     theme_petel
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$applysitewidecolor = optional_param('applysitewidecolor', null, PARAM_RAW);
if ($applysitewidecolor) {
    petel_clear_cache();
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_petel_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';

    if (isset($CFG->instancename)){
        $scss .= file_get_contents($CFG->dirroot . '/theme/petel/scss/globals/variables_'.$CFG->instancename.'.scss');
    }else {
        $scss .= file_get_contents($CFG->dirroot . '/theme/petel/scss/globals/variables_default.scss');
    }
    $scss .= file_get_contents($CFG->dirroot . '/theme/petel/scss/globals/variables.scss');
    $scss .= file_get_contents($CFG->dirroot . '/theme/petel/scss/main.scss');
    return $scss;
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_petel_process_css($css, $theme) {
    global $OUTPUT;

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }

    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);

    return $css;
}


// Clear theme cache on click 'apply sitewide color'.
function petel_clear_cache(){
    theme_reset_all_caches();
}

/**
 *
 */
function theme_petel_page_init(\moodle_page $page) {
    global $PAGE, $CFG;

    // PTL-7613 Hide quick access from module menu, when in course edit mode.
    if(false && strpos($page->pagetype, 'course-view-topics') !== false){
        // Quick access script init.
        $quickaccesses = json_decode(get_config('theme_petel', 'quick_access') ?? '[]', true);
        $qaid = isset($quickaccesses[$COURSE->id]) ? $quickaccesses[$COURSE->id] : '';
        $PAGE->requires->js_call_amd('theme_petel/quick_access', 'init', array('quickaccess' => $qaid));
    }

    $PAGE->requires->css('/lib/jquery/ui-1.13.2/jquery-ui.css');

    // Add local_accessibilitytool.
    if (file_exists($CFG->dirroot . "/local/accessibilitytool/lib.php")) {
        require_once($CFG->dirroot . "/local/accessibilitytool/lib.php");
        local_accessibilitytool_page_init($page);
    }
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_petel_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage')) {
        $theme = theme_config::load('petel');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else if ($context->contextlevel == CONTEXT_SYSTEM && ( $filearea === 'screenshot' )) {
        $theme = theme_config::load('petel');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

function theme_petel_get_policies() {
    global $PAGE, $CFG;
    $outputpolicy = $PAGE->get_renderer('tool_policy');
    $page = new \tool_policy\output\guestconsent();
    $data = $page->export_for_template($outputpolicy);
    $policies = [];
    if ($CFG->sitepolicyhandler == 'tool_policy' && $data->haspolicies) {
        foreach ($data->policies as $policy) {
            $pol = new stdClass();
            if (empty($returnurl)) {
                $returnurl = (new moodle_url('/admin/tool/policy/index.php'))->out_as_local_url(false);
            }
            $urlparams = ['versionid' => $policy->id, 'returnurl' => $returnurl];
            $pol->url = new moodle_url('/admin/tool/policy/view.php', $urlparams);
            $pol->name = $policy->name;
            $policies[] = $pol;
        }
    } else {

        $currentlang = current_language();
        $language = get_parent_language($currentlang) ?: $currentlang;

        switch ($language) {
            case 'en':
                $langfolder = 'en';
                break;
            case 'ar':
                $langfolder = 'ar';
                break;
            default:
                $langfolder = 'he';
        }

        $folder = '/theme/petel/docs/'.$langfolder;

        if (get_config('theme_petel', 'privacyurl')) {

            $file = $folder.'/petel_privacy_policy.html';

            $pol = new stdClass();
            $pol->url = new moodle_url($file);
            $pol->name = get_string('privacy', 'theme_petel');
            $policies[] = $pol;
        }

        if (get_config('theme_petel', 'termsurl')) {

            $file = $folder.'/petel_policy.html';

            $pol = new stdClass();
            $pol->url = new moodle_url($file);
            $pol->name = get_string('terms', 'theme_petel');
            $policies[] = $pol;
        }

        if (get_config('theme_petel', 'accessibility_policy_link')) {

            $file = $folder.'/accessibility_statement.html';

            $pol = new stdClass();
            $pol->url = new moodle_url($file);
            $pol->name = get_string('accessibility_policy', 'theme_petel');
            $policies[] = $pol;
        }
    }
    if (count($policies)>0){
        $policies[count($policies)-1]->lastitem = 1;
    }
    return $policies;
}
