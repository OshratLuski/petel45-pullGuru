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
 * Run the code checker from the web.
 *
 * @package    local_codechecker
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_grade.php');

use \local_diagnostic\excel\excel;
use mod_quiz\quiz_attempt;

require_login();


$cmids = required_param('cmids', PARAM_RAW);
$type = required_param('type', PARAM_ALPHA);
$questionid = optional_param('questionid', 1, PARAM_INT);

$cmids = explode(',', $cmids);


if ($cmids) {
    $cmid = reset($cmids);
    $cm = get_coursemodule_from_id('quiz', $cmid);
    $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
    $context = \context_course::instance($cm->course);
    $PAGE->set_context($context);
    require_capability('moodle/grade:viewall', $context);

    try {
        $gradeitem = new \grade_item([
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance
        ]);
    } catch (\Throwable $e) {
        $gradeitem = null;
    }

    if ($gradeitem) {

        $filename = 'excelreport_' .(time()). '.xls';

        $workbook = new excel('-');
        $workbook->send($filename);
        $worksheet = $workbook->add_worksheet($cm->name);
        $worksheet->setRightToLeft(true);
        $boldformat = $workbook->add_format();
        $boldformat->set_bold(true);
        $excelrow = 0;

        $headers = [
            get_string('firstname_excelhdr', 'local_diagnostic'),
            get_string('lastname_excelhdr', 'local_diagnostic'),
            get_string('group_excelhdr', 'local_diagnostic'),
            get_string('grade_excelhdr', 'local_diagnostic'),
            //get_string('gradecluster_excelhdr', 'local_diagnostic'),
            get_string('id_excelhdr', 'local_diagnostic'),
            get_string('email_excelhdr', 'local_diagnostic'),
            get_string('status_excelhdr', 'local_diagnostic'),
            get_string('started_excelhdr', 'local_diagnostic'),
            get_string('finished_excelhdr', 'local_diagnostic'),
            get_string('duration_excelhdr', 'local_diagnostic'),
        ];

        foreach ($headers as $col => $colname) {
            $worksheet->write_string($excelrow, $col++, $colname, $boldformat);
        }
        $params = [
            'cmids' => [$cmid],
            'cmid' => $cmid,
            'type' => $type,
            'filterattempt' => $questionid,
        ];
        $clusters  = \local_diagnostic_external::get_cached_clusters_array($params);

        $clustermapper = $clusterprcs = [];
        if (isset($clusters['clusters']) && !empty($clusters['clusters'])) {
            foreach ($clusters['clusters'] as $clusternum => $clusterdata) {
                $clusterprcsum = $clusterprccount = 0;
                foreach ($clusterdata['users'] as $userid => $userdata) {
                    list($userid2, $qid, $attempt) = explode('-', $userid);
                    $courses = isset($userdata['courses']) && is_array($userdata['courses']) ? $userdata['courses'] : [];
                    if ((in_array($cm->course, $courses) && $attempt == 1) || $type == 'extra') {
                        $clustermapper[$userid] = [
                            'cluster' => $clusternum,
                            'prc' => $userdata['prc'],
                            'user' => \core_user::get_user($userdata['user']['id'])
                        ];
                        if ($clusternum > 0) {
                            $clusterprccount++;
                            $clusterprcsum += $userdata['prc'];
                            $clusterprcs[$clusternum] = round($clusterprcsum / $clusterprccount, 2);
                        }
                    }
                }
            }
        }

        foreach ($clustermapper as $userid => $clustermapperdata) {
            $excelrow++;
            $col = 0;
            $heb_group_names = explode(',', get_string('alphabet','langconfig'));
            $cluster_heb_group = get_string('clustername', 'local_diagnostic', $heb_group_names[$clustermapperdata['cluster']-1]);
            $row = [
                'firstname' => $clustermapperdata['user']->firstname,
                'lastname' => $clustermapperdata['user']->lastname,
                'group' => isset($clustermapperdata['cluster']) && $clustermapperdata['cluster'] > 0 ? $cluster_heb_group : '-',
                'grade' => '-',
                //'gradecluster' => isset($clustermapper[$userid]['prc']) && $clustermapper[$userid]['prc'] > 0 ? $clustermapper[$userid]['prc'] : '-',
                'id' => $clustermapperdata['user']->idnumber ?: '-',
                'email' => $clustermapperdata['user']->email ?: '-',
                'status' => '-',
                'started' => '-',
                'finished' => '-',
                'duration' => '-',
            ];

            $attempt = $DB->get_record_select('quiz_attempts', ' userid = ? AND quiz = ? ORDER BY timefinish DESC LIMIT 1', [$userid, $cm->instance]);
            if ($attempt) {
                $attemptobj = quiz_attempt::create($attempt->id);

                switch ($attemptobj->get_state()) {
                    case quiz_attempt::IN_PROGRESS:
                        $state = get_string('stateinprogress', 'quiz');
                        break;
                    case quiz_attempt::OVERDUE:
                        $state = get_string('stateoverdue', 'quiz');
                        break;
                    case quiz_attempt::FINISHED:
                        $state = get_string('statefinished', 'quiz');
                        break;
                    case quiz_attempt::ABANDONED:
                        $state = get_string('stateabandoned', 'quiz');
                        break;
                    default:
                        $state = '';
                }

                if ($type == 'mid') {
                    $grade = new \grade_grade([
                        'itemid' => $gradeitem->id,
                        'userid' => $userid
                    ]);

                    $row['grade'] = number_format($grade->finalgrade, 0);
                } elseif ($type == 'extra') {
                    $grade = $DB->get_field('question_attempts', 'maxmark', ['questionusageid' => $attempt->uniqueid, 'questionid' => $questionid]);
                    $row['grade'] = $grade !== false ? number_format($grade, 2) : '-';
                }
                $row['status'] = $state;
                $row['started'] = date('d/m/Y H:i', $attempt->timestart);
                $row['finished'] = date('d/m/Y H:i', $attempt->timefinish);
                if ($attempt->timefinish) {
                    $row['duration'] =format_time($attempt->timefinish - $attempt->timestart);
                }
            }


            $worksheet->write_string($excelrow, $col++, $row['lastname']);
            $worksheet->write_string($excelrow, $col++, $row['firstname']);
            $worksheet->write_string($excelrow, $col++, $row['group']);
            $worksheet->write_string($excelrow, $col++, $row['grade']);
            //$worksheet->write_string($excelrow, $col++, $row['gradecluster']);
            $worksheet->write_string($excelrow, $col++, $row['id']);
            $worksheet->write_string($excelrow, $col++, $row['email']);
            $worksheet->write_string($excelrow, $col++, $row['status']);
            $worksheet->write_string($excelrow, $col++, $row['started']);
            $worksheet->write_string($excelrow, $col++, $row['finished']);
            $worksheet->write_string($excelrow, $col++, $row['duration']);
        }

        if (!empty($clusterprcs)) {
            // Add group info
            $worksheet_info = $workbook->add_worksheet(get_string('cluster_info', 'local_diagnostic'));
            $inforow = 1;
            $heb_group_names = explode(',', get_string('alphabet','langconfig'));
            foreach ($clusterprcs as $clusternum => $clusterprc) {
                $worksheet_info->write_string($inforow, 1, get_string('excelcluster', 'local_diagnostic', $heb_group_names[$clusternum-1]));
                $worksheet_info->write_string($inforow++, 3, strip_tags(get_string('avggrade', 'local_diagnostic', $clusterprc) . " " . get_config('local_diagnostic', 'cluster' . $clusternum . 'descriptionactivity' . $mid)));
            }
        }

        $workbook->close();

        // Trigger event, excel exported.
        $context = context_module::instance($cmid);

        $eventparams = [
            'relateduserid' => $USER->id,
            'context' => $context,
            'other' => [
                'cmid' => $cmid
            ]
        ];

        $event = \local_diagnostic\event\excel_exported::create($eventparams);
        $event->trigger();
    } else {
        throw new \moodle_exception(get_string('nogradeitem', 'local_diagnostic'), 'local_diagnostic');
    }
} else {
    throw new \moodle_exception(get_string('nocmids', 'local_diagnostic'), 'local_diagnostic');
}