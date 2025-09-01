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
 * This file defines the quiz grades table.
 *
 * @package   quiz_advancedoverview
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Devlion Moodle Development <service@devlion.co>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php');

/**
 * This is a table subclass for displaying the quiz grades report.
 *
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_advancedoverview_table extends mod_quiz\local\reports\attempts_report_table {

    protected $regradedqs = array();

    /**
     * Constructor
     *
     * @param object $quiz
     * @param context_module $context
     * @param string $qmsubselect
     * @param quiz_overview_options $options
     * @param \core\dml\sql_join $groupstudentsjoins
     * @param \core\dml\sql_join $studentsjoins
     * @param array $questions
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $qmsubselect,
            quiz_advancedoverview_options $options, \core\dml\sql_join $groupstudentsjoins,
            \core\dml\sql_join $studentsjoins, $questions, $reporturl) {
        parent::__construct('mod-quiz-report-overview-report', $quiz, $context,
                $qmsubselect, $options, $groupstudentsjoins, $studentsjoins, $questions, $reporturl);
    }

    public function getBetween($content, $start, $end) {
        $r = explode($start, $content);

        if (isset($r[1])) {
            $r = explode($end, $r[1]);
            return trim($r[0]);
        }
        return false;
    }

    /**
     * Contruct all the parts of the main database query.
     *
     * @param \core\dml\sql_join $allowedstudentsjoins (joins, wheres, params) defines allowed users for the report.
     * @return array with 4 elements ($fields, $from, $where, $params) that can be used to
     *     build the actual database query.
     */
    public function base_sql(\core\dml\sql_join $allowedstudentsjoins) {
        global $DB;

        // Please note this uniqueid column is not the same as quiza.uniqueid.
        $fields = 'DISTINCT ' . $DB->sql_concat('u.id', "'#'", 'COALESCE(quiza.attempt, 0)') . ' AS uniqueid,';

        if ($this->qmsubselect) {
            $fields .= "\n(CASE WHEN $this->qmsubselect THEN 1 ELSE 0 END) AS gradedattempt,";
        }

        $fieldsobj = \core_user\fields::for_identity($this->context, false)->excluding(...
                array('id', 'idnumber', 'firstname', 'lastname', 'picture', 'imagealt', 'institution', 'department', 'email'));
        $extrafields = $fieldsobj->get_sql('u', false, '')->selects;

        // Instead deprecated "get_all_user_name_fields".
        $alternatenames = [];
        foreach (\core_user\fields::get_name_fields() as $field) {
            $alternatenames[$field] = $field;
        }

        foreach ($alternatenames as $key => $altname) {
            $alternatenames[$key] = 'u' . '.' . $altname;
        }

        $allnames = implode(',', $alternatenames);

        $fields .= '
                quiza.uniqueid AS usageid,
                quiza.id AS attempt,
                u.id AS userid,
                u.idnumber, ' . $allnames . ',
                u.picture,
                u.imagealt,
                u.institution,
                u.department,
                u.email' . $extrafields . ',
                quiza.state,
                quiza.sumgrades,
                quiza.timefinish,
                quiza.timestart,
                CASE WHEN quiza.timefinish = 0 THEN null
                     WHEN quiza.timefinish > quiza.timestart THEN quiza.timefinish - quiza.timestart
                     ELSE 0 END AS duration';
        // To explain that last bit, timefinish can be non-zero and less
        // than timestart when you have two load-balanced servers with very
        // badly synchronised clocks, and a student does a really quick attempt.

        // This part is the same for all cases. Join the users and quiz_attempts tables.
        $from = " {user} u";
        $from .= "\nLEFT JOIN {quiz_attempts} quiza ON
                                    quiza.userid = u.id AND quiza.quiz = :quizid";

        $alies = $this->getBetween($allowedstudentsjoins->joins, '{user_enrolments}', 'ON');
        $alies = !empty($alies) ? $alies : 'ue_f';

        $params = array('quizid' => $this->quiz->id);

        if ($this->qmsubselect && $this->options->onlygraded) {
            $from .= " AND (quiza.state <> :finishedstate OR $this->qmsubselect)";
            $params['finishedstate'] = mod_quiz\quiz_attempt::FINISHED;
        }

        switch ($this->options->attempts) {
            case mod_quiz\local\reports\attempts_report::ALL_WITH:
                // Show all attempts, including students who are no longer in the course.
                $where = 'u.suspended = 0 AND ue_f.status = 0 AND quiza.id IS NOT NULL AND quiza.preview = 0';
                $where .= " AND ( 
						(ue_f.timestart = '0' AND ue_f.timeend = '0') OR 
						(ue_f.timestart = '0' AND ue_f.timeend > UNIX_TIMESTAMP()) OR 
						(ue_f.timeend = '0' AND ue_f.timestart < UNIX_TIMESTAMP()) OR
						(ue_f.timeend > UNIX_TIMESTAMP() AND ue_f.timestart < UNIX_TIMESTAMP())
						) ";
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_WITH:
                // Show only students with attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "u.suspended = 0 AND " . $alies . ".status = 0 AND quiza.preview = 0 AND quiza.id IS NOT NULL AND " .
                        $allowedstudentsjoins->wheres;
                $where .= " AND ( 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
						(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
						(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
						) ";
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_WITHOUT:
                // Show only students without attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "u.suspended = 0 AND " . $alies . ".status = 0 AND quiza.id IS NULL AND " . $allowedstudentsjoins->wheres;
                $where .= " AND ( 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
						(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
						(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
						) ";
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_ALL:
                // Show all students with or without attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "u.suspended = 0 AND " . $alies . ".status = 0 AND (quiza.preview = 0 OR quiza.preview IS NULL) AND " .
                        $allowedstudentsjoins->wheres;
                $where .= " AND ( 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
						(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
						(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
						(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
						) ";
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
        }

        $from .= "\nLEFT JOIN {user_enrolments} ue_f ON ue_f.userid = u.id ";

        if ($this->options->states) {
            list($statesql, $stateparams) = $DB->get_in_or_equal($this->options->states,
                    SQL_PARAMS_NAMED, 'state');
            $params += $stateparams;
            $where .= " AND (quiza.state $statesql OR quiza.state IS NULL)";
        }

        return array($fields, $from, $where, $params);
    }

    public function build_table() {
        global $DB;

        if (!$this->rawdata) {
            return;
        }

        // Filter teachers on the course - don't show them in report page table.
        foreach ($this->rawdata as $id => $row) {
            if (has_capability('report/courseoverview:view', $this->context, $row->userid)) {
                unset($this->rawdata[$id]);
            }
        }

        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        $this->column_class('fullname', 'advancedoverviewfullname');
        $this->column_class('email', 'advancedoverviewemail');
        $this->column_class('state', 'advancedoverviewstate');
        $this->column_class('sumgrades', 'advancedoverviewgrades');

        $i = 1;
        foreach ($this->questions as $qcounter => $question) {
            if ($question->qtype == 'description') {
                continue;
            }
            $this->column_class('qsgrade' . $qcounter, 'advancedoverviewqsgrade' . $i);
            $i++;
        }

        parent::build_table();

        // End of adding the data from attempts. Now add averages at bottom.
        $this->add_separator();

        if (!empty($this->groupstudentsjoins->joins)) {

            $alies = $this->getBetween($this->groupstudentsjoins->joins, '{user_enrolments}', 'ON');
            $alies = !empty($alies) ? $alies : 'ue_f';

            $sql = "SELECT DISTINCT u.id
                      FROM {user} u                      
                    {$this->groupstudentsjoins->joins}
                     WHERE u.suspended = 0 AND " . $alies . ".status = 0 AND {$this->groupstudentsjoins->wheres}";

            $sql .= " AND ( 
					(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
					(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
					(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
					(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
					) ";

            $groupstudents = $DB->get_records_sql($sql, $this->groupstudentsjoins->params);
            if ($groupstudents) {
                $this->add_average_row(get_string('groupavg', 'grades'), $this->groupstudentsjoins);
            }
        }

        if (!empty($this->studentsjoins->joins)) {

            $alies = $this->getBetween($this->studentsjoins->joins, '{user_enrolments}', 'ON');
            $alies = !empty($alies) ? $alies : 'ue_f';

            $sql = "SELECT DISTINCT u.id
                      FROM {user} u                      
                    {$this->studentsjoins->joins}
                     WHERE u.suspended = 0 AND " . $alies . ".status = 0 AND {$this->studentsjoins->wheres}";

            $sql .= " AND ( 
					(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
					(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
					(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
					(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
					) ";

            $students = $DB->get_records_sql($sql, $this->studentsjoins->params);
            if ($students) {
                $this->add_average_row(get_string('overallaverage', 'grades'), $this->studentsjoins);
            }
        }
    }

    /**
     * Add an average grade over the attempts of a set of users.
     *
     * @param string $label the title ot use for this row.
     * @param \core\dml\sql_join $usersjoins (joins, wheres, params) for the users to average over.
     */
    protected function add_average_row($label, \core\dml\sql_join $usersjoins) {
        global $DB;

        list($fields, $from, $where, $params) = $this->base_sql($usersjoins);

        $alies = $this->getBetween($from, '{user_enrolments}', 'ON');
        $alies = !empty($alies) ? $alies : 'ue_f';

        $sql = "
                SELECT AVG(quiza.sumgrades) AS grade, COUNT(quiza.sumgrades) AS numaveraged
                FROM $from
                WHERE u.suspended = 0 AND " . $alies . ".status = 0 AND $where";

        $sql .= " AND ( 
				(" . $alies . ".timestart = '0' AND " . $alies . ".timeend = '0') OR 
				(" . $alies . ".timestart = '0' AND " . $alies . ".timeend > UNIX_TIMESTAMP()) OR 
				(" . $alies . ".timeend = '0' AND " . $alies . ".timestart < UNIX_TIMESTAMP()) OR
				(" . $alies . ".timeend > UNIX_TIMESTAMP() AND " . $alies . ".timestart < UNIX_TIMESTAMP())
				) ";

        $record = $DB->get_record_sql($sql, $params);

        $record->grade = quiz_rescale_grade($record->grade, $this->quiz, false);

        if ($this->is_downloading()) {
            $namekey = 'lastname';
        } else {
            $namekey = 'fullname';
        }
        $averagerow = array(
                $namekey => $label,
                'sumgrades' => $this->format_average($record),
                'feedbacktext' => strip_tags(quiz_report_feedback_for_grade(
                        $record->grade, $this->quiz->id, $this->context)),
        );

        if ($this->options->slotmarks) {
            $dm = new question_engine_data_mapper();
            $qubaids = new qubaid_join($from, 'quiza.uniqueid', $where, $params);
            $avggradebyq = $dm->load_average_marks($qubaids, array_keys($this->questions));

            $averagerow += $this->format_average_grade_for_questions($avggradebyq);
        }

        $this->add_data_keyed($averagerow);
    }

    /**
     * Helper userd by {@link add_average_row()}.
     *
     * @param array $gradeaverages the raw grades.
     * @return array the (partial) row of data.
     */
    protected function format_average_grade_for_questions($gradeaverages) {
        $row = array();

        if (!$gradeaverages) {
            $gradeaverages = array();
        }

        foreach ($this->questions as $question) {
            if (isset($gradeaverages[$question->slot]) && $question->maxmark > 0) {
                $record = $gradeaverages[$question->slot];
                $record->grade = quiz_rescale_grade(
                        $record->averagefraction * $question->maxmark, $this->quiz, false);

            } else {
                $record = new stdClass();
                $record->grade = null;
                $record->numaveraged = 0;
            }

            $row['qsgrade' . $question->slot] = $this->format_average($record, true);
        }

        return $row;
    }

    /**
     * Format an entry in an average row.
     *
     * @param object $record with fields grade and numaveraged
     */
    protected function format_average($record, $question = false) {
        if (is_null($record->grade)) {
            $average = '-';
        } else if ($question) {
            $average = quiz_format_question_grade($this->quiz, $record->grade);
        } else {
            $average = quiz_format_grade($this->quiz, $record->grade);
        }

        if ($this->download) {
            return $average;
        } else if (is_null($record->numaveraged) || $record->numaveraged == 0) {
            return html_writer::tag('span', html_writer::tag('span',
                    $average, array('class' => 'average')), array('class' => 'avgcell'));
        } else {
            return html_writer::tag('span', html_writer::tag('span',
                            $average, array('class' => 'average')) . ' ' . html_writer::tag('span',
                            '(' . $record->numaveraged . ')', array('class' => 'count')),
                    array('class' => 'avgcell'));
        }
    }

    protected function submit_buttons() {
        if (has_capability('mod/quiz:regrade', $this->context)) {
            echo '<input type="submit" class="btn btn-secondary m-r-1" name="regrade" value="' .
                    get_string('regradeselected', 'quiz_advancedoverview') . '"/>';
        }
        parent::submit_buttons();
    }

    /**
     * Generate the display of the attempt state column.
     *
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_state($attempt) {
        if (!is_null($attempt->attempt)) {
            return mod_quiz\quiz_attempt::state_name($attempt->state);
        } else {
            return get_string('notstarted', 'quiz_advancedoverview');
        }
    }

    public function col_sumgrades($attempt) {
        if ($attempt->state != mod_quiz\quiz_attempt::FINISHED) {
            return '-';
        }

        $grade = quiz_rescale_grade($attempt->sumgrades, $this->quiz);
        //$grade = round($grade);

        if ($this->is_downloading()) {
            return $grade;
        }

        if (isset($this->regradedqs[$attempt->usageid])) {
            $newsumgrade = 0;
            $oldsumgrade = 0;
            foreach ($this->questions as $question) {
                if (isset($this->regradedqs[$attempt->usageid][$question->slot])) {
                    $newsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->newfraction * $question->maxmark;
                    $oldsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->oldfraction * $question->maxmark;
                } else {
                    $newsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                    $oldsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                }
            }
            $newsumgrade = quiz_rescale_grade($newsumgrade, $this->quiz);
            $oldsumgrade = quiz_rescale_grade($oldsumgrade, $this->quiz);
            $grade = html_writer::tag('del', $oldsumgrade) . '/' .
                    html_writer::empty_tag('br') . $newsumgrade;
        }
        return html_writer::link(new moodle_url('/mod/quiz/review.php',
                array('attempt' => $attempt->attempt)), $grade,
                array('title' => get_string('reviewattempt', 'quiz'), 'data-rel' => $grade));
    }

    /**
     * @param string $colname the name of the column.
     * @param object $attempt the row of data - see the SQL in display() in
     * mod/quiz/report/overview/report.php to see what fields are present,
     * and what they are called.
     * @return string the contents of the cell.
     */
    public function other_cols($colname, $attempt) {
        if (!preg_match('/^qsgrade(\d+)$/', $colname, $matches)) {
            return null;
        }
        $slot = $matches[1];

        $question = $this->questions[$slot];
        if (!isset($this->lateststeps[$attempt->usageid][$slot])) {
            return '-';
        }

        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        $state = question_state::get($stepdata->state);

        if ($question->maxmark == 0) {
            $grade = '-';
        } else if (is_null($stepdata->fraction)) {
            if ($state == question_state::$needsgrading) {
                $grade = get_string('requiresgrading', 'question');
            } else {
                $grade = '-';
            }
        } else {
            $grade = quiz_rescale_grade(
                    $stepdata->fraction * $question->maxmark, $this->quiz, 'question');
        }

        if ($this->is_downloading()) {
            return $grade;
        }

        if (isset($this->regradedqs[$attempt->usageid][$slot])) {
            $gradefromdb = $grade;
            $newgrade = quiz_rescale_grade(
                    $this->regradedqs[$attempt->usageid][$slot]->newfraction * $question->maxmark,
                    $this->quiz, 'question');
            $oldgrade = quiz_rescale_grade(
                    $this->regradedqs[$attempt->usageid][$slot]->oldfraction * $question->maxmark,
                    $this->quiz, 'question');

            $grade = html_writer::tag('del', $oldgrade) . '/' .
                    html_writer::empty_tag('br') . $newgrade;
        }

        return $this->make_review_link($grade, $attempt, $slot);
    }

    public function make_review_link($grade, $attempt, $slot) {
        global $OUTPUT;

        $flag = '';
        if ($this->is_flagged($attempt->usageid, $slot)) {
            $flag = $OUTPUT->pix_icon('i/flagged', get_string('flagged', 'question'),
                    'moodle', array('class' => 'questionflag'));
        }

        $feedbackimg = '';
        $state = $this->slot_state($attempt, $slot);
        if ($state->is_finished() && $state != question_state::$needsgrading) {
            $feedbackimg = $this->icon_for_fraction($this->slot_fraction($attempt, $slot));
        }
        $fraction = $this->slot_fraction($attempt, $slot);
        if ($fraction == 0) {
            $class = 'user-incorrect';
        }

        if ($fraction == 1) {
            $class = 'user-correct';
        }

        if ($fraction > 0 && $fraction < 1) {
            $class = 'user-partiallycorrect';
        }

        $output = html_writer::tag('span', $feedbackimg . html_writer::tag('span',
                        $grade, array('class' => $state->get_state_class(true))) . $flag, array('class' => 'que ' . $class));

        $reviewparams = array('attempt' => $attempt->attempt, 'slot' => $slot);
        if (isset($attempt->try)) {
            $reviewparams['step'] = $this->step_no_for_try($attempt->usageid, $slot, $attempt->try);
        }

        $url = new moodle_url('/mod/quiz/comment.php', $reviewparams);

        $output = $OUTPUT->action_link($url, $output,
                new popup_action('click', $url, 'reviewquestion',
                        array('height' => 450, 'width' => 650)),
                array('title' => get_string('reviewresponse', 'quiz')));

        return $output;
    }

    public function col_regraded($attempt) {
        if ($attempt->regraded == '') {
            return '';
        } else if ($attempt->regraded == 0) {
            return get_string('needed', 'quiz_advancedoverview');
        } else if ($attempt->regraded == 1) {
            return get_string('done', 'quiz_advancedoverview');
        }
    }

    protected function requires_latest_steps_loaded() {
        return $this->options->slotmarks;
    }

    protected function is_latest_step_column($column) {
        if (preg_match('/^qsgrade([0-9]+)/', $column, $matches)) {
            return $matches[1];
        }
        return false;
    }

    protected function get_required_latest_state_fields($slot, $alias) {
        return "$alias.fraction * $alias.maxmark AS qsgrade$slot";
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        parent::query_db($pagesize, $useinitialsbar);

        if ($this->options->slotmarks && has_capability('mod/quiz:regrade', $this->context)) {
            $this->regradedqs = $this->get_regraded_questions();
        }
    }

    public function get_sort_columns() {
        // Add attemptid as a final tie-break to the sort. This ensures that
        // Attempts by the same student appear in order when just sorting by name.
        $sortcolumns = parent::get_sort_columns();
        $sortcolumns['quiza.id'] = SORT_DESC;
        return $sortcolumns;
    }

    /**
     * Get all the questions in all the attempts being displayed that need regrading.
     *
     * @return array A two dimensional array $questionusageid => $slot => $regradeinfo.
     */
    protected function get_regraded_questions() {
        global $DB;

        $qubaids = $this->get_qubaids_condition();
        $regradedqs = $DB->get_records_select('quiz_overview_regrades',
                'questionusageid ' . $qubaids->usage_id_in(), $qubaids->usage_id_in_params());
        return quiz_report_index_by_keys($regradedqs, array('questionusageid', 'slot'));
    }

    public function col_fullname($attempt) {
        $html = fullname($attempt);
        if ($this->is_downloading() || empty($attempt->attempt)) {
            return $html;
        }

        return html_writer::link(
                new moodle_url('/mod/quiz/review.php', array('attempt' => $attempt->attempt)),
                $html, array('class' => 'reviewlink'));
    }

    public function wrap_html_start() {
        global $PAGE;

        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        $url = $this->options->get_url();
        $url->param('sesskey', sesskey());

        echo '<div id="tablecontainer">';
        echo '<form id="attemptsform" method="post" action="' . $url->out_omit_querystring() . '">';

        echo html_writer::input_hidden_params($url);

        // TODO: Fix delete button logic
        /*
        if (has_capability('mod/quiz:deleteattempts', $this->context)) {
        echo '<input type="hidden" class="btn btn-secondary m-r-1"
        id="deleteattemptsbutton" name="delete" value="' .
        get_string('deleteselected', 'quiz_advancedoverview') . '"/>';
        $PAGE->requires->event_handler('#deleteattemptsbutton', 'click', 'M.util.show_confirm_dialog',
        array('message' => get_string('deleteattemptcheck', 'quiz')));
        }
         */

        echo '<div>';
    }

    public function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
            // No data has been added to the table.
            $this->print_nothing_to_display();

        } else {
            // Print empty rows to fill the table to the current pagesize.
            // This is done so the header aria-controls attributes do not point to
            // non existant elements.
            $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            echo html_writer::end_tag('tbody');
            echo html_writer::end_tag('table');
            echo html_writer::end_tag('div');
            $this->wrap_html_finish();
        }
    }

    public function wrap_html_finish() {
        global $PAGE;
        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        // Close the form.
        echo '</div>';
        echo '</form></div>';
    }

    /**
     * @param array $headers numerical keyed array of displayed string titles
     * for each column.
     */
    public function define_headers($headers) {
        global $PAGE;

        if (!$this->is_downloading()) {
            // Select all / Deselect all.
            $checkboxhader = '';
            $checkboxhader .= '<div id="commands">';
            $checkboxhader .= '<a id="checkattempts" href="#">' .
                    get_string('selectall', 'quiz') . '</a> / ';
            $checkboxhader .= '<a id="uncheckattempts" href="#">' .
                    get_string('selectnone', 'quiz') . '</a> ';
            $PAGE->requires->js_amd_inline("
                require(['jquery'], function($) {
                    $('#checkattempts').click(function(e) {
                        $('#attemptsform').find('input:checkbox').prop('checked', true);
                        e.preventDefault();
                    });
                    $('#uncheckattempts').click(function(e) {
                        $('#attemptsform').find('input:checkbox').prop('checked', false);
                        e.preventDefault();
                    });
                });");
            $checkboxhader .= '&nbsp;&nbsp;';
            $checkboxhader .= '</div>';

            $headers[0] = $checkboxhader;
        }
        $this->headers = $headers;

    }

    /**
     * Generate the display of the checkbox column.
     *
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_checkbox($attempt) {
        if ($attempt->attempt) {
            return '<input type="checkbox" name="attemptid[]" value="' . $attempt->attempt . '
                " data-userid="' . $attempt->userid . '" class="userid-checkbox" />';
        } else {
            return '<input type="checkbox" name="attemptid[]" value="" 
                data-userid="' . $attempt->userid . '" class="userid-checkbox" />';
        }
    }

    public function download_buttons() {
        global $OUTPUT;
        return;
    }

    public function get_columns_custom() {
        $columns = [];
        foreach ($this->columns as $colname => $key) {
            if (!in_array($colname, $this->column_nosort)) {
                $columns[] = $colname;
            }
        }

        $columns[] = 'firstname';
        $columns[] = 'lastname';

        return $columns;
    }

    public function get_sql_sort() {
        $tsort = optional_param('tsort', '', PARAM_TEXT);
        $tdir = optional_param('tdir', 0, PARAM_INT);

        if (!empty($tsort) && in_array($tsort, $this->get_columns_custom())) {
            if ($tdir == SORT_ASC) {
                $orderby = $tsort . ' ASC';
            } else {
                $orderby = $tsort . ' DESC';
            }

            return $orderby;
        } else {
            return parent::get_sql_sort();
        }
    }

    protected function sort_link($text, $column, $isprimary, $order) {
        $tsort = optional_param('tsort', '', PARAM_TEXT);
        $tdir = optional_param('tdir', 0, PARAM_INT);

        $sortable = [];
        if (!empty($tsort) && in_array($tsort, $this->get_columns_custom())) {
            $sortable[] = $tsort;
        }

        // If we are already sorting by this column, switch direction.
        if (in_array($column, $sortable)) {
            $sortorder = $tdir == SORT_ASC ? SORT_DESC : SORT_ASC;
        } else {
            $sortorder = SORT_ASC;
        }

        $params = [
                $this->request[TABLE_VAR_SORT] => $column,
                $this->request[TABLE_VAR_DIR] => $sortorder,
        ];

        return html_writer::link($this->baseurl->out(false, $params),
                        $text . get_accesshide(get_string('sortby') . ' ' .
                                $text . ' ' . $this->sort_order_name($isprimary, $sortorder)),
                        [
                                'data-sortable' => $this->is_sortable($column),
                                'data-sortby' => $column,
                                'data-sortorder' => $sortorder,
                        ]) . ' ' . $this->sort_icon($isprimary, $sortorder);
    }
}
