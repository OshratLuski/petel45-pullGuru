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
 * @package     qtype_mlnlpessay
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_category_types() {
    $settings = get_config('qtype_mlnlpessay');

    $texttypes = preg_split("/\r\n|\n|\r/", $settings->categorytypes);

    $types = [];
    $prefix = 'categorytype';

    $i = 1;
    foreach ($texttypes as $key => $type) {
        if ($type == '') {
            continue;
        }
        $types[$prefix . $i] = $type;
        $i++;
    }

    $types[''] = '—';

    return $types;
}

function hascapedit($courseid, $userid) {

    if (is_siteadmin()) {
        return true;
    }

    $context = context_course::instance($courseid);
    $editroles = array('manager');
    foreach (get_user_roles($context, $userid) as $item) {
        if (in_array($item->shortname, $editroles)) {
            return true;
        }
    }

    return false;
}

function check_response($questionid, $questionattemptid) {
    global $DB;
    $response = null;

    $response = $DB->get_record('qtype_mlnlpessay_response',
        array('questionid' => $questionid, 'questionattemptid' => $questionattemptid), '*');

    return $response;
}

function get_response($pythonfeedbacksql, $questiondata = []) {
    global $DB, $OUTPUT, $COURSE;
    $response = null;
    $pythonfeedback = (array) json_decode($pythonfeedbacksql->pythonresponse);
    //TODO ADD TO CACHE qtype_mlnlpessay_categories
    $allcategories = $DB->get_records('qtype_mlnlpessay_categories');
    $cat = [];
    foreach ($allcategories as $row) {
        $cat[$row->model . "_" . $row->modelid] = (array) $row;
    }
    $categories = [];
    $types = get_category_types();

    foreach ($pythonfeedback as $key => $category) {
        $category->type = isset($category->type) ? $types[$category->type] : '';
        $category->name = $cat[$category->id]['name'];
        $context = \context_course::instance($COURSE->id);
        $category->description = format_text($cat[$category->id]['description'], FORMAT_HTML, ['context' => $context]);
        $categories[] = $category;
    }

    $data = new stdClass;
    $data->categories = $categories;
    $data->qid = $questiondata['qid'];
    $data->questionattemptid = $pythonfeedbacksql->questionattemptid;
    $data->questionid = $pythonfeedbacksql->questionid;
    $cmid = $DB->get_field_sql("SELECT cm.id FROM {course_modules} cm
                                        JOIN {modules} m ON cm.module = m.id
                                        JOIN {quiz} q ON cm.instance = q.id
                                        JOIN {quiz_attempts} qa ON q.id = qa.quiz
                                        WHERE m.name = ? AND qa.id = ?",
        ['quiz', $pythonfeedbacksql->quizattemptid]);

    $data->showoverridden = $cmid ? has_capability('qtype/mlnlp:edit', \context_module::instance($cmid)) : false;

    $truefeedback = html_writer::start_tag('div',
        ['class' => 'mlnlpessay-container-' . $pythonfeedbacksql->questionid . $pythonfeedbacksql->questionattemptid]);     

    $truefeedback .= $OUTPUT->render_from_template('qtype_mlnlpessay/responsetable', $data);
    $truefeedback .= html_writer::end_tag('div');

    $response = $truefeedback;

    return $response;
}

function get_enabled_categories($questionid) {
    global $DB;

    $return = [];

    if ($question = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $questionid))) {
        $categories = $question->categoriesweightteacher ? json_decode($question->categoriesweightteacher) :
        json_decode($question->categoriesweight);
        foreach ($categories as $cat) {
            if ($cat->iscategoryselected) {
                $return[$cat->id] = $cat;
            }
        }
    }

    return $return;
}

/**
 * @param $event
 * @return false|void
 * @throws coding_exception
 * @throws dml_exception
 */
function lambdawarmup($event) {
    global $DB, $CFG;
    require_once ($CFG->dirroot . '/mod/quiz/locallib.php');
    $quizid = isset($event->get_data()['other']['quizid']) ? $event->get_data()['other']['quizid'] : 0;
    if (empty($quizid)) {
        return;
    }

    $quizobj = \mod_quiz\quiz_settings::create($quizid);
    $quizobj->preload_questions();
    $quizobj->load_questions();
    $questions = $quizobj->get_questions();
    $hasmlnlp = false;
    foreach ($questions as $question) {
        if ($question->qtype == 'mlnlpessay') {
            $hasmlnlp = true;
        }
    }

    if (!$hasmlnlp) {
        return false;
    }

    // Initial cache.
    $cache = \cache::make('qtype_mlnlpessay', 'quizlambdawarmup');
    $started = $cache->get('started');
    if (empty($started)) {
        $task = new \qtype_mlnlpessay\task\adhoc_lambdawarmup();
        $task->set_custom_data([]);
        \core\task\manager::queue_adhoc_task($task);
        $cache->set('started', 1);
    }
}

// PTL-11225 QUESTIONREPORT

function qtype_mlnlpessay_get_questionattempts_w_categories($questionid, $limit = 20, $offset = 0, $sort = 'ASC', $col = '',
    $search = '') {
    global $DB, $CFG;

    require_once ($CFG->dirroot . '/question/engine/lib.php');
    require_once ($CFG->dirroot . '/question/type/questiontypebase.php');
    require_once ($CFG->dirroot . '/mod/quiz/report/advancedoverview/classes/quizdata.php');

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

    $coltitles = [
        'qattid' => get_string('qattid', 'qtype_mlnlpessay'),
        'quizattuserid' => get_string('quizattuserid', 'qtype_mlnlpessay'),
        'quizattattempt' => get_string('quizattattempt', 'qtype_mlnlpessay'),
        'qattresponsesummary' => get_string('qattresponsesummary', 'qtype_mlnlpessay'),
    ];

    $sortcolumnsids = $coltitles;

    // Get categories
    $questioncategories = $DB->get_field('qtype_mlnlpessay_options', 'categoriesweight ', [
        'questionid' => $questionid,
    ]);
    $questioncategories = (array) json_decode($questioncategories);

    $categoryprefix = "category";
    $defaultcategoriesdata = [];
    foreach ($questioncategories as $category) {
        if ($category->iscategoryselected == 1) {
            $defaultcategoriesdata[$categoryprefix . $category->id] = $category->name;
            $defaultcategoriesdata[$categoryprefix . '_correctness_' . $category->id] = $category->name . ' + (' . get_string("correcntess", "qtype_mlnlpessay") . ')';
        }
    }

    $coltitles += $defaultcategoriesdata;

    $categorykeys = array_keys($defaultcategoriesdata);

    foreach ($attempts as $attkey => $attempt) {
        // Merge attempt with categorykeys
        $attempt = (object) array_merge((array) $attempt, array_fill_keys($categorykeys, '—'));

        $response = $DB->get_record('qtype_mlnlpessay_response', ['questionattemptid' => $attempt->qattid]);
        if ($response) {
            $pythonresponse = json_decode($response->pythonresponse);
            foreach ($pythonresponse as $category) {
                $categorycolid = $categoryprefix . $category->id;
                $categorycorrectnesscolid = $categoryprefix . '_correctness_' . $category->id;
                $overridden = isset($category->overridden) && $category->overridden == 1 ? 1 : 0;
                $correctedresult = $category->correct ? 1 : 0;
                $originalresult = $overridden ? !$correctedresult : $correctedresult;

                // Original
                $attempt->{$categorycolid} = $originalresult ? 1 : 0;

                // Corrected
                $attempt->{$categorycorrectnesscolid} = $correctedresult;

            }
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
    $data->last_page = $limit == 0 ? 0 : ceil($total / $limit);

    return $data;

}

function qtype_mlnlpessay_get_courseid($questionid) {
    global $DB;

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

    return $quiz->courseid;
}

function get_model_name(int $key): string {
    $key--;
    // Define the model names
    $models = [
            0 => "AlephBert",
            1 => "DictaBert_A",
            2 => "DictaBert_B",
    ];

    // Validate that the key exists
    if (!array_key_exists($key, $models)) {
        return "";
    }

    // Return the model name
    return $models[$key];
}

function qtype_mlnlpessay_get_all_question_attempts($limit = 20, $offset = 0, $sort = 'ASC', $col = '', $search = '', $questionnumber = '') {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/question/engine/lib.php');
    require_once($CFG->dirroot . '/question/type/questiontypebase.php');

    $col = $col ? $col : 'qattid';
    $sort = $sort ? $sort : 'ASC';
    $search = !empty($search) ? '%' . $search . '%' : '';
    $questionnumber = !empty($questionnumber) ? '%' . $questionnumber . '%' : '';

    $params = [];
    $params['search'] = $search;
    $params['search2'] = $search;
    $params['search3'] = $search;
    $params['search4'] = $search;
    $params['questionnumber'] = $questionnumber;

    $sql = "SELECT
            qatt.id AS qattid,
            q.id AS qid,
            qbe.idnumber AS qnumber,
            q.name AS questionname,
            quiz.name AS quizname,
            quiz.id AS quizid,
            quizatt.userid AS quizattuserid,
            quizatt.attempt AS quizattattempt,
            qatt.responsesummary AS qattresponsesummary,
            cm.id AS cmid,
            c.id AS courseid,
            c.fullname AS coursename,
            quizatt.timestart AS attemptstart,
            quizatt.timefinish AS attemptfinish
        FROM {question_attempts} qatt
        JOIN {quiz_attempts} quizatt ON qatt.questionusageid = quizatt.uniqueid
        JOIN {quiz_slots} quizslots ON quizslots.slot = qatt.slot AND quizslots.quizid = quizatt.quiz
        JOIN {question_references} qr ON qr.itemid = quizslots.id
        JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
        JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
        JOIN {question} q ON q.id = qv.questionid
        LEFT JOIN {quiz} quiz ON quiz.id = quizatt.quiz
        LEFT JOIN {course_modules} cm ON cm.instance = quiz.id AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz')
        LEFT JOIN {course} c ON c.id = quiz.course";

    $whereConditions = [];
    $whereConditions[] ="q.qtype = 'mlnlpessay'";
    if (!empty($search)) {
        $whereConditions[] = "(qatt.id LIKE :search OR q.name LIKE :search2 OR quiz.name LIKE :search3 OR qatt.responsesummary LIKE :search4)";
    }
    if (!empty($questionnumber)) {
        $whereConditions[] = "qbe.idnumber LIKE :questionnumber";
    }

    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $sql .= " ORDER BY $col $sort";

    $total = $DB->count_records_sql("SELECT COUNT(*) FROM ($sql) as totalcount", $params);
    $records = $DB->get_records_sql($sql, $params, $offset, $limit);

    $coltitles = [
        'qattid' => get_string('qattid', 'qtype_mlnlpessay'),
        'qid' => 'Question ID',
        'qnumber' => 'Question Number',
        'questionname' => 'Question Name',
        'quizname' => 'Quiz Name',
        'quizattuserid' => get_string('quizattuserid', 'qtype_mlnlpessay'),
        'quizattattempt' => get_string('quizattattempt', 'qtype_mlnlpessay'),
        'qattresponsesummary' => get_string('qattresponsesummary', 'qtype_mlnlpessay'),
        'attempttime' => get_string('attempttime', 'qtype_mlnlpessay'),
        'coursename' => get_string('coursename', 'qtype_mlnlpessay'),
    ];

    $questionIds = [];
    foreach ($records as $attempt) {
        if (!empty($attempt->qid)) {
            $questionIds[] = $attempt->qid;
        }
    }

    $all_categories = [];
    if (!empty($questionIds)) {
        list($insql, $inparams) = $DB->get_in_or_equal($questionIds);
        $categories_sql = "SELECT DISTINCT questionid, categoriesweight 
                           FROM {qtype_mlnlpessay_options}
                           WHERE questionid $insql";
        $categories_records = $DB->get_records_sql($categories_sql, $inparams);

        foreach ($categories_records as $record) {
            $categories = json_decode($record->categoriesweight);
            foreach ($categories as $category) {
                if ($category->iscategoryselected == 1) {
                    $all_categories[$category->id] = $category->name;
                }
            }
        }
    }

    $categoryprefix = "category";
    foreach ($all_categories as $id => $name) {
        $coltitles[$categoryprefix . $id] = $name;
        $coltitles[$categoryprefix . '_correctness_' . $id] = $name . ' + (' . get_string("correcntess", "qtype_mlnlpessay") . ')';
    }

    $sortcolumnsids = array_keys($coltitles);
    $attempts = [];

    foreach ($records as $attempt) {
        $questionlink = new moodle_url('/question/type/mlnlpessay/questionreport.php', ['id' => $attempt->qid]);
        $previewlink = new moodle_url('/question/bank/previewquestion/preview.php', ['id' => $attempt->qid]);
        $quizlink = $attempt->cmid ? new moodle_url('/mod/quiz/view.php', ['id' => $attempt->cmid]) : '';

        $courselink = '';
        $coursename_display = 'N/A';
        if ($attempt->courseid) {
            $courselink = new moodle_url('/course/view.php', ['id' => $attempt->courseid]);
            $coursename_display = $attempt->coursename;
        }

        $attempttime = '—';
        if ($attempt->attemptfinish && $attempt->attemptstart) {
            $duration = $attempt->attemptfinish - $attempt->attemptstart;
            $attempttime = format_time($duration);
        }

        $row = [
            $attempt->qattid,
            "<a href='{$questionlink}'>{$attempt->qid}</a>",
            $attempt->qnumber,
            "<a href='{$previewlink}'>{$attempt->questionname}</a>",
            $attempt->quizname ? "<a href='{$quizlink}'>{$attempt->quizname}</a>" : 'N/A',
            $attempt->quizattuserid,
            $attempt->quizattattempt,
            $attempt->qattresponsesummary,
            $attempttime,
            $courselink ? "<a href='{$courselink}'>{$coursename_display}</a>" : $coursename_display
        ];

        $response = $DB->get_record('qtype_mlnlpessay_response', ['questionattemptid' => $attempt->qattid]);
        $category_data = array_fill_keys(array_keys($all_categories), '—');
        $category_correctness_data = array_fill_keys(array_keys($all_categories), '—');

        if ($response) {
            $pythonresponse = json_decode($response->pythonresponse);
            if ($pythonresponse) {
                foreach ($pythonresponse as $category) {
                    if (isset($all_categories[$category->id])) {
                        $overridden = isset($category->overridden) && $category->overridden == 1 ? 1 : 0;
                        $correctedresult = $category->correct ? 1 : 0;
                        $originalresult = $overridden ? !$correctedresult : $correctedresult;

                        $category_data[$category->id] = $originalresult ? 1 : 0;
                        $category_correctness_data[$category->id] = $correctedresult;
                    }
                }
            }
        }

        foreach ($all_categories as $id => $name) {
            $row[] = $category_data[$id];
            $row[] = $category_correctness_data[$id];
        }

        $attempts[] = $row;
    }

    $data = new stdClass();
    $data->attempts = $attempts;
    $data->coltitles = array_values($coltitles);
    $data->sortcolumnsids = array_flip($sortcolumnsids);
    $data->last_page = $limit == 0 ? 0 : ceil($total / $limit);

    return $data;
}