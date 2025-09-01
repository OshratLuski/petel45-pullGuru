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
 * Plugin general functions are defined here.
 *
 * @package     qtype_essayrubric
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function qtype_essayrubric_get_indicators() {
    global $DB;

    $indicators = $DB->get_records("qtype_essayrubric_ind", ['deleted' => 0]);

    return $indicators;
}

function qtype_essayrubric_update_indicators($indicators) {
    global $DB;

    $existingindicators = qtype_essayrubric_get_indicators();

    foreach ($indicators as $key => $indicator) {
        if (isset($indicator['id']) && $DB->get_record('qtype_essayrubric_ind', ['id' => $indicator['id']])) {
            $DB->update_record('qtype_essayrubric_ind', $indicator);
        } elseif (isset($indicator['indicatorid'])) {
            if ($samename = $DB->get_record_sql('SELECT *
            FROM {qtype_essayrubric_ind}
            WHERE `indicatorid` = ?
            ', [$indicator['indicatorid']], IGNORE_MISSING)) {
                $indicator['model'] = !isset($indicator['model']) ? '' : $indicator['model'];
                $indicator['id'] = $samename->id;
                $indicators[$key]['id'] = $samename->id;
                $DB->update_record('qtype_essayrubric_ind', $indicator);
            } else {
                $indicator['model'] = !isset($indicator['model']) ? '' : $indicator['model'];
                $indicators[$key]['id'] = $DB->insert_record('qtype_essayrubric_ind', $indicator);
            }
        }
    }

    foreach ($existingindicators as $existingindicator) {
        $found = false;
        foreach ($indicators as $indicator) {
            if ($existingindicator->id == $indicator['id']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $DB->delete_records('qtype_essayrubric_ind', ['id' => $existingindicator->id]);
        }
    }

    return true;
}

function qtype_essayrubric_get_available_indicators() {
    global $DB;

    $indicators = $DB->get_records("qtype_essayrubric_ind", ['visible' => 1, 'deleted' => 0]);

    return $indicators;
}

function qtype_essayrubric_get_question_indicators($qid = null) {
    global $DB;

    if (is_null($qid)) {
        return [1, [], 0];
    }

    $existqinds = $DB->get_field('qtype_essayrubric_options', 'indicators', array('questionid' => $qid));

    $existqinds = qtype_essayrubric_parse_ind_options_json($existqinds);

    $indicators = $existqinds->indicatorlist;

    // Get isgradestypescalar, check and set default if needed.
    if (!isset($existqinds->isgradestypescalar)) {
        $existqinds->isgradestypescalar = 1;
    }
    // Get researchquestion, check and set default if needed.
    if (!isset($existqinds->researchquestion)) {
        $existqinds->researchquestion = 0;
    }

    return [$existqinds->isgradestypescalar, $indicators, $existqinds->researchquestion];
}

function qtype_essayrubric_prepare_ind_options_json($isgradestypescalar, $questionindicatorfulltable, $researchquestion = false) {
    $indicatorsoptions = new stdClass();
    $indicatorsoptions->isgradestypescalar = $isgradestypescalar;
    $indicatorsoptions->researchquestion = $researchquestion;
    $indicatorsoptions->indicatorlist = json_decode($questionindicatorfulltable);

    $indicatorsoptions = json_encode($indicatorsoptions);

    return $indicatorsoptions;
}

function qtype_essayrubric_parse_ind_options_json($json) {
    $indicatorsoptions = json_decode($json);

    return $indicatorsoptions;
}

function qtype_essayrubric_store_grades($data, $qaid) {
    global $DB;

    $result = false;

    $qa = $DB->get_record('question_attempts', ['id' => $qaid]);
    $time = time();

    foreach ($data->indicatorlist as $key => $indicator) {

        $indicator->qindid = $indicator->id;

        $responsedata = [
            'questionattemptid' => $qaid,
            'questionid' => $qa->questionid,
            'quizattemptid' => 0,
            'question' => $qa->questionsummary,
            'answer' => $qa->responsesummary,
            'timecreated' => $time,
            'timemodified' => $time,
            'isgradestypescalar' => $data->isgradestypescalar,
            'weight' => $indicator->weight,
            'indicatorid' => $indicator->indicatorid,
            'name' => $indicator->name,
            'type' => $indicator->type,
            'qindid' => $indicator->qindid,
            'grade' => strval($data->grade),
            'checked' => $indicator->checked,
            'normalizedweight' => $indicator->normalizedWeight,
            'weightedgrade' => $indicator->weightedGrade,
            'maxmark' => $data->maxmark,
            'minfraction' => $data->minfraction,
            'maxfraction' => $data->maxfraction,
            'usageid' => $data->usageid,
            'slot' => $data->slot,
        ];

        $resp = $DB->get_record('qtype_essayrubric_resp', ['questionattemptid' => $qaid, 'qindid' => $indicator->qindid]);
        if ($resp) {
            $responsedata['id'] = $resp->id;
            $result = $DB->update_record('qtype_essayrubric_resp', $responsedata);
        } else {
            $result = $DB->insert_record('qtype_essayrubric_resp', $responsedata, false);
        }

    }

    return $result;
}

function qtype_essayrubric_get_usedindicators() {
    global $DB;

    $sql = "SELECT DISTINCT
                qv.questionid
            FROM
                {question_versions} qv
            JOIN {question_references} qr ON qv.questionbankentryid = qr.questionbankentryid
            JOIN {quiz_slots} qs ON qr.itemid = qs.id
            JOIN {question} q ON q.id = qv.questionid
            WHERE
                q.qtype = 'essayrubric'
                AND(qr.version IS NULL
                AND qv.version = (
                    SELECT
                        MAX(version)
                        FROM {question_versions} qv2
                    WHERE
                        qv2.questionbankentryid = qr.questionbankentryid)
                    OR qr.version IS NOT NULL
                    AND qv.version = qr.version)";

    $questionids = $DB->get_records_sql($sql);
    $usedindicators = [];

    foreach ($questionids as $questionid) {
        $options = $DB->get_record('qtype_essayrubric_options', ['questionid' => $questionid->questionid]);

        if ($options && !empty($options->indicators)) {
            $indicators = json_decode($options->indicators);

            if (isset($indicators->indicatorlist) && is_array($indicators->indicatorlist)) {
                foreach ($indicators->indicatorlist as $indicator) {
                    if ($indicator && isset($indicator->indicatorid)) {
                        $usedindicators[] = $indicator->indicatorid;
                    }
                }
            }
        }
    }

    return $usedindicators;
}

function qtype_essayrubric_is_student() {
    global $USER, $COURSE;

    $result = false;
    $context = context_course::instance($COURSE->id);
    $capability = 'moodle/course:update';
    if (!has_capability($capability, $context, $USER->id)) {
        $result = true;
    }

    return $result;
}

function qtype_essayrubric_get_grades($qid, $qaid, $props = []) {
    global $DB;

    $ind = $DB->get_record('qtype_essayrubric_options',
        array('questionid' => $qid), 'indicators');

    if ($data = qtype_essayrubric_parse_ind_options_json($ind->indicators)) {

        $atleastoneindicator = false;

        foreach ($data->indicatorlist as $key => $value) {

            $resp = null;

            if ($existresponse = $DB->get_record('qtype_essayrubric_resp', ['questionattemptid' => $qaid, 'qindid' => $key])) {
                $resp = $existresponse;
                $atleastoneindicator = isset($existresponse) ? true : $atleastoneindicator;
            }

            $value->id = $key;
            $value->checked = $resp ? (int) $resp->checked : 0;
            if ($data->isgradestypescalar) {
                $value->selected0 = 0;
                $value->selected1 = 0;
                $value->selected2 = 0;
                $value->selected3 = 0;
                $value->selected4 = 0;
                $value->selected5 = 0;
                $propertyName = "selected{$value->checked}";
                $value->$propertyName = 1;
            } else {
                $value->checked = $value->checked;
            }

            $value->type = isset($value->type) ? get_string($value->type, 'qtype_essayrubric') : '';

            $data->indicatorlist[$key] = $value;
        }
        $data->indicators = array_values($data->indicatorlist);
        $data->qaid = $qaid;

        $data->isstudent = $props[0];

        return [$data, $atleastoneindicator];
    }
}

// PTL-11224 QUESTIONREPORT
function qtype_essayrubric_get_questionattempts_w_categories($questionid, $limit = 20, $offset = 0, $sort = 'ASC', $col = '',
    $search = '') {
    global $DB, $CFG, $OUTPUT;

    require_once ($CFG->dirroot . '/question/engine/lib.php');
    require_once ($CFG->dirroot . '/question/type/questiontypebase.php');
    require_once ($CFG->dirroot . '/mod/quiz/report/advancedoverview/classes/quizdata.php');
    require_once ($CFG->dirroot . '/question/type/essayrubric/locallib.php');

    $sql = 'SELECT
            q.id,
            q.*,
            q.createdby,
            qc.contextid
        FROM {question} q
        JOIN {question_versions} qv
        ON qv.questionid = q.id
        JOIN {question_bank_entries} qbe
        ON qbe.id = qv.questionbankentryid
        JOIN {question_categories} qc
        ON qc.id = qbe.questioncategoryid
        WHERE q.id = :id';

    $question = $DB->get_record_sql($sql, ['id' => $questionid]);

    $col = $col ? $col : 'qattid';
    $sort = $sort ? $sort : 'ASC';
    $search = !empty($search) ? '%' . $search . '%' : '';

    $params = [];
    $params['questionid'] = $questionid;
    $params['search'] = $search;
    $params['search2'] = $search;
    $params['search3'] = $search;
    $params['search4'] = $search;

    $sql = "SELECT
            qatt.id AS qattid,
            quizatt.userid AS quizattuserid,
            quizatt.attempt AS quizattattempt,
            q.id AS qid,
            qatt.questionsummary AS qattquestionsummary,
            qatt.responsesummary AS qattresponsesummary,
            quizatt.quiz AS quizattquiz
        FROM
            {question_attempts} qatt
        JOIN {quiz_attempts} quizatt ON qatt.questionusageid = quizatt.uniqueid
        JOIN {quiz_slots} quizslots ON quizslots.slot = qatt.slot AND quizslots.quizid = quizatt.quiz
        JOIN {question_references} qr ON qr.itemid = quizslots.id
        JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
        JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
        JOIN {question} q ON q.id = qv.questionid
        JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
        WHERE q.id = :questionid";

    if (!empty($search)) {
        $sql .= " AND (qatt.id LIKE :search OR quizatt.userid LIKE :search2 OR quizatt.userid LIKE :search3 OR qatt.responsesummary LIKE :search4)";
    }

    $sql .= " ORDER BY $col $sort";

    $attempts = $DB->get_records_sql($sql, $params, $offset, $limit);
    $total = count($DB->get_records_sql($sql, $params));

    $sql = "SELECT
            q.id AS qid,
            qatt.questionsummary AS qattquestionsummary,
            quizatt.quiz AS quizattquiz
        FROM
            {question_attempts} qatt
        JOIN {quiz_attempts} quizatt ON qatt.questionusageid = quizatt.uniqueid
        JOIN {quiz_slots} quizslots ON quizslots.slot = qatt.slot AND quizslots.quizid = quizatt.quiz
        JOIN {question_references} qr ON qr.itemid = quizslots.id
        JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
        JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
        JOIN {question} q ON q.id = qv.questionid
        WHERE q.id = ?";

    $questioninfo = $DB->get_record_sql($sql, [$questionid], IGNORE_MULTIPLE);

    if(!$questioninfo) {
        $questioninfo = new stdClass();
        $questioninfo->qid = $questionid;
        $questioninfo->qattquestionsummary = strip_tags($question->questiontext);
    }

    $coltitles = [
        'qattid' => get_string('qattid', 'qtype_essayrubric'),
        'quizattuserid' => get_string   ('quizattuserid', 'qtype_essayrubric'),
        'quizattattempt' => get_string('quizattattempt', 'qtype_essayrubric'),
        'qattresponsesummary' => get_string('qattresponsesummary', 'qtype_essayrubric'),
    ];

    $sortcolumnsids = $coltitles;

    // Get categories
    $questioncategories = $DB->get_field('qtype_essayrubric_options', 'indicators ', [
        'questionid' => $questionid,
    ]);
    $questioncategories = (array) json_decode($questioncategories);

    $categoryprefix = "category";
    $defaultcategoriesdata = [];
    foreach ($questioncategories['indicatorlist'] as $key => $category) {
        $defaultcategoriesdata[$categoryprefix . $key] = $category->name;
    }

    $coltitles += $defaultcategoriesdata;

    $categorykeys = array_keys($defaultcategoriesdata);

    foreach ($attempts as $attkey => $attempt) {
        // Merge attempt with categorykeys
        $attempt = (object) array_merge((array) $attempt, array_fill_keys($categorykeys, '—'));

        list($gradedata, $atleastoneindicator) = qtype_essayrubric_get_grades($questionid, $attempt->qattid, [false]);

        foreach ($gradedata->indicators as $indkey => $indicator) {
            $categorycolid = $categoryprefix . $indkey;

            $indicator->isgradestypescalar = $gradedata->isgradestypescalar;

            $gradeindicator = $OUTPUT->render_from_template('qtype_essayrubric/grade_indicator', $indicator);

            $attempt->{$categorycolid} = $gradeindicator;
        }

        unset($attempt->qid);
        unset($attempt->qattquestionsummary);
        unset($attempt->quizattquiz);
        $attempts[$attkey] = array_values((array) $attempt);
    }

    $data = new stdClass();
    $data->attempts = array_values($attempts);
    $data->coltitles = array_values($coltitles);
    $data->questioninfo = $questioninfo;
    $data->question = $question;
    $data->sortcolumnsids = array_flip($sortcolumnsids);

    $dif = $limit != 0 ? ceil($total / $limit) : 0;
    $data->last_page = $total == 0 ? 0 : $dif;

    return $data;

}

function qtype_essayrubric_get_courseid($questionid) {
    global $DB;

    // First query: Check if there are quiz attempts associated with the question
    $sql = "SELECT
                quizatt.quiz AS quizid,
                quiz.course AS courseid
            FROM
                {question_attempts} qatt
            JOIN {quiz_attempts} quizatt ON qatt.questionusageid = quizatt.uniqueid
            JOIN {quiz_slots} quizslots ON quizslots.slot = qatt.slot AND quizslots.quizid = quizatt.quiz
            JOIN {quiz} quiz ON quiz.id = quizatt.quiz
            JOIN {question_references} qr ON qr.itemid = quizslots.id
            JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
            JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
            JOIN {question} q ON q.id = qv.questionid
            WHERE q.id = :questionid";

    $quiz = $DB->get_record_sql($sql, ['questionid' => $questionid]);

    // If no quiz attempts found, check for quizzes without attempts
    if (!$quiz) {
        $sql = "SELECT
                    quiz.id AS quizid,
                    quiz.course AS courseid
                FROM
                    {quiz_slots} quizslots
                JOIN {quiz} quiz ON quiz.id = quizslots.quizid
                JOIN {question_references} qr ON qr.itemid = quizslots.id
                JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                JOIN {question} q ON q.id = qv.questionid
                WHERE q.id = :questionid";

        $quiz = $DB->get_record_sql($sql, ['questionid' => $questionid]);
    }

    // Default to course ID 1 if no course is found
    $courseid = $quiz ? $quiz->courseid : 1;


    return $courseid;
}

