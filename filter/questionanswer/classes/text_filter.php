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
 * Version details
 *
 * @package     filter_questionanswer
 * @copyright   2023 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @auther      Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @auther      Initial code by assistant author: GPT4 <gpt4@openai.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_questionanswer;

defined('MOODLE_INTERNAL') || die();

use mod_quiz\quiz_attempt;

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'filter_questionanswer_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'filter_questionanswer_base_text_filter');
}

class text_filter extends \filter_questionanswer_base_text_filter {

    public function filter($text, array $options = []) {
        global $USER, $PAGE, $DB;

        //if (preg_match_all('/\{questionanswer:(\d+)\}/', $text, $matches, PREG_SET_ORDER)) {
        if (preg_match_all('/\{questionanswer:(\d+),([^,]*),([^}]*)\}/', $text, $matches, PREG_SET_ORDER)) {

            $coursecontext = $PAGE->context->get_course_context(false);
            if ($coursecontext) {
                $cm = get_coursemodule_from_id('quiz', $PAGE->cm->id, 0, false, MUST_EXIST);
                $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

                // Get the user's latest attempt.
                $attempts = $DB->get_records('quiz_attempts',
                    ['quiz' => $quiz->id, 'userid' => $USER->id] #, 'state' => 'finished']
                    , 'attempt DESC', '*', 0, 1);
                $attempt = reset($attempts);

                //if ($attempt) {
                //    $attemptobj = quiz_attempt::create($attempt->id);
                //    foreach ($matches as $match) {
                //        $questionnumber = $match[1];
                //        $answer = $this->get_student_answer($attemptobj, $questionnumber);
                //        $text = str_replace($match[0], $answer, $text);
                //    }
                //}
                if ($attempt) {
                    $attemptobj = quiz_attempt::create($attempt->id);
                    foreach ($matches as $match) {
                        $questionnumber = $match[1];
                        $compareValue = $match[2];
                        $outputText = $match[3];
                        $answer = $this->get_student_answer($attemptobj, $questionnumber,
                                                            $compareValue, $outputText);
                        $text = str_replace($match[0], $answer, $text);
                    }
                }
            }
        }

        return $text;
    }

    private function get_student_answer($attemptobj, $questionnumber, $compareValue, $outputText) {
        $slots = $attemptobj->get_slots();

        foreach ($slots as $slot) {
            $question = $attemptobj->get_question_attempt($slot)->get_question();

            if ($slot === $questionnumber) {
                $response = $attemptobj->get_question_attempt($slot)->get_response_summary();
                // If qtype=formulas do some preprocessing...
                if (get_class($question->qtype) === 'qtype_formulas') {
                    $response = str_replace(', ', ':', $response);
                }
                if (get_class($question->qtype) === 'qtype_essay') {
                    // Get the question attempt object for the specific slot
                    $questionattempt = $attemptobj->get_question_attempt($slot);

                    // Get the response for the essay question
                    $response_html = $questionattempt->get_last_qt_var('answer');

                    // If you want to get the response as text (assuming the response is in HTML format)
                    $response = html_to_text($response_html, 0, false);
                }
                if ($response) {
                    return (trim($response) === trim($compareValue)) ? s($outputText) : s($response);
                } else {
                    return 'No response';
                }
            }
        }

        return 'Question not found';
    }

    private function get_student_answer_v3($attemptobj, $questionnumber) {
        $slots = $attemptobj->get_slots();

        foreach ($slots as $slot) {
            //$question = $attemptobj->get_question($slot); // v2
            $question = $attemptobj->get_question_attempt($slot)->get_question();

            //if ($question->number == $questionnumber) {
            if ($slot == $questionnumber) {
                //$response = $attemptobj->get_response_summary($slot); // v3
                $response = $attemptobj->get_question_attempt($slot)->get_response_summary();

                if ($response) {
                    return s($response);
                } else {
                    return 'No response';
                }
            }
        }

        return 'Question not found';
    }
}