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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_advancedoverview
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz report subclass for the overview (grades) report.
 *
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_advancedoverview_report extends mod_quiz\local\reports\attempts_report {

    public $displayfull = true;
    public $lastaccess = 0;
    public $table;
    public $form;
    public $context;
    public $qmsubselect;
    public $course;
    public $mode;
    public $hasgroupstudents;

    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE, $CFG;

        $PAGE->requires->css('/mod/quiz/report/advancedoverview/css/tabulator.min.css');

        // Print the page header.
        $PAGE->set_title($quiz->name);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        $quizdata = new \quiz_advancedoverview\quizdata($cm->id);
        $quizdata->prepare_questions();
        $quizdata->prepare_skills();
        $quizdata->prepare_charts();
        $quizdata->prepare_students();
        $data = $quizdata->get_render_data();

        $isrepository = false;
        if (\community_oer\main_oer::is_activity_in_research($cm->id) && !\local_metadata\mcontext::module()->get($cm->id, 'cresearch')) {
            $isrepository = true;
        }

        $data['isrepository'] = $isrepository;
        echo $OUTPUT->render_from_template('quiz_advancedoverview/dashboard', $data);

        $defaultconfig = json_encode($quizdata->get_config());
        $PAGE->requires->js_call_amd('quiz_advancedoverview/main', 'init', [$cm->id, $course->id, $quiz->id, $defaultconfig]);

        // Export to XLSX prepare.
        echo '<script src="' . $CFG->wwwroot . '/mod/quiz/report/advancedoverview/js/xlsx.full.min.js"></script>';

        return true;
    }

    public function init($mode, $formclass, $quiz, $cm, $course): array {
        $this->mode = $mode;

        $this->context = context_module::instance($cm->id);

        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) = $this->get_students_joins(
                $cm, $course);

        $this->qmsubselect = quiz_report_qm_filter_select($quiz);

        $this->form = new $formclass($this->get_base_url(),
                array('quiz' => $quiz, 'currentgroup' => $currentgroup, 'context' => $this->context));

        return array($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins);
    }

    public function process_actions($quiz, $cm, $currentgroup, \core\dml\sql_join $groupstudentsjoins,
            \core\dml\sql_join $allowedjoins, $redirecturl) {
        if (empty($currentgroup) || $this->hasgroupstudents) {
            if (optional_param('delete', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    require_capability('mod/quiz:deleteattempts', $this->context);
                    $this->delete_selected_attempts($quiz, $cm, $attemptids, $allowedjoins);
                    redirect($redirecturl);
                }
            }
            if (optional_param('closeattempts', 0, PARAM_BOOL) && confirm_sesskey()) {
                if ($attemptids = optional_param_array('attemptid', array(), PARAM_INT)) {
                    $this->close_selected_attempts($quiz, $cm, $attemptids, $allowedjoins);
                    redirect($redirecturl);
                }
            }
        }
    }

    public function get_max_min_avg_grades($table, $quiz, \core\dml\sql_join $usersjoins) {
        global $DB;

        list($fields, $from, $where, $params) = $table->base_sql($usersjoins);

        $alies = $this->table->getBetween($from, '{user_enrolments}', 'ON');
        $alies = !empty($alies) ? $alies : 'ue_f';

        $sql = "SELECT AVG(quiza.sumgrades) AS averagegrade, MAX(quiza.sumgrades) AS maxgrade,
                     MIN(quiza.sumgrades) AS mingrade, COUNT(quiza.sumgrades) AS numgrades
                  FROM $from
                 WHERE u.suspended = 0 AND " . $alies . ".status = 0 AND $where";

        $sql .= " AND (
				(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR
				(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR
				(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
				(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
				) ";

        $record = $DB->get_record_sql($sql, $params);

        if ($record->numgrades == 0) {
            $record->averagegrade = '-';
            $record->maxgrade = '-';
            $record->mingrade = '-';
        } else {
            $record->averagegrade = round(quiz_rescale_grade($record->averagegrade, $quiz, false), 1);
            $record->maxgrade = round(quiz_rescale_grade($record->maxgrade, $quiz, false));
            $record->mingrade = round(quiz_rescale_grade($record->mingrade, $quiz, false));
        }

        $record->str_max_grade = 'max_grade';
        $record->title_max_grade = get_string('max_grade', 'quiz_advancedoverview');

        $record->str_min_grade = 'min_grade';
        $record->title_min_grade = get_string('min_grade', 'quiz_advancedoverview');

        $record->str_attempts_grade = 'attempts';
        $record->title_attempts_grade = get_string('attempts', 'quiz_advancedoverview');

        // If group.
        if (count($params) > 2) {
            $record->str_max_grade = 'max_grade_group';
            $record->title_max_grade = get_string('max_grade_group', 'quiz_advancedoverview');

            $record->str_min_grade = 'min_grade_group';
            $record->title_min_grade = get_string('min_grade_group', 'quiz_advancedoverview');
        }

        return $record;
    }

    public function quiz_num_attempt_summary($quiz, $cm, $returnzero = false, $currentgroup = 0) {
        global $DB, $USER, $COURSE;

        $sql = "
            SELECT UUID() as uniqueid, quiza.*
            FROM {quiz_attempts} quiza
            LEFT JOIN {user} u ON (quiza.userid = u.id)
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
            WHERE quiza.quiz = :quizid AND quiza.preview = 0 AND u.suspended = 0 AND ue_d.status = 0
			AND (
				(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
				(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
				(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
				(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
				)
        ";

        $numattempts = count($DB->get_records_sql($sql, ['courseid' => $COURSE->id, 'quizid' => $quiz->id]));

        if ($numattempts || $returnzero) {
            if (groups_get_activity_groupmode($cm)) {
                $a = new stdClass();
                $a->total = $numattempts;
                if ($currentgroup) {
                    $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                            '{quiz_attempts} qa JOIN ' .
                            '{groups_members} gm ON qa.userid = gm.userid ' .
                            'LEFT JOIN {user} u ON (qa.userid = u.id) ' .
                            'JOIN {user_enrolments} ue_d ON ue_d.userid = u.id ' .
                            'JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?) ' .
                            'WHERE u.suspended = 0 AND ue_d.status = 0 AND quiz = ? AND preview = 0 AND groupid = ?' .

                            "AND (
								(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
								(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
								(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
								(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
								)",
                            array($COURSE->id, $quiz->id, $currentgroup));
                    return get_string('attemptsnumthisgroup', 'quiz', $a);
                } else if ($groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid)) {
                    list($usql, $params) = $DB->get_in_or_equal(array_keys($groups));
                    $a->group = $DB->count_records_sql('SELECT COUNT(DISTINCT qa.id) FROM ' .
                            '{quiz_attempts} qa JOIN ' .
                            '{groups_members} gm ON qa.userid = gm.userid ' .
                            'LEFT JOIN {user} u ON (qa.userid = u.id) ' .
                            'JOIN {user_enrolments} ue_d ON ue_d.userid = u.id ' .
                            'JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?) ' .
                            'WHERE u.suspended = 0 AND ue_d.status = 0 AND quiz = ? AND preview = 0 AND ' .
                            "groupid $usql" .
                            "AND (
								(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
								(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
								(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
								(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
								)", array_merge(array($COURSE->id, $quiz->id), $params));
                    return get_string('attemptsnumyourgroups', 'quiz', $a);
                }
            }
            return get_string('attemptsnum', 'quiz', $numattempts);
        }
        return '';
    }

    public function quiz_submissions_stat($quiz, $currentgroup) {
        global $DB, $COURSE;

        list($usersincoursejoin, $usersincourseparams) = get_enrolled_sql($this->context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);
        $params = array('quizid' => $quiz->id, 'courseid' => $COURSE->id);
        $params = array_merge($params, $usersincourseparams);

        $student = $DB->get_record('role', ['shortname' => 'student']);

        // Count only one finished attempt for one user.
        $data = $DB->get_records_sql("
            SELECT DISTINCT u.id AS userid
            FROM {user} u
            JOIN ($usersincoursejoin) ue on ue.id = u.id
            LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :quizid
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
            WHERE u.suspended = 0 AND ue_d.status = 0 AND quiza.id IS NOT NULL AND quiza.state = 'finished'
			AND (
				(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
				(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
				(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
				(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
				)
			",
                $params);

        $result = [];
        foreach ($data as $item) {

            $tmp = [];
            foreach (get_user_roles($this->context, $item->userid) as $role) {
                $tmp[] = $role->roleid;
            }

            if (in_array($student->id, $tmp)) {
                $result[] = $item->userid;
            }
        }

        $usersfinished = count(array_unique($result));

        $data = $DB->get_records_sql("
            SELECT u.id AS userid
            FROM {user} u
            JOIN ($usersincoursejoin) ue on ue.id = u.id
            LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :quizid
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
            WHERE u.suspended = 0 AND ue_d.status = 0 AND quiza.id IS NOT NULL AND quiza.state = 'inprogress'
			AND (
				(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
				(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
				(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
				(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
				)
			",
                $params);

        $result = [];
        foreach ($data as $item) {

            $tmp = [];
            foreach (get_user_roles($this->context, $item->userid) as $role) {
                $tmp[] = $role->roleid;
            }

            if (in_array($student->id, $tmp)) {
                $result[] = $item->userid;
            }
        }

        $usersinprogress = count(array_unique($result));

        $data = $DB->get_records_sql("
            SELECT u.id AS userid
            FROM {user} u
            JOIN ($usersincoursejoin) ue on ue.id = u.id
            LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :quizid
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
            WHERE u.suspended = 0 AND ue_d.status = 0 AND quiza.id IS NULL
			AND (
				(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
				(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
				(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
				(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
				)
			",
                $params);

        $result = [];
        foreach ($data as $item) {

            $tmp = [];
            foreach (get_user_roles($this->context, $item->userid) as $role) {
                $tmp[] = $role->roleid;
            }

            if (in_array($student->id, $tmp)) {
                $result[] = $item->userid;
            }
        }

        $usersnotstarted = count(array_unique($result));

        $enrolleduserscount = $usersfinished + $usersinprogress + $usersnotstarted;

        return array($enrolleduserscount, $usersfinished, $usersinprogress, $usersnotstarted);
    }

    public static function get_chart($labels, $data) {
        $chart = new \core\chart_bar();
        $chart->set_labels($labels);
        $chart->get_xaxis(0, true)->set_label(get_string('grade', 'grades'));

        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_label(get_string('participants'));
        $yaxis->set_stepsize(max(1, round(max($data) / 10)));

        $series = new \core\chart_series(get_string('participants'), $data);
        $chart->add_series($series);
        return $chart;
    }

    public function get_questions_stat($quiz, $currentgroup, $usersfinished) {
        global $DB, $COURSE;

        // Find questions (slots) for current Quiz.
        $slots = $DB->get_records('quiz_slots', array('quizid' => $quiz->id), null, 'slot, questionid');

        // Count users, that finished this quiz (if empty argument).
        $ufuesql = "
            SELECT COUNT(qa.userid) AS ufue
            FROM {quiz_attempts} qa
            LEFT JOIN {user} u ON (qa.userid = u.id)
            JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
            JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
            WHERE u.suspended = 0 AND ue_d.status = 0 AND qa.quiz = :quiz AND qa.preview = 0
			AND (
				(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
				(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
				(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
				(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
				)
        ";
        $usersfinishedunenrolled = $DB->get_record_sql($ufuesql, array('quiz' => $quiz->id, 'courseid' => $COURSE->id));
        $usersfinished = !empty($usersfinished) ? $usersfinished : $usersfinishedunenrolled->ufue;

        list($usersincoursejoin, $usersincourseparams) = get_enrolled_sql($this->context,
                array('mod/quiz:reviewmyattempts', 'mod/quiz:attempt'), $currentgroup);

        $questions = array();

        // Question number iterator.
        $i = 1;

        // Count each questions right answers.
        foreach ($slots as $slot => $slotrecord) {
            $qtype = $DB->get_record_sql("SELECT q.qtype FROM {question} q WHERE q.id = :quid",
                    array('quid' => $slotrecord->questionid))->qtype;
            if ($qtype === 'description') {
                // Skip description questions.
                continue;
            }

            $qsql = "
                SELECT COUNT(qa.questionid) AS rigthqcount
                FROM {question_attempt_steps} qas
                INNER JOIN (SELECT DISTINCT eu2_u.id FROM {user} eu2_u
                JOIN {user_enrolments} ej4_ue ON ej4_ue.userid = eu2_u.id
                JOIN {enrol} ej4_e ON (ej4_e.id = ej4_ue.enrolid AND ej4_e.courseid = :courseid)
                JOIN {role_assignments} eu3_ra3 ON (eu3_ra3.userid = eu2_u.id AND eu3_ra3.roleid IN (5))
                WHERE eu2_u.deleted = 0 AND eu2_u.suspended = 0 AND ej4_ue.status = 0
				AND (
					(ej4_ue.timestart = '0' AND ej4_ue.timeend = '0') OR
					(ej4_ue.timestart = '0' AND ej4_ue.timeend > UNIX_TIMESTAMP()) OR
					(ej4_ue.timeend = '0' AND ej4_ue.timestart < UNIX_TIMESTAMP()) OR
					(ej4_ue.timeend > UNIX_TIMESTAMP() AND ej4_ue.timestart < UNIX_TIMESTAMP())
					)
				) ue on ue.id = qas.userid
                LEFT JOIN {question_attempts} qa ON qas.questionattemptid = qa.id
                LEFT JOIN {quiz_attempts} quiza ON qa.questionusageid = quiza.uniqueid
                LEFT JOIN {groups_members} gm ON (qas.userid = gm.userid)
                WHERE qa.questionid = :quid
                AND (qas.state = 'gradedright' OR qas.state = 'mangrright') AND qas.userid > 0
                AND (quiza.userid, quiza.attempt) IN
                    (
                        SELECT qa.userid, MAX(qa.attempt) as att
                        FROM {quiz_attempts} qa
                        LEFT JOIN {user} u ON (qa.userid = u.id)
                        JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                        JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid2)
                        WHERE u.suspended = 0 AND ue_d.status = 0 AND qa.state = 'finished' AND qa.quiz = :quiz
						AND (
							(ue_d.timestart = '0' AND ue_d.timeend = '0') OR
							(ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
							(ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
							(ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
							)
                        GROUP BY userid

                    )
                AND qas.fraction = 1
            ";

            if ($currentgroup > 0) {
                $qsql .= " AND gm.groupid = :gmid ";
            }

            $params = array(
                    'quid' => $slotrecord->questionid,
                    'quiz' => $quiz->id,
                    'courseid' => $COURSE->id,
                    'courseid2' => $COURSE->id,
                    'gmid' => $currentgroup,
            );
            $params = array_merge($usersincourseparams, $params);

            $questions[$i] = $DB->get_record_sql($qsql, $params);
            $questions[$i]->slot = $i;
            $questions[$i]->qname =
                    mb_substr(strip_tags($DB->get_record_sql("SELECT q.questiontext 'qname' FROM {question} q WHERE q.id = :qid",
                            array('qid' => $slotrecord->questionid))->qname), 0, 100);

            // Compute ratio of correct answers to all answers and define proper badge color.
            if ($usersfinished != 0) {
                $questions[$i]->ratio = $questions[$i]->rigthqcount / $usersfinished;
            } else {
                $questions[$i]->ratio = null;
            }
            if ($questions[$i]->ratio == 1) {
                $questions[$i]->badgecolor = 'green';
            } else if ($questions[$i]->ratio >= 0.5 && $questions[$i]->ratio < 1) {
                $questions[$i]->badgecolor = 'yellow';
            } else if ($questions[$i]->ratio > 0 && $questions[$i]->ratio < 0.5) {
                $questions[$i]->badgecolor = 'red';
            } else if (empty($questions[$i]->ratio)) {
                $questions[$i]->badgecolor = 'grey';
            }

            $i++; // Increment question number.
        }

        // Get only values for mustache.
        $questions = array_values($questions);
        return $questions;
    }

    public function add_user_columns($table, &$columns, &$headers) {
        global $CFG;
        if (!$table->is_downloading() && $CFG->grade_report_showuserimage) {
            $columns[] = 'picture';
            $headers[] = '';
        }
        if (!$table->is_downloading()) {
            $columns[] = 'fullname';
            $headers[] = get_string('name');
        } else {
            $columns[] = 'lastname';
            $headers[] = get_string('lastname');
            $columns[] = 'firstname';
            $headers[] = get_string('firstname');
        }

        // PTL-4263 remove extra user fields (idnumber & email) from quiz reports.
        if ($table->is_downloading()) {
            $fields = \core_user\fields::for_identity($this->context, false)->excluding(...[]);

            foreach ($fields->get_required_fields() as $field) {
                $columns[] = $field;
                $headers[] = \core_user\fields::get_display_name($field);
            }
        }
        // PTL-7651 Display internal userid for researchers when downloading data.
        $research = optional_param('research', '', PARAM_BOOL);
        if ($table->is_downloading() && $research) {
            $columns[] = 'userid';
            $headers[] = 'userid';
        }
    }

    public function add_state_column(&$columns, &$headers) {
        $columns[] = 'state';
        $headers[] = get_string('attemptstate', 'quiz');
    }

    public function add_time_columns(&$columns, &$headers) {
        $columns[] = 'timestart';
        $headers[] = get_string('startedon', 'quiz');

        $columns[] = 'timefinish';
        $headers[] = get_string('timecompleted', 'quiz');

        $columns[] = 'duration';
        $headers[] = get_string('attemptduration', 'quiz');
    }

    public function add_grade_columns($quiz, $usercanseegrades, &$columns, &$headers, $includefeedback = true) {
        if ($usercanseegrades) {
            $columns[] = 'sumgrades';
            $headers[] = get_string('grade', 'quiz') . '/' .
                    quiz_format_grade($quiz, $quiz->grade);
        }

        if ($includefeedback && quiz_has_feedback($quiz)) {
            $columns[] = 'feedbacktext';
            $headers[] = get_string('feedback', 'quiz');
        }
    }

    public function has_regraded_questions($from, $where, $params) {
        global $DB;

        $alies = $this->table->getBetween($from, '{user_enrolments}', 'ON');
        $alies = !empty($alies) ? $alies : 'ue_f';

        $where .= ' AND u.suspended = 0 AND ' . $alies . '.status = 0 ';

        $where .= " AND (
			(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR
			(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR
			(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
			(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
			) ";

        return $DB->record_exists_sql("
                SELECT 1
                  FROM {$from}
                  JOIN {quiz_overview_regrades} qor ON qor.questionusageid = quiza.uniqueid
                 WHERE {$where}", $params);
    }

    public function set_up_table_columns($table, $columns, $headers, $reporturl,
            mod_quiz\local\reports\attempts_report_options $options, $collapsible) {
        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->sortable(true, 'uniqueid');

        $table->define_baseurl($options->get_url());

        $this->configure_user_columns($table);

        $table->no_sorting('feedbacktext');
        $table->column_class('sumgrades', 'bold');

        $table->set_attribute('id', 'attempts');

        $table->collapsible($collapsible);
    }

    public function get_base_url() {
        return new moodle_url('/mod/quiz/report.php',
                array('id' => $this->context->instanceid, 'mode' => $this->mode));
    }

    public function count_question_attempts_needing_regrade($quiz, \core\dml\sql_join $groupstudentsjoins) {
        global $DB;

        $userjoin = '';
        $usertest = '';
        $params = array();
        if ($this->hasgroupstudents) {
            $userjoin = "JOIN {user} u ON (u.id = quiza.userid)
                    {$groupstudentsjoins->joins}";
            $usertest = "{$groupstudentsjoins->wheres} AND u.id = quiza.userid AND u.suspended = 0 AND ";
            $params = $groupstudentsjoins->params;
        }

        $params['cquiz'] = $quiz->id;
        $sql = "SELECT COUNT(DISTINCT quiza.id)
                  FROM {quiz_attempts} quiza
                  JOIN {quiz_overview_regrades} qqr ON quiza.uniqueid = qqr.questionusageid
                $userjoin
                 WHERE
                      $usertest
                      quiza.quiz = :cquiz AND
                      quiza.preview = 0 AND
                      qqr.regraded = 0";
        return $DB->count_records_sql($sql, $params);
    }

    public function regrade_attempts($quiz, $dryrun = false,
            \core\dml\sql_join $groupstudentsjoins = null, $attemptids = array()) {
        global $DB;
        $this->unlock_session();

        $sql = "SELECT quiza.*
              FROM {quiz_attempts} quiza";
        $where = "quiz = :qid AND preview = 0";
        $params = array('qid' => $quiz->id);

        if ($this->hasgroupstudents && !empty($groupstudentsjoins->joins)) {
            $sql .= "\nJOIN {user} u ON (u.id = quiza.userid)
                {$groupstudentsjoins->joins}";
            $where .= " AND u.suspended = 0 AND {$groupstudentsjoins->wheres}";
            $params += $groupstudentsjoins->params;
        }

        if ($attemptids) {
            $aids = join(',', $attemptids);
            $where .= " AND quiza.id IN ({$aids})";
        }

        $sql .= "\nWHERE {$where}";
        $attempts = $DB->get_records_sql($sql, $params);
        if (!$attempts) {
            return;
        }

        $this->clear_regrade_table($quiz, $groupstudentsjoins);

        $progressbar = new progress_bar('quiz_overview_regrade', 500, true);
        $a = array(
                'count' => count($attempts),
                'done' => 0,
        );
        foreach ($attempts as $attempt) {
            $this->regrade_attempt($attempt, $dryrun);
            $a['done']++;
            $progressbar->update($a['done'], $a['count'],
                    get_string('regradingattemptxofy', 'quiz_advancedoverview', $a));
        }

        if (!$dryrun) {
            $this->update_overall_grades($quiz);
        }
    }

    protected function unlock_session() {
        \core\session\manager::write_close();
        ignore_user_abort(true);
    }

    protected function clear_regrade_table($quiz, \core\dml\sql_join $groupstudentsjoins) {
        global $DB;

        // Fetch all attempts that need regrading.
        $select = "questionusageid IN (
                SELECT uniqueid
                  FROM {quiz_attempts} quiza";
        $where = "WHERE quiza.quiz = :qid";
        $params = array('qid' => $quiz->id);
        if ($this->hasgroupstudents && !empty($groupstudentsjoins->joins)) {
            $select .= "\nJOIN {user} u ON (u.id = quiza.userid)
                {$groupstudentsjoins->joins}";
            $where .= " AND u.suspended = 0 AND {$groupstudentsjoins->wheres}";
            $params += $groupstudentsjoins->params;
        }
        $select .= "\n$where)";

        $DB->delete_records_select('quiz_overview_regrades', $select, $params);
    }

    protected function regrade_attempt($attempt, $dryrun = false, $slots = null) {
        global $DB;
        // Need more time for a quiz with many questions.
        core_php_time_limit::raise(300);

        $transaction = $DB->start_delegated_transaction();

        $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

        if (is_null($slots)) {
            $slots = $quba->get_slots();
        }

        $finished = $attempt->state == mod_quiz\quiz_attempt::FINISHED;
        foreach ($slots as $slot) {
            $qqr = new stdClass();
            $qqr->oldfraction = $quba->get_question_fraction($slot);

            $quba->regrade_question($slot, $finished);

            $qqr->newfraction = $quba->get_question_fraction($slot);

            if (abs($qqr->oldfraction - $qqr->newfraction) > 1e-7) {
                $qqr->questionusageid = $quba->get_id();
                $qqr->slot = $slot;
                $qqr->regraded = empty($dryrun);
                $qqr->timemodified = time();
                $DB->insert_record('quiz_overview_regrades', $qqr, false);
            }
        }

        if (!$dryrun) {
            question_engine::save_questions_usage_by_activity($quba);
        }

        $transaction->allow_commit();

        // Really, PHP should not need this hint, but without this, we just run out of memory.
        $quba = null;
        $transaction = null;
        gc_collect_cycles();
    }

    protected function update_overall_grades($quiz) {
        quiz_update_all_attempt_sumgrades($quiz);
        quiz_update_all_final_grades($quiz);
        quiz_update_grades($quiz);
    }

    public function close_selected_attempts($quiz, $cm, $attemptids, \core\dml\sql_join $allowedjoins) {
        global $DB;

        foreach ($attemptids as $attemptid) {
            if (empty($allowedjoins->joins)) {
                $sql = "SELECT quiza.*
                          FROM {quiz_attempts} quiza
                          JOIN {user} u ON u.id = quiza.userid
                         WHERE quiza.id = :attemptid";
            } else {
                $sql = "SELECT quiza.*
                          FROM {quiz_attempts} quiza
                          JOIN {user} u ON u.id = quiza.userid
                        {$allowedjoins->joins}
                         WHERE {$allowedjoins->wheres} AND quiza.id = :attemptid";
            }
            $params = $allowedjoins->params + array('attemptid' => $attemptid);
            $attempt = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
            if (!$attempt || $attempt->quiz != $quiz->id || $attempt->preview != 0) {
                // Ensure the attempt exists, belongs to this quiz and belongs to
                // a student included in the report. If not skip.
                continue;
            }

            $params = array(
                    'objectid' => $attempt->id,
                    'relateduserid' => $attempt->userid,
                    'context' => context_module::instance($cm->id),
                    'other' => array(
                            'quizid' => $quiz->id,
                    ),
            );

            if (class_exists('\mod_quiz\event\attempt_closed')) {
                $event = \mod_quiz\event\attempt_closed::create($params);
                $event->add_record_snapshot('quiz_attempts', $attempt);
                $event->trigger();
            }

            if (function_exists('quiz_close_attempt')) {
                quiz_close_attempt($attempt, $quiz);
            }
        }
    }

    public function delete_selected_attempts($quiz, $cm, $attemptids, \core\dml\sql_join $allowedjoins) {
        global $DB;

        foreach ($attemptids as $attemptid) {
            if (empty($allowedjoins->joins)) {
                $sql = "SELECT quiza.*
                          FROM {quiz_attempts} quiza
                          JOIN {user} u ON u.id = quiza.userid
                         WHERE quiza.id = :attemptid";
            } else {
                $sql = "SELECT quiza.*
                          FROM {quiz_attempts} quiza
                          JOIN {user} u ON u.id = quiza.userid
                        {$allowedjoins->joins}
                         WHERE {$allowedjoins->wheres} AND quiza.id = :attemptid";
            }
            $params = $allowedjoins->params + array('attemptid' => $attemptid);
            $attempt = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
            if (!$attempt || $attempt->quiz != $quiz->id || $attempt->preview != 0) {
                continue;
            }

            // Set the course module id before calling quiz_delete_attempt().
            $quiz->cmid = $cm->id;
            quiz_delete_attempt($attempt, $quiz);
        }
    }
}
