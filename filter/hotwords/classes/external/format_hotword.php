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

namespace filter_hotwords\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External service
 *
 * @package   filter_hotwords
 * @category  external
 * @copyright 2024 Devlion.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_hotword extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'content' => new external_value(PARAM_RAW, 'raw data-text content', VALUE_REQUIRED),
        ]);
    }

    /**
     * formats code
     *
     * @param string $content
     * @return array (empty array for now)
     *
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \require_login_exception
     */
    public static function execute(string $content): array {
        global $COURSE, $CFG;

        require_login();
        $params = self::validate_parameters(self::execute_parameters(), [
            'content' => $content,
        ]);

        $result = [
            'warnings' => [],
            'content' => $params['content'],
        ];

        try {
            $doc = new \DOMDocument();
            $fs = get_file_storage();

            $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $params['content']);
            foreach ($doc->getElementsByTagName('img') as $imgtag) {
                $src = $imgtag->getAttribute('src');
                if (strpos($src, 'data:image') !== 0) {

                    $contents = '';
                    $path_parts = pathinfo($src);
                    $path_parts['extension'] = (explode('?', $path_parts['extension']))[0];

                    if (strpos($src, $CFG->wwwroot) !== false) {
                        //try local storage
                        $dirpath = explode('/', $path_parts['dirname']);
                        $itemid = array_pop($dirpath);
                        $filearea = array_pop($dirpath);
                        $component = array_pop($dirpath);
                        $contextid = array_pop($dirpath);
                        if ($file = $fs->get_file(
                                $contextid,
                                $component,
                                $filearea,
                                $itemid,
                                '/',
                                $path_parts['filename'] . '.' . $path_parts['extension']
                        )) {
                            $contents = $file->get_content();
                        }
                    } else {
                        $contents = file_get_contents($src);
                    }
                }

                if ($contents) {
                    $imgtag->setAttribute('src',
                            'data:image/' . $path_parts['extension'] . ';base64,' . base64_encode($contents));
                }
            }

            $result['content'] = $doc->saveHTML();
        } catch (\Exception $e) {
            $result['warnings'][] = [
                'item' => 'filter_hotwords',
                'itemid' => $COURSE->id,
                'warningcode' => $e->errorcode,
                'message' => $e->getMessage()
            ];
        }

        $result['content'] = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $result['content']);
        return $result;
    }

    /**
     * Describe the return structure of the external service.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'content' => new external_value(PARAM_RAW, 'formatted code', VALUE_OPTIONAL),
            'warnings' => new \external_warnings()
        ]);
    }
}
