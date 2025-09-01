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
 * External functions backported.
 *
 * @module     tool/inplacetranslate
 * @package    tool
 * @subpackage inplacetranslate
 * @copyright  2021 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/admin/tool/inplacetranslate/locallib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/customlang/locallib.php');

class tool_inplacetranslate_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_translated_string_parameters() {
        return new external_function_parameters(
            array(
                'query' => new external_value(PARAM_TEXT, 'String identifier'),
            )
        );
    }

    /**
     * @return string result of submittion
     */
    public static function get_translated_string($query) {
        $params = self::validate_parameters(self::translation_get_string_returns(),
            array(
                'query' => $query,
            )
        );

        $enabled = get_config('tool_inplacetranslate', 'enabled');

        $adminandedit = is_admin_and_page_edit();

        if (!($adminandedit && $enabled)) {
            return '';
        }

        $lang = current_language();
        $stringmanager = get_string_manager();
        $languages = $stringmanager->get_list_of_translations();
        $data = [];
        $data['currentlang'] = $lang;
        $query = explode('/', $query);
        $data['identifier'] = $query[0];
        $data['plugin'] = $query[1];
        if ($query[1] == '') {
            $data['plugin'] = 'core';
        }
        foreach ($languages as $lang => $language) {
            $l = new lang_string($data['identifier'], $data['plugin'], null, $lang);
            $original = get_string_from_lang_file($data['identifier'], $data['plugin'], $lang);
            $textel = new stdClass();
            $textel->lang = $lang;
            $textel->string = $l->out();
            $textel->original = $original;
            $data['text'][] = $textel;
        }
        return json_encode($data);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_translated_string_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function translation_get_string_returns() {
        return new external_function_parameters(
            array(
                'query' => new external_value(PARAM_TEXT, 'String identifier'),
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_translated_string_parameters() {
        return new external_function_parameters(
            array(
                'identifier' => new external_value(PARAM_TEXT, 'String identifier'),
                'translateJson' => new external_value(PARAM_RAW, 'translateJson'),
            )
        );
    }

    /**
     * @return string result of submittion
     */
    public static function set_translated_string($identifier, $translatejson) {
        $params = self::validate_parameters(self::set_translated_string_parameters(),
            array(
                'identifier' => $identifier,
                'translateJson' => $translatejson,
            )
        );

        $translations = json_decode($translatejson);

        $parts = explode('/', $identifier);
        $stringid = $parts[0];

        if ((isset($parts[1]) && $parts[1] == 'core') || !isset($parts[1])) {
            $component = 'moodle';
        } else {
            $component = $parts[1];
        }

        foreach ($translations as $translation) {
            $lang = $translation->lang;
            $customization = trim($translation->string);
            store_string_in_moodledata($component, $lang, $stringid, $customization);
        }

        get_string_manager()->reset_caches();
        template_reset_all_caches();

        return ['query' => true];
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function set_translated_string_returns() {
        return new external_function_parameters(
            array(
                'query' => new external_value(PARAM_TEXT, 'String identifier'),
            )
        );
    }

}
