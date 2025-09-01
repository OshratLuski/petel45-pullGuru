<?php
// This file is part of Moodle - https://moodle.org/
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
 * savpl renderer class.
 * @package    qtype_savpl
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../../../config.php');
require_once(__DIR__.'/locallib.php');

require_login();

/**
 * Generates HTML output for savpl.
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_savpl_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        $question = $qa->get_question();

        global $USER, $COURSE, $CFG, $SESSION;
        $userid = $USER->id;
        $qid = $question->id;

        $inputname = $qa->get_qt_field_name('answer');
        $lastanswer = $qa->get_last_qt_var('answer');

        if ($lastanswer == null) {
            if ($question->trywithoutgrade){
                $lastanswer = $question->answertemplate;
            }
            else {
                $lastanswer = static::get_last_answer($qa) ?: $question->answertemplate;

            }
        }

        $html = parent::formulation_and_controls($qa, $options) . $this->output->box_start();

        $defaulttheme = isset(get_config('mod_vpl')->editor_theme) ? get_config('mod_vpl')->editor_theme : 'chrome';
        $acetheme = get_user_preferences('vpl_acetheme', $defaulttheme);

        $plugin = new stdClass();
        require($CFG->dirroot . '/mod/vpl/version.php');
        $vplversion = $plugin->version;
        unset($plugin);

        $this->output->page->requires->strings_for_js(
            array('compilation', 'evaluation', 'evaluationerror', 'execerror', 'execerrordetails', 'execution'),
            SAQVPL);

        $this->output->page->requires->js_call_amd(SAQVPL.'/studentanswer', 'setup',
            array($qid, $userid, $inputname, $vplversion));

        // Find the line where the {{ANSWER}} tag is located, to offset line numbers on Ace editor.
        // This offset is useful for compilation errors, so that error line will match editor line.
        $lineoffset = 1;
        foreach (explode("\n", $question->templatecontext) as $index => $line) {
            if (strpos($line, "{{ANSWER}}") !== false) {
                $lineoffset = $index + 1;
            }
        }
        $templatecontext = new stdClass();
        $templatecontext->qid = $qid;
        $templatecontext->readonly = $options->readonly;
        $templatecontext->inputname = $inputname;
        $templatecontext->lineoffset = $lineoffset;
        $templatecontext->templatelang = $question->templatelang;
        $templatecontext->lastanswer = $lastanswer;
        $templatecontext->run = empty($question->disablerun); //EC-397
        $key = 'aisupportleft' . $qa->get_database_id();

        if (!isset($SESSION->$key)) {
            $SESSION->$key = $question->ainumrequests;
        }

        $templatecontext->aisupport = !empty($question->aisupport); //PTL-12168
        $templatecontext->ainumrequests = $SESSION->$key ?? $question->ainumrequests; //PTL-12168
        $templatecontext->ainumenabled = $question->ainumrequests > 0; //PTL-12168
        $templatecontext->qaid = $qa->get_database_id(); //PTL-12168
        $templatecontext->qid = $qa->get_question_id(); //PTL-12168
        $templatecontext->quizid = $qa->get_usage_id(); //PTL-12168
        $templatecontext->userid = $userid; //PTL-12168
        $templatecontext->precheck = $question->precheckpreference != 'none';
        $templatecontext->precheckaction = $question->precheckpreference == 'dbg' ? 'debug' : 'evaluate';
        // Adjusting precheck and precheckaction based on disableevaluate
        if ($question->precheckpreference != 'none') {
            // Determine the intended precheck action
            $precheckaction = $question->precheckpreference == 'dbg' ? 'debug' : 'evaluate';

            if ($precheckaction == 'evaluate' && !empty($question->disableevaluate)) {
                // Evaluate is disabled, so precheck should be false
                $templatecontext->precheck = false;
            } else {
                // Set precheck and precheckaction as intended
                $templatecontext->precheck = true;
                $templatecontext->precheckaction = $precheckaction;
            }
        } else {
            // Precheck preference is 'none', so precheck is false
            $templatecontext->precheck = false;
        }
        $templatecontext->answertemplate = $question->answertemplate;
        $templatecontext->correction = has_capability('moodle/course:update', context_course::instance($COURSE->id));
        $templatecontext->teachercorrection = $question->teachercorrection;

        $html .= $this->output->render_from_template('qtype_savpl/question', $templatecontext);

        $html .= $this->output->box_end();

        return $html;
    }

    public function specific_feedback(question_attempt $qa) {
        $feedback = '';
        if ($qa->get_state()->is_finished()) {
            $feedback = '<div class="correctness '.$qa->get_state_class(true).' badge">'.
                $qa->get_state()->default_string(true).
                '</div>';
        }
        if ($qa->get_state()->is_graded()) {
            $evaldata = $qa->get_last_qt_var('_evaldata', null);
            if ($evaldata === null) {
                // In older versions (<= 2021070700), evaluation data was stored as response summary.
                // Keep this piece of code to handle old question attempts.
                $evaldata = $qa->get_response_summary();
            }
            $displayid = 'vpl_eval_details_q'.$qa->get_question()->id;
            $feedback .= '<div class="m-t-1">
                            <h5>'.get_string('evaluationdetails', SAQVPL).'</h5>
                            <pre id="'.$displayid.'" class="bg-white p-2 border" style="text-align: left;"
                                data-result="'.htmlspecialchars($evaldata).'">
                            </pre>
                         </div>';
            $this->output->page->requires->js_call_amd(SAQVPL.'/studentanswer', 'displayResult',
                array($displayid, null));
        }
        return $feedback;
    }

    public function correct_response(question_attempt $qa) {
        if (!$qa->get_question()->teachercorrection) {
            return '';
        }
        return '<h5>'.get_string('possiblesolution', SAQVPL).'</h5>'.
            '<pre class="line-height-3" style="text-align: left;direction: ltr;">'.htmlspecialchars($qa->get_question()->teachercorrection).'</pre>';
    }

    static function get_last_answer(question_attempt $qa) {
        global $CFG, $DB;
        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->libdir . '/modinfolib.php');

        $return = null;
        $usage = $DB->get_record('question_usages', ['id' => $qa->get_usage_id()]);
        //We do this for quizzes only currently.
        if ($usage->component == 'mod_quiz') {
            $quizattempt = $DB->get_record('quiz_attempts', ['uniqueid' => $qa->get_usage_id()]);
            list($course, $cm) = get_course_and_cm_from_instance($quizattempt->quiz, 'quiz');
            $context = \context_module::instance($cm->id);
            $question = $qa->get_question();

            //Get previous quiz attempt on this.
            $sql = "SELECT qa.id 
                    FROM {question_attempts} qa 
                    JOIN {quiz_attempts} qua ON (qa.questionusageid = qua.id) 
                    WHERE qua.userid = ? AND qa.questionid = ? AND qa.id <> ? ORDER BY qa.id DESC LIMIT 1";
            if ($questionattemptid = $DB->get_record_sql($sql, [$quizattempt->userid, $question->id, $qa->get_database_id()])) {
                $previousattempt = (new question_engine_data_mapper())->load_question_attempt($questionattemptid);
                $return = $previousattempt->get_last_step_with_qt_var('answer');
            }
        }

        return $return;
    }
}
