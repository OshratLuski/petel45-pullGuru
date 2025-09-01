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
 * @package    local_quiz_summary_option
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quiz_summary_option;

defined('MOODLE_INTERNAL') || die();

class funcs {

    public static function quizquestiontitlepresets_default() : \stdClass {

        // Default.
        $objdefault = new \StdClass();
        $objdefault->summary_hideall = $objdefault->summary_numbering = $objdefault->summary_grade
                = $objdefault->summary_mark = $objdefault->summary_teacherdialog = $objdefault->summary_fixedmin = 0;

        $objdefault->summary_state = $objdefault->summary_questionname = $objdefault->summary_teamwork =
        $objdefault->show_summary = 1;

        // Get question title elements presets from config.php.
        $objdefault =  self::update_quizquestiontitlepresets($objdefault);

        return $objdefault;
    }

    private static function update_quizquestiontitlepresets(\stdClass $objdefault) : \stdClass {
        global $CFG;

        // Get question title elements presets from config.php.
        if (isset($CFG->quizquestiontitlepresets) && is_array($CFG->quizquestiontitlepresets)) {
            if (array_key_exists('no-qname', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_questionname = $CFG->quizquestiontitlepresets['no-qname'];
            }
            if (array_key_exists('no-qstate', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_state = $CFG->quizquestiontitlepresets['no-qstate'];
            }
            if (array_key_exists('no-qnumbering', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_numbering = $CFG->quizquestiontitlepresets['no-qnumbering'];
            }
            if (array_key_exists('no-qgrade', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_grade = $CFG->quizquestiontitlepresets['no-qgrade'];
            }
            if (array_key_exists('no-qmark', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_mark = $CFG->quizquestiontitlepresets['no-qmark'];
            }
            if (array_key_exists('no-qchatwithteacher', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_teacherdialog = $CFG->quizquestiontitlepresets['no-qchatwithteacher'];
            }
            if (array_key_exists('no-hideall', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_hideall = $CFG->quizquestiontitlepresets['no-hideall'];
            }
            if (array_key_exists('no-teamwork', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_teamwork = $CFG->quizquestiontitlepresets['no-teamwork'];
            }
            if (array_key_exists('no-fixedmin', $CFG->quizquestiontitlepresets)) {
                $objdefault->summary_fixedmin = $CFG->quizquestiontitlepresets['no-fixedmin'];
            }
        }

        return $objdefault;
    }

    public static function get_quiz_config($cmid = 0) {
        global $DB;

        $objdefault = self::quizquestiontitlepresets_default();

        if ($cmid > 0) {

            $row = $DB->get_record('local_quiz_summary_option', ['cmid' => $cmid]);

            // Teacher see all options always.
            if ($row) {
                $obj = json_decode($row->show_elements);
                if (is_object($obj)) {
                    $objdefault->summary_hideall = isset($obj->summary_hideall) ? $obj->summary_hideall : 0;
                    $objdefault->summary_numbering = isset($obj->summary_numbering) ? $obj->summary_numbering : 0;
                    $objdefault->summary_state = isset($obj->summary_state) ? $obj->summary_state : 1;
                    $objdefault->summary_grade = isset($obj->summary_grade) ? $obj->summary_grade : 0;
                    $objdefault->summary_mark = isset($obj->summary_mark) ? $obj->summary_mark : 0;
                    $objdefault->summary_teacherdialog = isset($obj->summary_teacherdialog) ? $obj->summary_teacherdialog : 0;
                    $objdefault->summary_questionname = isset($obj->summary_questionname) ? $obj->summary_questionname : 1;
                    $objdefault->summary_teamwork = isset($obj->summary_teamwork) ? $obj->summary_teamwork : 1;
                    $objdefault->summary_fixedmin = isset($obj->summary_fixedmin) ? $obj->summary_fixedmin : 0;
                }

                $objdefault->show_summary = $row->show_summary;
            }

            if (isset($objdefault->summary_hideall) && $objdefault->summary_hideall === 1) {
                $objdefault->summary_numbering = $objdefault->summary_state = $obj->summary_teamwork = $obj->summary_fixedmin =
                $objdefault->summary_grade = $objdefault->summary_mark = $objdefault->summary_teacherdialog =
                $objdefault->summary_questionname = 1;
            }
        }

        unset($objdefault->summary_hideall);

        return $objdefault;
    }

}
