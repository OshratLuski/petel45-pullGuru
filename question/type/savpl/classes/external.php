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
 * @package    qtype_savpl
 * @copyright  2024 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_savpl;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * @package    qtype_savpl
 * @copyright  2024 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class external extends \external_api {

    /**
     * Describes the parameters for get_quizzes_by_courses.
     *
     * @return \external_function_parameters
     * @since Moodle 3.1
     */
    public static function get_aisupport_parameters() {
        return new \external_function_parameters (
            array(
                'prompt' => new \external_value(PARAM_RAW, 'student prompt'),
                'userid' => new \external_value(PARAM_INT, 'student id'),
                'qaid' => new \external_value(PARAM_INT, 'question attempt id'),
                'questionid' => new \external_value(PARAM_INT, 'question id'),
                'quizid' => new \external_value(PARAM_INT, 'quiz (or another usage) id'),
            )
        );
    }

    /**
     * Returns a list of quizzes in a provided list of courses,
     * if no list is provided all quizzes that the user can view will be returned.
     *
     * @param array $courseids Array of course ids
     * @return array of quizzes details
     * @since Moodle 3.1
     */
    public static function get_aisupport($prompt, $userid, $qaid, $questionid, $quizid) {
        global $DB, $SESSION;

        $message = $llmresponse = '';
        $status = true;
        $isrestricted = false;

        try {
            $params = array(
                'prompt' => $prompt,
                'userid' => $userid,
                'qaid' => $qaid,
                'questionid' => $questionid,
                'quizid' => $quizid,
            );
            $params = self::validate_parameters(self::get_aisupport_parameters(), $params);

            $aisupportquestion = new \qtype_savpl\aisupport_question($params['questionid']);
            $isrestricted = !empty($aisupportquestion->question->ainumrequests);

            $key = 'aisupportleft' . $qaid;

            if (!isset($SESSION->$key) && $isrestricted) {
                //Abuse attempt
                $llmresponse = get_string('aiabuseattempt', 'qtype_savpl');
            } elseif ($SESSION->$key <= 0 && $isrestricted) {
                $llmresponse = get_string('ainosupportleft', 'qtype_savpl');
            } else {
                if (class_exists('\aiplacement_petel\external\qtype_savpl')) {

                    $fullaiprompt = $aisupportquestion->get_full_prompt($params['prompt']);
                    unset($params['prompt']);
                    $params['instanceid'] = $params['questionid'];
                    if ($isrestricted) {
                        $SESSION->$key--;
                    }
                    $response = \aiplacement_petel\external\qtype_savpl::execute(
                        $aisupportquestion->question->contextid,
                        $fullaiprompt,
                        'aisupport',
                        json_encode($params)
                    );
                    $llmresponse = $response['generatedcontent'];
                    $llmresponse =
                            trim(str_replace('```', '', substr($llmresponse, strpos($llmresponse, "\n"), strlen($llmresponse))));

                    $usage = $DB->get_record('question_usages', ['id' => $params['quizid']]);

                    if ($usage->component == 'mod_quiz') {
                        $sql =
                                "SELECT qa.preview, qa.quiz FROM {quiz_attempts} qa JOIN {context} c ON (qa.quiz = c.instanceid) WHERE c.id = ?";
                        $quizattempt = $DB->get_record_sql($sql, [$usage->contextid]);
                        if (isset($quizattempt->preview) && $quizattempt->preview === 0) {
                            //This is a real attempt
                            $courseid = $DB->get_field('quiz', 'course', ['id' => $quizattempt->quiz]);
                            $promptdata = (object) [
                                    'qattemptid' => $params['qaid'],
                                    'questionid' => $params['questionid'],
                                    'userid' => $params['userid'],
                                    'quizid' => $quizattempt->quiz,
                                    'courseid' => $courseid,
                                    'prompt' => $fullaiprompt,
                                    'response' => $llmresponse
                            ];

                            $promptslog = new \qtype_savpl\prompts(0, $promptdata);
                            $promptslog->create();
                        }
                    }
                }
            }

        } catch (\Throwable $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            'result' => $status,
            'response' => htmlentities($llmresponse),
            'message' => $message,
            'airequestsleft' => isset($key) ? $SESSION->$key : 0,
            'isrestricted' => $isrestricted,
        ];
    }

    /**
     * Describes the get_quizzes_by_courses return value.
     *
     * @return \external_single_structure
     * @since Moodle 3.1
     */
    public static function get_aisupport_returns() {
        return new \external_single_structure(
            array(
                'result' => new \external_value(PARAM_BOOL, 'return status'),
                'response' => new \external_value(PARAM_RAW, 'AI response', VALUE_OPTIONAL),
                'message' => new \external_value(PARAM_TEXT, 'error message', VALUE_OPTIONAL),
                'airequestsleft' => new \external_value(PARAM_INT, 'requests left', VALUE_OPTIONAL),
                'isrestricted' => new \external_value(PARAM_BOOL, 'are requests restricted', VALUE_OPTIONAL),
            )
        );
    }
}
