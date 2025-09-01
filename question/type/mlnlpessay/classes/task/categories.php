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

namespace qtype_mlnlpessay\task;

/**
 * Class llm_feedback
 *
 * @package    qtype_mlnlpessay
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('categories', 'qtype_mlnlpessay');
    }

    /**
     * Run task for getting llm feedback
     */
    public function execute() {
        global $DB;
        mtrace('AWS Lambda mode');
        $key = get_config('qtype_mlnlpessay', 'aws_labmda_key');
        $secret = get_config('qtype_mlnlpessay', 'aws_labmda_secret');
        $region = get_config('qtype_mlnlpessay', 'aws_labmda_region');
        $functionname = 'mlnlp-map-model';

        $payload = '{}';

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

        $response = json_decode($result['Payload']->getContents())->body;
        $data = str_getcsv($response, "\n"); //parse the rows
        $headerskip = true;

        foreach($data as &$row) {
            if ($headerskip) {
                $headerskip = false;
                continue;
            }
            $parsedrow = str_getcsv($row);
            $persistentdata = [
                'modelid' => $parsedrow[2],
                'model' => $parsedrow[0],
                'tag' => $parsedrow[1],
            ];

            if (empty($parsedrow[2]) || in_array($parsedrow[2], ['.locks'])) {
                continue;
            }
            $existingids = [];
            if ($persistent = \qtype_mlnlpessay\persistent\categories::get_record(['modelid' => $persistentdata['modelid'], 'model' => $persistentdata['model']])) {
                $persistent->set('tag', $persistentdata['tag']);
                $persistent->update();
                $existingids[] = $persistent->get('id');
            } else {
                $persistent = new \qtype_mlnlpessay\persistent\categories(0, (object) $persistentdata);
                $persistent->create();
            }
        }

    }
}
