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
 * Implementaton of the quizaccess_teamworkcheck plugin.
 *
 * @package   quizaccess_teamworkcheck
 * @copyright 2025 Weizmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\quiz_settings;

defined('MOODLE_INTERNAL') || die();

// This work-around is required until Moodle 4.2 is the lowest version we support.
if (class_exists('\mod_quiz\local\access_rule_base')) {
    class_alias('\mod_quiz\local\access_rule_base', '\quizaccess_teamworkcheck_parent_class_alias');
    class_alias('\mod_quiz\form\preflight_check_form', '\quizaccess_teamworkcheck_preflight_form_alias');
    class_alias('\mod_quiz\quiz_settings', '\quizaccess_teamworkcheck_quiz_settings_class_alias');
} else {
    require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
    class_alias('\quiz_access_rule_base', '\quizaccess_teamworkcheck_parent_class_alias');
    class_alias('\mod_quiz_preflight_check_form', '\quizaccess_teamworkcheck_preflight_form_alias');
    class_alias('\quiz', '\quizaccess_teamworkcheck_quiz_settings_class_alias');
}

/**
 * A rule requiring the student to promise not to cheat.
 *
 * @copyright  2025 Weizmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_teamworkcheck extends quizaccess_teamworkcheck_parent_class_alias {

    public function is_preflight_check_required($attemptid) {
        return empty($attemptid);
    }

    public function add_preflight_check_form_fields(quizaccess_teamworkcheck_preflight_form_alias $quizform,
            MoodleQuickForm $mform, $attemptid) {
        global $PAGE;

        $mform->addElement('header', 'teamworkcheckheader',
                get_string('teamworkcheckheader', 'quizaccess_teamworkcheck'));
        $mform->addElement('static', 'teamworkcheckmessage', '',
                get_string('teamworkcheckstatement', 'quizaccess_teamworkcheck'));
        $mform->addElement('html',
                '<style>.quiz-bottom-bar{display: none !important;} input[name="new_submitbutton"] {display: none !important; }</style>');
        $btnhtml = \html_writer::start_tag('div', array('class' => 'pb-2'));
        $btnhtml .= \html_writer::tag('button', get_string('open_local', 'local_teamwork'),
                array('class' => 'btn btn-primary', 'id' => 'quizaccess_teamworkcheck'));
        $btnhtml .= \html_writer::end_tag('div');
        $mform->addElement('html', $btnhtml);

        $PAGE->requires->js_amd_inline('require(["jquery"], function($) {
                    $("#quizaccess_teamworkcheck").click(function(event){
                        event.preventDefault();
                        
                        $("#id_cancel").click();
                        
                        setTimeout(function () {
                            $("#open_local").attr("data-redirect", "1");
                            $("#open_local").click();
                        }, 300);
                    });
                });');
    }

    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        global $DB, $USER;

        $lqsoptions = \local_quiz_summary_option\funcs::get_quiz_config($quizobj->get_quiz()->cmid);
        if ($lqsoptions->summary_teamwork == 1) {
            return null;
        }

        $context = context_module::instance($quizobj->get_quiz()->cmid);
        if (has_capability('mod/quiz:manageoverrides', $context)) {
            return null;
        }
        if (!empty($quizobj->get_quiz()->teamworkcheckrequired)) {
            $teamwork = $DB->get_record('local_teamwork', ['moduleid' => $quizobj->get_quiz()->cmid]);
            if (!empty($teamwork) && $teamwork->active) {
                $sql = "SELECT count(*)
                        FROM {local_teamwork_members} m
                        JOIN {local_teamwork_groups} g ON (g.id = m.teamworkgroupid)
                        WHERE g.teamworkid= ? AND m.userid = ?";
                if (!$DB->count_records_sql($sql, [$teamwork->id, $USER->id])) {
                    return new self($quizobj, $timenow);
                }
            }
        }
        return null;
    }

    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {

        $mform->addElement('select', 'teamworkcheckrequired',
                get_string('teamworkcheckrequired', 'quizaccess_teamworkcheck'),
                array(
                        0 => get_string('notrequired', 'quizaccess_teamworkcheck'),
                        1 => get_string('required', 'quizaccess_teamworkcheck'),
                ));
        $mform->addHelpButton('teamworkcheckrequired',
                'teamworkcheckrequired', 'quizaccess_teamworkcheck');
        $mform->setDefault('teamworkcheckrequired', 1);
    }

    public static function save_settings($quiz) {
        global $DB;

        if (empty($quiz->teamworkcheckrequired)) {
            $DB->delete_records('quizaccess_teamworkcheck', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('quizaccess_teamworkcheck', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->teamworkcheckrequired = 1;
                $DB->insert_record('quizaccess_teamworkcheck', $record);
            }
        }
    }

    public static function delete_settings($quiz) {
        global $DB;

        $DB->delete_records('quizaccess_teamworkcheck', array('quizid' => $quiz->id));
    }

    public static function get_settings_sql($quizid) {
        return array(
                'teamworkcheckrequired',
                'LEFT JOIN {quizaccess_teamworkcheck} teamworkcheck ON teamworkcheck.quizid = quiz.id',
                array());
    }
}
