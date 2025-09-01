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

namespace mod_codescore\task;

use core\task\adhoc_task;
// use codescore\document_services;
// use codescore\combined_document;

// use assign;

/**
 * An adhoc task to convert submissions to pdf in the background.
 *
 * @copyright  2024 Devlion <info@devlion.co>
 * @package    codescore
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_attempt extends adhoc_task {

    /**
     * Run the task.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/mod/codescore/classes/GptxPrompt.php");
        $data = $this->get_custom_data();

        $config = get_config('mod_codescore');
        $key = $config->apikey;
        $tt = new \GptxPrompt($data);
        $attempts = $DB->get_records('codescore_attempts', ['id' => $data->id]);
        foreach ($attempts as $attempt) {
            $cm = get_coursemodule_from_id('codescore', $attempt->codescore);
            $codescore = $DB->get_record('codescore', ['id' => $cm->instance]);
            $attempt->state = 'pending';
            $attempt->timemodified = time();
            $langobject = array(
                    '0' => 'Javascript',
                    '1' => 'C++',
                    '2' => 'Python',
                    '3' => 'Java',
                    '4' => 'GO',
                    '5' => 'C#',
                    '6' => 'PHP',
                    '7' => 'HTML',
                    '8' => 'Typescript',
                    '9' => 'Visual Basic',
                    '10' => 'Ruby',
                    '11' => 'SQL',
                    '12' => 'Assembly',
            );
            $data = (object) [];
            $data->lang = $langobject[$codescore->programminglang];
            $data->solution = $attempt->code;
            $data->task = $codescore->task;

            $response = $tt->sendprompt($codescore);
            $response = str_replace('```json', '', $response);
            $response = str_replace('```', '', $response);
            $response = json_decode($response);
            if ($codescore->autograde === "1") {
                $attempt->grade = $response->grade;
            }
            $attempt->aigrade = $response->grade;
            $updatedattempt = $DB->get_record('codescore_attempts', ['id' => $attempt->id]);
            $rubricsobj = json_decode($updatedattempt->rubricsobject, true);

            foreach ($rubricsobj as $arrkey => $value) {
                $rubricsobj[$arrkey]['grade'] = $response->grades->{'rubric' . $arrkey . 'grade'};
            }

            $attempt->rubricsobject = json_encode($rubricsobj);

            $attempt->feedback = $response->feedback;
            $attempt->output = $response->output;
            $attempt->correctedcode = $response->corrected;
            $attempt->state = 'finished';
            $attempt->timemodified = time();
            $DB->update_record('codescore_attempts', $attempt);
        }

    }
}




