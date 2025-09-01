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
 * Run the diagnostic for selected activities.
 *
 * @package    local_diagnostic
 * @copyright  2021 Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
global $CFG, $DB;

require_once($CFG->libdir.'/clilib.php');      // cli only functions

$usage = "Runs the brad estimation optionally with selected mids only.

Usage:
    # php brad.php [--mids=<mids>[--skip|-s]]
    # php brad.php [--help|-h]

Options:
    -h --help                   Print this help.
    <mids> - repository module ids, separated with comma

Examples:

    # php brad.php
        Does nothing

    # php brad.php --mids=12
        estimates course module id = 12

    # php brad.php --mids=12 -s
       only calculates question number

    # php brad.php --mids=12,13
        estimates two course modules with id = 12 and id = 13

    # php brad.php --help
    # php brad.php -h
        Prints this help
";

list($options, $unrecognised) = cli_get_params(
    [
        'mids' => '',
        'help' => false,
        'skip' => false,
        'time' => null,
    ], [
        'h' => 'help',
        's' => 'skip',
    ]
);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if (isset($options['help']) && $options['help'] !== false) {
    cli_writeln($usage);
    exit(2);
}

$metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
$config = get_config('local_diagnostic');

if (!$mids = explode(',', $options['mids'])) {

    if (!$parentcategoryid = \community_oer\main_oer::get_oer_category()) {
        $parentcategoryid = 0;
    }

    $rows = $DB->get_records_sql('SELECT lm.id, lm.data, lm.instanceid 
                                                FROM {local_metadata} lm 
                                                JOIN {course_modules} cm ON (cm.id = lm.instanceid) 
                                                JOIN {course} c ON (cm.course = c.id) 
                                                JOIN {course_categories} AS cc ON c.category = cc.id
                                                JOIN {modules} AS m ON m.id = cm.module
                                                WHERE cc.id IN (SELECT id FROM {course_categories} WHERE parent = ?) AND m.name = "quiz"
                                                AND lm.fieldid = ? AND lm.data IS NOT NULL AND cm.id IS NOT NULL',
        [$parentcategoryid, $metadatafieldid]);
    mtrace('All mids: ' . count($rows));
    $mids = array_map(function($row){
        return $row->data;
    }, $rows);
} else {
    list($where, $params) = $DB->get_in_or_equal($mids, SQL_PARAMS_NAMED, 'mid');
    $caches = \local_diagnostic\cache::get_records_select('mid ' . $where, $params);
}
$params = [
    'recache' => true,
    'metadatafieldid' => $metadatafieldid,
    /*This is only to supress warnings in get_userdata() function*/
    'hasnewattempts' => false,

];
foreach ($mids as $mid) {
    $params['mid'] = $mid;
    $start = time();
    if (\local_diagnostic_external::has_custom_settings($params['mid'])) {
        $params = array_merge($params, \local_diagnostic_external::get_custom_settings($params['mid']));
    }
    list ($allkeys, $params['qids']) = \local_diagnostic_external::get_qids($params);
    if (!$options['skip']) {
        if (empty($params['qids'])) {
            mtrace('MID: ' . $mid . ' has no associated quizzes');
            continue;
        }

        list($userdata, $enrolled, $activitydata) = \local_diagnostic_external::get_userdata($params, $allkeys);
        if (empty($userdata)) {
            mtrace('MID: ' . $mid . ' has no userdata');
            continue;
        }

        $points = [];

        foreach ($userdata as $userdatakey => $data) {
            foreach ($allkeys as $uniquekey => $allkeysdata) {
                $point = (isset($data['keys'][$uniquekey]['fraction']) && !empty($data['keys'][$uniquekey]['fraction'])) ? $data['keys'][$uniquekey]['fraction'] : 0;
                $points[$userdatakey][$uniquekey] = $point;
            }
        }

        $bradclusters = \local_diagnostic_external::Rcluster($points, [], [], true);
        if (empty($bradclusters)) {
            $bradclusters = [0];
        }
        $allbradclusters = implode(',', $bradclusters);
        $taken = time() - $start;

        mtrace('MID: ' . $mid . ', bradclusters: ' . $allbradclusters . ', questions: ' . count($allkeys) . ', attempts: ' . count($points) . ', seconds taken: ' . $taken);

        $nmax = $config->nmax ?: local_diagnostic_external::NMAX;
        $nmin = $config->nmin ?: local_diagnostic_external::NMIN;

        if (!$bradobj = \local_diagnostic\brad::get_record(['mid' => $mid])) {
            $bradobj = new \local_diagnostic\brad(0);
            $bradobj->set('mid', $mid);
            $bradobj->set('bradclusternum', min($bradclusters));
            $bradobj->set('allbradclusters', $allbradclusters);
            $bradobj->set('bradmin', $nmin);
            $bradobj->set('bradmax', $nmax);
            $bradobj->set('attempts', count($points));
            $bradobj->set('questions', count($allkeys));

            $bradobj->create();
        } else {
            $bradobj->set('bradclusternum', min($bradclusters));
            $bradobj->set('allbradclusters', $allbradclusters);
            $bradobj->set('bradmin', $nmin);
            $bradobj->set('bradmax', $nmax);
            $bradobj->set('attempts', count($points));
            $bradobj->set('questions', count($allkeys));

            $bradobj->update();
        }
    } else {
        mtrace('MID: ' . $mid . ', skip requested, questions: ' . count($allkeys));
        if ($bradobj = \local_diagnostic\brad::get_record(['mid' => $mid])) {
            $bradobj->set('questions', count($allkeys));
            $bradobj->update();
        }
    }
}