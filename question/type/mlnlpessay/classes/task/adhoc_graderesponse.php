<?php

namespace qtype_mlnlpessay\task;

use question_engine;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/question/engine/lib.php';
require_once $CFG->dirroot . '/question/type/mlnlpessay/locallib.php';

class adhoc_graderesponse extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'qtype_mlnlpessay';
    }

    public function execute() {
        global $DB, $CFG;

        //$row = $DB->get_record('task_adhoc',['id'=>167]);
        //$data = json_decode($row->customdata);
        $data = $this->get_custom_data();
        if (empty($data->categoriesweight)){
            mtrace("The question was defined without categories");
            return;
        }
        $categoriesweight = $data->categoriesweight;
        $questionid = $data->questionid;
        $qa = $data->question_attempt->id;
        $answertext = self::preparetext($data->answertext);
        $question_attempt = $data->question_attempt;
        $models_number = $data->models_number;
        $categoriesids = array_keys((array)$categoriesweight);

        mtrace('answertext:');
        mtrace('==========================================');
        mtrace($answertext);
        mtrace('==========================================');

        // Checking for question attempt.
        $question_attempt_id = $question_attempt->id;
        mtrace('Checking for question attempt = ' . $question_attempt_id);
        $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);
        if (!$question_attempt) {
            mtrace('question_attempt with id ' . $question_attempt . 'does not exist. Exiting.');
            return;
        }
        mtrace('question_attempt');
        mtrace(print_r($question_attempt, 1));

        $catparams = [
            'active' => 1,
            'disabled' => 0
        ];
        $cattags = [];
        foreach (\qtype_mlnlpessay\persistent\categories::get_records($catparams) as $persistent) {
            $key = $persistent->get('model') . '_' . $persistent->get('modelid');
            $cattags[$key] = [
                'tag' => $persistent->get('tag'),
                'model' => $persistent->get('model'),
                'modelid' => $persistent->get('modelid'),
            ];
        }

        if ($answertext != '') {
            $categories = get_enabled_categories($questionid);
            $processingmode = get_config('qtype_mlnlpessay', 'processing_mode');
            switch ($processingmode) {
                case '0': // Random
                    $output = [];
                    foreach ($categories as $cat) {
                        if (isset($cattags[$cat->id])) {
                            $output[$cattags[$cat->id]['tag']] = random_int(0, 1);
                        }
                    }
                    mtrace(" Generating random respons in qtype_mlnlp_wo_python mode: " . json_encode($output , JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    $output = (object) $output;
                    break;

                case '1': // Local
                    mtrace('Local mode');
                    $moodledatapath = $CFG->dataroot;
                    if (!empty($CFG->mlnlpdebug)) {
                        try {
                            $path = make_writable_directory($moodledatapath . '/mlnlpdata');
                        } catch (\moodle_exception $e) {
                            mtrace($e->getMessage());
                        }

                    } else {
                        try {
                            $path = make_temp_directory(random_string());
                        } catch (\moodle_exception $e) {
                            mtrace($e->getMessage());
                        }
                    }

                    $script = $CFG->libdir . '/../question/type/mlnlpessay/scripts/calc.py';
                    $pathtopython = get_config('core', 'pathtopython');
                    if ($pathtopython) {
                        $script = $pathtopython . ' ' . $script;
                    }

                    try {
                        $executestart = time();
                        $result_filename = 'o_' . $qa . "_" . $executestart . '.json';
                        $outputfilepath = $path . '/' . $result_filename;
                        $fp = fopen($outputfilepath, 'w');
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    try {
                        $text_filename = 't_' . $qa . "_" . $executestart . '.txt';
                        $textfilepath = $path . '/' . $text_filename;
                        $fp = fopen($textfilepath, 'w');
                        fwrite($fp, $answertext);
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    try {
                        $log_filename = 'o_' . $qa . "_" . $executestart . '.log';
                        $logfilepath = $path . '/' . $log_filename;
                        $fp = fopen($logfilepath, 'w');
                        fclose($fp);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    mtrace(" $script '$outputfilepath' '$moodledatapath' '$textfilepath' '$qa' '$categoriesids' > '$logfilepath' 2>&1 ");

                    try {
                        shell_exec(" $script '$outputfilepath' '$moodledatapath' '$textfilepath' '$qa' '$categoriesids' '$models_number' > '$logfilepath' 2>&1 ");
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    $file = fopen($outputfilepath, "r");
                    if (!$file) {
                        mtrace("Error in opening file: " . $outputfilepath);
                    }
                    mtrace("output file path: " . $outputfilepath);

                    try {
                        $filesize = filesize($outputfilepath);
                        $filetext = fread($file, $filesize);
                        fclose($file);
                    } catch (\moodle_exception $e) {
                        mtrace($e->getMessage());
                    }

                    $output = json_decode($filetext);

                    foreach ($output as $key => $value) {
                        mtrace('output for categoyid: ' . $key . ' => ' . $value);
                    }
                    break;
                case '2': // AWS Lambda
                    $output = [];
                    $chunks = get_config('qtype_mlnlpessay', 'executechunks'); // Chunk size
                    $attemptsnumber = get_config('qtype_mlnlpessay', 'errorsrequests'); // Retry attempts


                    if (json_last_error() !== JSON_ERROR_NONE) {
                        mtrace("Invalid JSON format in categories IDs");
                        return;
                    }

                    // Group tags by their models
                    $modelGroups = [];
                    foreach ($categoriesids as $cat) {
                        if (isset($cattags[$cat])) {
                            $modelGroups[$cattags[$cat]['model']][] =  $cattags[$cat]['modelid'];
                        }
                    }
                    $resultmodelGroups = [];
                    foreach ($modelGroups as $model => $tags) {
                        mtrace("Processing model: $model");
                        // Split tags for the current model into chunks if necessary
                        if ($chunks > 0 && count($tags) > $chunks) {
                            $tagChunks = array_chunk($tags, $chunks);
                        } else {
                            $tagChunks = [$tags];
                        }

                        $tmp = [];
                        foreach ($tagChunks as $chunkIndex => $tagChunk) {
                            mtrace("Processing chunks ".count($tagChunk)." for model: ".$model);
                            // Retry logic
                            for ($i = 0; $i < $attemptsnumber; $i++) {
                                try {
                                    $output = static::execute_mlnlp($tagChunk, $answertext, $qa, $model);
                                    if (!empty($output)) {
                                        break; // Successful execution
                                    }
                                } catch (\Exception $e) {
                                    mtrace("Attempt " . ($i + 1) . " failed for chunk $chunkIndex: " . $e->getMessage());
                                }
                            }
                            $tmp = array_merge($tmp, (array) $output); // Merge output results
                        }
                        $resultmodelGroups[$model] = $tmp;
                    }

                    $output = $resultmodelGroups;
                    break;

            }
        } else {
            // If answertext == '' -> WRONG ANSWER.
            $output = [];
            foreach ($categoriesweight as $catn) {
                $output[$catn->id] = 0;
            }
            mtrace('Genegarating wrong answer cos of empty answer');
            $output = (object) $output;
        }

        $newresult = [];
        foreach ($output as $model => $catids) {
            foreach ($catids as $cat => $value) {
                $newresult[$model . "_" . $cat] = $value;
            }
        }

        $feedback = [];

        $question_attempt_id = $question_attempt->id;
        $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);
        mtrace('question_attempt');
        mtrace(json_encode($question_attempt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $quizattempt = $DB->get_record('quiz_attempts', ['uniqueid' => $question_attempt->questionusageid]);
        mtrace('quizattempt');
        mtrace(json_encode($quizattempt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $mlnlpresponseparams = [
                'questionid' => $questionid,
                'questionattemptid' => $question_attempt_id,
                'quizattemptid' => $quizattempt->id,
        ];

        $overriddenpythonresponse = [];
        if ($mlnlpresponse = $DB->get_record('qtype_mlnlpessay_response', $mlnlpresponseparams)) {
            $currentpythonresponse = json_decode($mlnlpresponse->pythonresponse);
            if ($currentpythonresponse) {
                foreach ($currentpythonresponse as $currentpythonresp) {
                    if (isset($currentpythonresp->overridden) && !empty($currentpythonresp->overridden)) {
                        $overriddenpythonresponse[$currentpythonresp->id] = $currentpythonresp->correct;
                    }
                }
            }
        }

        mtrace('Run for each category');
        $sumweghts = 0;
        $totalweight = 0;
        foreach ($categoriesweight as $catid => $category) {
                $catgrade = $newresult[$catid];
                $categoriesweight->$catid->$catgrade = $catgrade;
                $overridden = 0;
                if (isset($overriddenpythonresponse[$catid])) {
                    $catgrade = $overriddenpythonresponse[$catid];
                    $overridden = 1;
                }
                $sumweghts += (int) $category->weight * (int) $catgrade;
                $totalweight += (int) $category->weight;
                $feedback[] = [
                    'name' => $category->name,
                    'id' => $category->id,
                    'sortorder' => $category->sortorder,
                    'type' => $category->type,
                    'correct' => trim($catgrade),
                    'overriden' => $overridden
                ];
            }

        $fraction = number_format((int) $sumweghts / (int) $totalweight, 2);
        mtrace('FRaction: ' . $fraction);
        mtrace('Feedback: ');
        mtrace(print_r($feedback, 1));

        $mlnlpessay_response = new stdClass();
        $mlnlpessay_response->questionid = $questionid;
        $mlnlpessay_response->questionattemptid = $question_attempt_id;
        $mlnlpessay_response->quizattemptid = $quizattempt->id;
        $mlnlpessay_response->pythonresponse = json_encode($feedback, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $mlnlpessay_response->timemodified = time();
        $mlnlpessay_response->timecreated = time();
        mtrace('mlnlpessay_response');
        mtrace(print_r($mlnlpessay_response, 1));

        if ($mlnlpresponse = $DB->get_record('qtype_mlnlpessay_response', $mlnlpresponseparams)) {
            $mlnlpessay_response->id = $mlnlpresponse->id;
            $mlnlpresponseupdated = $DB->update_record('qtype_mlnlpessay_response', $mlnlpessay_response);
            mtrace('qtype_mlnlpessay_response updated');
        } else {
            $inserted = $DB->insert_record('qtype_mlnlpessay_response', $mlnlpessay_response);
            mtrace('qtype_mlnlpessay_response inserted');
        }

        static::regrade_attempt_by_questionattempt($question_attempt_id, $fraction);

        return array($fraction, $feedback);
    }

    public static function regrade_attempt_by_questionattempt($questionattemptid, $fraction) {
        global $DB;

        //update grade for question after giving feedback.
        $question_attempt_step =
                $DB->get_record_select(
                        'question_attempt_steps',
                        'questionattemptid = ? AND fraction IS NOT NULL',
                        [$questionattemptid]);
        mtrace('question_attempt_step');
        mtrace(json_encode($question_attempt_step, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        if ($question_attempt_step) {
            $question_attempt_step->fraction = $fraction;
            $updated = $DB->update_record('question_attempt_steps', $question_attempt_step);
            mtrace('question_attempt_steps updated');
            mtrace(json_encode($updated, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $questionattempt = $DB->get_record('question_attempts', ['id' => $questionattemptid]);
        $quba = \question_engine::load_questions_usage_by_activity($questionattempt->questionusageid);
        mtrace('question_attempt updated');
        mtrace(print_r($questionattempt, 1));

        $quizattempt = $DB->get_record('quiz_attempts', ['uniqueid' => $questionattempt->questionusageid]);
        mtrace('quiz_attempts');
        $student_user = $DB->get_record('user', ['id' => $quizattempt->userid]);
        mtrace('Student (user): ' . fullname($student_user));

        $quizattempt->sumgrades = $quba->get_total_mark();
        $quizattempt->timemodified += 1;
        $updated2 = $DB->update_record('quiz_attempts', $quizattempt);
        mtrace('quiz_attempts updated');

        //DO NOT SAVE HISTORY REGRADED fraction for mlnlpessay question
        $DB->delete_records('quiz_overview_regrades', ['questionusageid' => $questionattempt->questionusageid]);
    }


    static function execute_mlnlp($cattemp, $answertext, $qattemptid, $models_number){
        global $CFG;
        mtrace('AWS Lambda mode');
        $key = get_config('qtype_mlnlpessay', 'aws_labmda_key');
        $secret = get_config('qtype_mlnlpessay', 'aws_labmda_secret');
        $region = get_config('qtype_mlnlpessay', 'aws_labmda_region');
        $functionname = get_config('qtype_mlnlpessay', 'aws_labmda_functionname');
        $payload = '{
                                  "textfilepath": "' . $answertext . '",
                                  "question_attempt": "' . $qattemptid . '",
                                  "categoriesids": ' . json_encode($cattemp) . ',
                                  "model_name": "' . $models_number . '"
                                }';



        $client = \Aws\Lambda\LambdaClient::factory(array(
            'credentials' => array(
                'key' => $key,
                'secret' => $secret,
            ),
            'region' => $region,
            'version' => 'latest',

        ));

        $result = $client->invoke(array(
            'FunctionName' => $functionname,
            'Payload' => $payload,
        ));


        mtrace('Lambda response body');
        $output = json_decode($result['Payload'])->body;
        mtrace(print_r(json_decode($output), 1));
        return  json_decode($output);
    }

    static function preparetext($text) {
        $text = trim(str_replace('&nbsp;', ' ', $text));
        $text = trim(str_replace('"', ' ', $text));
        $text = htmlentities($text, ENT_QUOTES, "UTF-8");
        return $text;
    }
}
