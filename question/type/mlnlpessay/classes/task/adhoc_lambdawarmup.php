<?php

namespace qtype_mlnlpessay\task;

defined('MOODLE_INTERNAL') || die();

class adhoc_lambdawarmup extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'qtype_mlnlpessay';
    }

    public function execute() {
        global $DB,$CFG;

        $stopwarmup = 60 * 60;
        $sleeptime = 20;
        $cache = \cache::make('qtype_mlnlpessay', 'quizlambdawarmup');
        $inprocess = $cache->get('inprocess');
        if (empty($inprocess) || $inprocess == 0) {
            $start = time();
            while (true) {
                //Clearing failed ad hoc task
                $DB->delete_records_select('task_adhoc', 'faildelay > ? and classname = ? ',
                        [0, '\qtype_mlnlpessay\task\adhoc_graderesponse']);
                $cache->set('inprocess', 1);
                $cache->set('started', 1);

                if (time() - $start > $stopwarmup) {
                    mtrace('Lambda Stop warmup');
                    $cache->set('inprocess', 0);
                    $cache->delete('started', 0);
                    break;
                }

                mtrace('Lambda Start');
                $key = get_config('qtype_mlnlpessay', 'aws_labmda_key');
                $secret = get_config('qtype_mlnlpessay', 'aws_labmda_secret');
                $region = get_config('qtype_mlnlpessay', 'aws_labmda_region');
                $functionname = get_config('qtype_mlnlpessay', 'aws_labmda_functionname');
                $cattemp = ["C1","C2"];
                $payload = '{
                                  "textfilepath": "text text",
                                  "question_attempt": "1",
                                  "categoriesids": ' . json_encode($cattemp) . ',
                                  "model_name": "AlephBert"
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
                mtrace("Sleep for " . $sleeptime . " seconds");
                sleep($sleeptime);
            }
        }
    }
}
