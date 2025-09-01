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
 * Serve question type files
 *
 * @since      Moodle 2.0
 * @package    qtype_mlnlpessay
 * @copyright  Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

function qtype_mlnlpessay_before_http_headers() {
    global $PAGE;
    $url = new \moodle_url('/admin/settings.php', ['section' => 'qtypesettingmlnlpessay']);

    if ($PAGE->url->compare($url)) {
        $PAGE->requires->css('/question/type/mlnlpessay/css/tabulator.css');
    }
}

/**
 * Checks file access for essay questions.
 *
 * @package  qtype_mlnlpessay
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @package  qtype_mlnlpessay
 * @category files
 */
function qtype_mlnlpessay_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_mlnlpessay', $filearea, $args, $forcedownload, $options);
}

function qtype_mlnlpessay_get_results($quizid, $questionid, $timefinish = 0) {
    global $DB;
    $return = [];

    $categories = get_enabled_categories($questionid);
    foreach ($categories as $cat) {
        $return[$cat->id] = $cat;
        $return[$cat->id]->description = get_config('qtype_mlnlpessay', 'category' . ($cat->id + 1) . 'description');
    }

    $params = ['questionid' => $questionid, 'quizid' => $quizid];

    $timefinishsql = '';
    if ($timefinish) {
        $timefinishsql = ' AND qa.timefinish < :timefinish';
        $params['timefinish'] = $timefinish;
    }

    $sql = "SELECT qas.id as qasid, mlnr.id,mlnr.pythonresponse,att.`maxfraction`,qas.fraction, qas.userid, qa.timestart, qa.timefinish
            FROM {question_attempt_steps}  qas
            JOIN {question_attempts}  att ON qas.questionattemptid = att.id
            JOIN {question}  que ON att.questionid = que.id
            JOIN {quiz_attempts}  qa ON (qa.uniqueid = att.questionusageid)
            JOIN {quiz}  q ON (qa.quiz = q.id)
            JOIN {qtype_mlnlpessay_response}  mlnr ON (mlnr.questionid = que.id AND mlnr.questionattemptid = att.id AND qa.id=mlnr.quizattemptid)
            WHERE 
            q.id= :quizid
            AND que.qtype = 'mlnlpessay'
            AND que.stamp IS NOT NULL
            AND que.length > 0
            AND (qa.state = 'finished' OR qa.state = 'gaveup')
            AND qa.preview = 0 
            AND que.id = :questionid
            $timefinishsql
            ORDER BY qas.id ASC";

    $request = $DB->get_records_sql($sql, $params);

    $timetaken = $grades = [];

    foreach ($request as $result) {
        $response = json_decode($result->pythonresponse);
        $timetaken[$result->userid] = $result->timefinish - $result->timestart;
        foreach ($response as $category) {
            if (!isset($grades[$category->id])) {
                $grades[$category->id] = new \stdClass();
                $grades[$category->id]->name = $category->name;
            }
            $grades[$category->id]->grades[$result->userid] = $category->correct ?: 0;
        }
    }

    return [
        'timetaken' => $timetaken,
        'grades' => $grades,
    ];
}

function qtype_mlnlpessay_output_fragment_settingsform(array $args): string {
    global $CFG;

    $action = $args['action'];
    $mformclassname = '\qtype_mlnlpessay\form\\' . $action . '_form';
    $mform = new $mformclassname();

    $id = $args['id'];
    $persistentclassname = '\qtype_mlnlpessay\persistent\\' . $action;
    if ($persistent = $persistentclassname::get_record(['id' => $id])) {
        $record = $persistent->to_record();
        foreach (['topic', 'subtopic'] as $field) {
            $$field = [];
            $catpersistentname = $field . 's';
            $classname = 'qtype_mlnlpessay\persistent\\categories_' . $catpersistentname;
            foreach ($classname::get_records(['categoryid' => $record->id]) as $catpersistent) {
                $$field[] = $catpersistent->get($field . 'id');
            }
            $record->$catpersistentname = $$field;
        }
        $mform->set_data($record);
    }

    return $mform->render();
}

function qtype_mlnlpessay_output_fragment_csvuploadform(array $args): string {
    global $CFG;

    $mform = new \qtype_mlnlpessay\form\csvupload_form;
    return $mform->render();
}