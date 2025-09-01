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
 * This is built using the bootstrapbase template to allow for new theme's using Moodle's new Bootstrap theme engine
 *
 * @package   theme_petel
 * @copyright Copyright (c) 2016 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel;

defined('MOODLE_INTERNAL') || die();

/**
 * Additional methods for theme petel.
 * @package     theme_petel
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toolbox {

    /**
     *
     * @var bool
     */
    protected $corerenderer = null;

    /**
     *
     * @var object
     */
    protected static $instance;

    /**
     * Init instance
     */
    private function __construct() {

    }

    /**
     * Get instance
     * @return object
     */
    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets the core_renderer class instance so that when purging all caches and 'theme_xxx_process_css' etc.
     * the settings are correct.
     * @param object $core Child object of core_renderer class.
     */
    public static function set_core_renderer($core) {
        $us = self::get_instance();
        // Set only once from the initial calling lib.php process_css function so that subsequent parent calls do not override it.
        // Must happen before parents.
        if (null === $us->corerenderer) {
            $us->corerenderer = $core;
        }
    }

    /**
     * Finds the given setting in the theme from the themes' configuration object.
     * @param string $setting Setting name.
     * @param string $format false|'format_text'|'format_html'.
     * @return string false|value of setting.
     */
    public static function get_setting($setting, $format = false) {
        $us = self::check_corerenderer();

        $settingvalue = $us->get_setting($setting);

        global $CFG;
        require_once($CFG->dirroot . '/lib/weblib.php');
        if (empty($settingvalue)) {
            return false;
        } else if (!$format) {
            return $settingvalue;
        } else if ($format === 'format_text') {
            return format_text($settingvalue, FORMAT_PLAIN);
        } else if ($format === 'format_html') {
            return format_text($settingvalue, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
        } else if ($format === 'format_file_url') {
            return self::setting_file_url($setting, $setting);
        } else {
            return format_string($settingvalue);
        }
    }

    /**
     * Get setting file url
     * @param object $setting
     * @param object $filearea
     * @return object
     */
    public static function setting_file_url($setting, $filearea) {
        $us = self::check_corerenderer();

        return $us->setting_file_url($setting, $filearea);
    }

    /**
     * Get setting image url
     * @param object $imagename
     * @param object $component
     * @return object
     */
    public static function image_url($imagename, $component) {
        $us = self::check_corerenderer();
        return $us->image_url($imagename, $component);
    }

    /**
     * Check corerenderer
     * @return string|bool
     */
    static private function check_corerenderer() {
        $us = self::get_instance();
        if (empty($us->corerenderer)) {
            // Use $OUTPUT unless is not a Remui or child core_renderer which can happen on theme switch.
            global $OUTPUT;
            if (property_exists($OUTPUT, 'petel')) {
                $us->corerenderer = $OUTPUT;
            } else {
                // Use $PAGE->theme->name as will be accurate than $CFG->theme when using URL theme changes.
                // Core 'allowthemechangeonurl' setting.
                global $PAGE;
                $corerenderer = null;
                try {
                    $corerenderer = $PAGE->get_renderer('theme_' . $PAGE->theme->name, 'core');
                } catch (\coding_exception $ce) {
                    // Specialised renderer may not exist in theme.  This is not a coding fault.  We just need to cope.
                    $corerenderer = null;
                }
                // Fallback check.
                if (($corerenderer != null) && (property_exists($corerenderer, 'petel'))) {
                    $us->corerenderer = $corerenderer;
                } else {
                    // Probably during theme switch, '$CFG->theme' will be accurrate.
                    global $CFG;
                    try {
                        $corerenderer = $PAGE->get_renderer('theme_' . $CFG->theme, 'core');
                    } catch (\coding_exception $ce) {
                        // Specialised renderer may not exist in theme.  This is not a coding fault.  We just need to cope.
                        $corerenderer = null;
                    }
                    if (($corerenderer != null) && (property_exists($corerenderer, 'petel'))) {
                        $us->corerenderer = $corerenderer;
                    } else {
                        // Last resort.  Hopefully will be fine on next page load for Child themes.
                        // However '***_process_css' in lib.php will be fine as it sets the correct renderer.
                        $us->corerenderer = $PAGE->get_renderer('theme_petel', 'core');
                    }
                }
            }
        }
        return $us->corerenderer;
    }

    /**
     * Set font
     * @param string $css
     * @param string $fontname
     * @return string
     */
    static public function set_font($css, $fontname) {
        $fontfamilytag = '[[setting:fontfamily]]';
        $familyreplacement = $fontname;

        $css = str_replace($fontfamilytag, $familyreplacement, $css);

        return $css;
    }

    /**
     * Set color
     * @param string $css
     * @param string $themecolor
     * @param string $tag
     * @param string $defaultcolour
     * @return string
     */
    static public function set_color($css, $themecolor, $tag, $defaultcolour) {
        if (!($themecolor)) {
            $replacement = $defaultcolour;
        } else {
            $replacement = $themecolor;
        }

        $css = str_replace($tag, $replacement, $css);
        return $css;
    }

    /**
     * Set custom css
     * @param string $css
     * @param string $customcss
     * @return string
     */
    static public function set_customcss($css, $customcss) {
        $tag = '[[setting:customcss]]';
        $replacement = $customcss;
        $css = str_replace($tag, $replacement, $css);
        return $css;
    }

    /**
     * Set logo
     * @param string $css
     * @param string $logo
     * @return string
     */
    static public function set_logo($css, $logo) {
        $tag = '[[setting:logo]]';
        if (!($logo)) {
            $replacement = 'none';
        } else {
            $replacement = 'url(\'' . $logo . '\')';
        }
        $css = str_replace($tag, $replacement, $css);
        return $css;
    }

    /**
     * Set logo height
     * @param string $css
     * @param string $logoheight
     * @return string
     */
    static public function set_logoheight($css, $logoheight) {
        $tag = '[[setting:logoheight]]';
        if (!($logoheight)) {
            $replacement = '65px';
        } else {
            $replacement = $logoheight;
        }
        $css = str_replace($tag, $replacement, $css);
        return $css;
    }
}

