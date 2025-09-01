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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
namespace qtype_diagnosticadv\external;

global $CFG;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/question/type/diagnosticadv/lib.php');
require_once($CFG->dirroot . '/lib/modinfolib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use qtype_diagnosticadv\event\ai_analytics_created;

class analytics_processor extends external_api {

    /**
     * Returns description of the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'qid' => new external_value(PARAM_INT, 'Question id'),
                'cmid' => new external_value(PARAM_INT, 'CMID'),
                'slot' => new external_value(PARAM_INT, 'Slot of the question'),
        ]);
    }

    /**
     * The main execution function.
     *
     * @param int $input1 An integer input
     * @param string $input2 A text input
     * @return array Execution result
     * @throws \dml_exception
     */
    public static function execute($qid, $cmid, $slot): array {
        global $DB, $CFG, $SESSION;
        require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
        $params = self::validate_parameters(self::execute_parameters(), [
                'qid' => $qid,
                'cmid' => $cmid,
                'slot' => $slot,
        ]);


        $cmid = $params['cmid'];
        $qid = $params['qid'];
        $slot = $params['slot'];
        $context = \context_module::instance($cmid);
        list($course, $cm) = get_course_and_cm_from_cmid($cmid);

        require_capability('mod/quiz:viewreports', $context);

        $attempts = get_quiz_attempts($cm);
        if (!$attempts) {
            return [
                    'result' => "No attempts found",
                    'timecreated' => date("Y-m-d H:i")
            ];
        }
        $columns = getlogcolumns();
        $data = get_summary_data($attempts, $slot);
        $users = [];
        $log = [];
        foreach ($data as $key => $value) {
            if (isset($value['securitysure'])) {
                $value['securitysure'] = $value['securitysure'] == 'yes' ? 1 : 0;
            } else {
                $value['securitysure'] = '';
            }
            $users[$value['userid']]['studentname'] = "user" . $value['userid'];
            if ($value['answer']->custom) {
                $users[$value['userid']]['answer'] = $value['customanswer'];
            } else {
                $users[$value['userid']]['answer'] = $value['answer']->answer;
            }
            $users[$value['userid']]['comment'] = trim($value['comment']);
            $users[$value['userid']]['ifsecured'] = $value['securitysure'];
            $users[$value['userid']]['securedanswer'] = !empty($value['security']) ? trim($value['security']) : '';
        }

        $log[] = implode(',', $columns);
        foreach ($users as $user => $value) {
            $tmp = [];
            foreach (array_keys($columns) as $columindex) {
                $tmp[] = $value[$columindex];
            }
            $log[] = implode(',', $tmp);
        }
        $result = implode("\n", $log);
        $usersdata = $result;
        $questionoptions = \qtype_diagnosticadv\options::get_record(['questionid' => $qid]);
        $systempromt = get_config('qtype_diagnosticadv', 'systempromt');
        $teacherpromt = $questionoptions->get('promt');
        $teacherpromt = $systempromt. "\n" . $teacherpromt;
        $promt = str_replace('{{LOG}}', $result, $teacherpromt);

        if (class_exists('\aiplacement_petel\external\qtype_diagnosticadv')) {
            $params['instanceid'] = $qid;
            $SESSION->openai_adv_temperature = $questionoptions->get('temperature');
            $response = \aiplacement_petel\external\qtype_diagnosticadv::execute(
                \context_module::instance($cmid)->id,
                $promt,
                'diagnosticadv',
                json_encode($params)
            );
            if ($response['errorcode'] > 0) {

                $event = ai_analytics_created::create([
                        'objectid' => $qid,
                        'context' => $context,
                        'other' => ['promt' => $promt, 'result' => 'error ai request']
                ]);
                $event->trigger();
                return [
                        'result' => "AI request error",
                        'timecreated' => date("Y-m-d H:i")
                ];
            }

            $airesult = $response['generatedcontent'];

            $airesult = str_replace('```html', '', $airesult);
            $airesult = str_replace('```', '', $airesult);

            $airesult = replaceusersnames($airesult);

            $event = ai_analytics_created::create([
                    'objectid' => $qid,
                    'context' => $context,
                    'other' => ['promt' => $promt, 'result' => $airesult, 'userdata' => $usersdata]
            ]);
            $event->trigger();
            return [
                    'result' => $airesult,
                    'timecreated' => date("Y-m-d H:i")
            ];
        } else {
            return [
                    'result' => "Ai disabled or not installed. Please install tool_aiconnect plugin and enable ai in question settings.",
                    'timecreated' => date("Y-m-d H:i")
            ];
        }
    }

    /**
     * Returns description of the output data.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
                'result' => new external_value(PARAM_RAW, 'The result of the execution in HTML format'),
                'timecreated' => new external_value(PARAM_TEXT, 'The result of the execution')
        ]);
    }
}