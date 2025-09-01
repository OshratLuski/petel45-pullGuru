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
 * Export quiz modules response matrix, with student's attempts calculations.
 *
 * @package    local_diagnostic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

$cmids = required_param('activities', PARAM_RAW);
$questionid = optional_param('questionid', 0, PARAM_INT);

$cmids = explode(',', $cmids);
$params = [
    'cmids' => $cmids,
    'cmid' => array_shift($cmids),
    'type' => $questionid > 0 ? 'extra' : 'mid',
    'filterattempt' => $questionid,
];
$excludedqids = get_config('local_diagnostic','activity' . $params['cmid'] .'exludedquestionids');
$excludedkeys = [];
if ($excludedqids){
    $qexcluded = $DB->get_records_sql('SELECT id, stamp from {question} where id in (' . $excludedqids . ')');
    foreach ($qexcluded as $q){
        $excludedkeys[] = $q->stamp;
    }
}

header("Content-type: text/csv");
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="response-matrix.csv"');
$fp = fopen('php://output', 'w');
$header = false;
$headerkeys = [];
if ($params['type'] == 'mid') {
    $clusters = \local_diagnostic_external::get_cached_clusters_array($params);
    if ($clusters['clusters']) {
        $clusterdata = $clusters['clusters'];
        unset($clusterdata[0]);

        foreach ($clusterdata as $clusternum => $clusterinfo) {
            foreach ($clusterinfo['users'] as $userdatakey => $data) {
                $activitycmids = [];
                $duration = '-';
                foreach ($data['activities'] as $activity) {
                    $activitycmids[] = $activity['cmid'];
                    if ($activity['first']) {
                        $duration = round($activity['timetaken'] / 60);
                    }
                }
                $valuearr = [implode(',', $data['courses']), implode(',', $activitycmids), $data['user']['id']];
                foreach ($data['keys'] as $key => $value) {
                    $temp_key = substr($key, strpos($key, '-') + 1, strlen($key) - 1);
                    if (!in_array($temp_key, $excludedkeys)) {
                        if (is_array($value) && isset($value['fraction'])) {
                            $value = $value['fraction'];
                        }
                        $valuearr[] = $value;
                        if (!$header) {
                            $question = $DB->get_record('question', ['stamp' => $temp_key]);
                            $headerkeys[] = $question->name; //$key;
                        }
                    }
                }
                $valuearr[] = $clusternum;
                $valuearr[] = $data['toclusters'];
                $valuearr[] = $duration;
                if (!$header) {
                    $headerarr = [get_string('courseids', 'local_diagnostic'), get_string('activityids', 'local_diagnostic'),
                        get_string('userid', 'local_diagnostic')];
                    foreach ($headerkeys as $key) {
                        $headerarr[] = $key;
                    }

                    $headerarr[] = get_string('cluster', 'local_diagnostic');
                    $headerarr[] = get_string('iskmeans', 'local_diagnostic');
                    $headerarr[] = get_string('duration_excelhdr', 'local_diagnostic');
                    fputcsv($fp, $headerarr);
                    $header = true;
                }
                fputcsv($fp, $valuearr);
            }
        }
    }
} else if ($params['type'] == 'extra') {
    $params['metadatafieldid'] = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
    $mids = \local_diagnostic_external::get_mids($params['metadatafieldid'], $params['cmids']);

    $mid = array_shift($mids);

    if ($record = \local_diagnostic\cache::get_record(['mid' => $mid])) {
        $extra = json_decode($record->get('extra'), true);
        $clustermapper = [];

        if ($extra[$questionid]) {
            $extra = $extra[$questionid];
            foreach ($extra['clusters'] as $quizid => $clusterdata) {
                foreach ($clusterdata as $clusternum => $cluster) {
                    foreach ($cluster['users'] as $userid => $userdata) {
                        if (!isset($clustermapper[$userid]) ||
                            ($clustermapper[$userid]['clusternum'] == 0 && $clusternum > 0)) {
                            $clustermapper[$userid] = [
                                'keys' => $userdata['keys'],
                                'user' => $userdata['user'],
                                'clusternum' => $clusternum
                            ];
                        }
                    }
                }
            }

            foreach ($clustermapper as $userid => $data) {
                if (!$header) {
                    $headerarr = [get_string('userid', 'local_diagnostic')];
                    foreach ($extra['categorynames'] as $catname) {
                        $headerarr[] = $catname;
                    }
                    $headerarr[] = get_string('cluster', 'local_diagnostic');
                    $headerarr[] = get_string('duration_excelhdr', 'local_diagnostic');
                    fputcsv($fp, $headerarr);
                    $header = true;
                }
                $valuearr = [$data['user']['id']];
                foreach ($data['keys'] as $grade) {
                    $valuearr[] = $grade;
                }
                $valuearr[] = $data['clusternum'];
                $valuearr[] = round($data['user']['timetaken'] / 60);
                fputcsv($fp, $valuearr);
            }
        }
    }
}

fclose($fp);