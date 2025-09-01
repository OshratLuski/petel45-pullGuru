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
 * @package   quiz_questionsoverview
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/report/competencyoverview/classes/datalib_questionoverview.php';

/**
 * This is a table subclass for displaying the quiz grades report.
 *
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_questionsoverview_table extends mod_quiz\local\reports\attempts_report_table {

    protected $regradedqs = array();
    protected $o = '';
    protected $prefs = [];
    public $lastaccess = null;
    protected $helpforheaders = [];

    /**
     * Constructor
     * @param object $quiz
     * @param context_module $context
     * @param string $qmsubselect
     * @param quiz_questionsoverview_options $options
     * @param \core\dml\sql_join $groupstudentsjoins
     * @param \core\dml\sql_join $studentsjoins
     * @param array $questions
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $qmsubselect,
        quiz_questionsoverview_options $options, \core\dml\sql_join $groupstudentsjoins,
        \core\dml\sql_join $studentsjoins, $questions, $reporturl) {
        parent::__construct('mod-quiz-report-questionsoverview-report', $quiz, $context,
            $qmsubselect, $options, $groupstudentsjoins, $studentsjoins, $questions, $reporturl);
    }

    public function build_table() {
        global $DB;

        if (!$this->rawdata) {
            return;
        }

        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();

        // End of adding the data from attempts. Now add averages at bottom.
        $this->add_separator();

        if (!empty($this->groupstudentsjoins->joins)) {
            $hasgroupstudents = $DB->record_exists_sql("
                    SELECT 1
                      FROM {user} u
                    {$this->groupstudentsjoins->joins}
                     WHERE {$this->groupstudentsjoins->wheres}
                    ", $this->groupstudentsjoins->params);
            if ($hasgroupstudents) {
                $this->add_average_row(get_string('groupavg', 'grades'), $this->groupstudentsjoins);
            }
        }

        if (!empty($this->studentsjoins->joins)) {
            $hasstudents = $DB->record_exists_sql("
                    SELECT 1
                      FROM {user} u
                    {$this->studentsjoins->joins}
                     WHERE {$this->studentsjoins->wheres}
                    ", $this->studentsjoins->params);
            if ($hasstudents) {
                $this->add_average_row(get_string('overallaverage', 'grades'), $this->studentsjoins);
            }
        }
    }

    /**
     * Calculate the average overall and question scores for a set of attempts at the quiz.
     *
     * @param string $label the title ot use for this row.
     * @param \core\dml\sql_join $usersjoins to indicate a set of users.
     * @return array of table cells that make up the average row.
     */
    public function compute_average_row($label, \core\dml\sql_join $usersjoins) {
        global $DB;

        list($fields, $from, $where, $params) = $this->base_sql($usersjoins);
        $record = $DB->get_record_sql("
                SELECT AVG(quizaouter.sumgrades) AS grade, COUNT(quizaouter.sumgrades) AS numaveraged
                  FROM {quiz_attempts} quizaouter
                  JOIN (
                       SELECT DISTINCT quiza.id
                         FROM $from
                        WHERE $where
                       ) relevant_attempt_ids ON quizaouter.id = relevant_attempt_ids.id
                ", $params);
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
            $qubaids = new qubaid_join("{quiz_attempts} quizaouter
                  JOIN (
                       SELECT DISTINCT quiza.id
                         FROM $from
                        WHERE $where
                       ) relevant_attempt_ids ON quizaouter.id = relevant_attempt_ids.id",
                'quizaouter.uniqueid', '1 = 1', $params);
            $avggradebyq = $dm->load_average_marks($qubaids, array_keys($this->questions));

            $averagerow += $this->format_average_grade_for_questions($avggradebyq);
        }

        return $averagerow;
    }

    /**
     * Add an average grade row for a set of users.
     *
     * @param string $label the title ot use for this row.
     * @param \core\dml\sql_join $usersjoins (joins, wheres, params) for the users to average over.
     */
    protected function add_average_row($label, \core\dml\sql_join $usersjoins) {
        $averagerow = $this->compute_average_row($label, $usersjoins);
        $this->add_data_keyed($averagerow);
    }

    /**
     * Helper userd by {@link add_average_row()}.
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
     * @param object $record with fields grade and numaveraged.
     * @param bool $question true if this is a question score, false if it is an overall score.
     * @return string HTML fragment for an average score (with number of things included in the average).
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
        return '';
    }

    public function col_sumgrades($attempt) {
        if ($attempt->state != \mod_quiz\quiz_attempt::FINISHED) {
            return '-';
        }

        $grade = quiz_rescale_grade($attempt->sumgrades, $this->quiz);
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
            array('title' => get_string('reviewattempt', 'quiz')));
    }

    /**
     * @param string $colname the name of the column.
     * @param object $attempt the row of data - see the SQL in display() in
     * mod/quiz/report/questionsoverview/report.php to see what fields are present,
     * and what they are called.
     * @return string the contents of the cell.
     */
    public function other_cols($colname, $attempt) {
        if (!preg_match('/^qsgrade(\d+)$/', $colname, $matches)) {
            return parent::other_cols($colname, $attempt);
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

    public function col_regraded($attempt) {
        if ($attempt->regraded == '') {
            return '';
        } else if ($attempt->regraded == 0) {
            return get_string('needed', 'quiz_questionsoverview');
        } else if ($attempt->regraded == 1) {
            return get_string('done', 'quiz_questionsoverview');
        }

        return '';
    }

    protected function update_sql_after_count($fields, $from, $where, $params) {
        $fields .= ", COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded";
        if ($this->options->onlyregraded) {
            $where .= " AND COALESCE((
                                    SELECT MAX(qqr.regraded)
                                      FROM {quiz_overview_regrades} qqr
                                     WHERE qqr.questionusageid = quiza.uniqueid
                                ), -1) <> -1";
        }
        return [$fields, $from, $where, $params];
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

    /**
     * Get all the questions in all the attempts being displayed that need regrading.
     * @return array A two dimensional array $questionusageid => $slot => $regradeinfo.
     */
    protected function get_regraded_questions() {
        global $DB;

        $qubaids = $this->get_qubaids_condition();
        $regradedqs = $DB->get_records_select('quiz_overview_regrades',
            'questionusageid ' . $qubaids->usage_id_in(), $qubaids->usage_id_in_params());
        return quiz_report_index_by_keys($regradedqs, array('questionusageid', 'slot'));
    }

    public function htmlout($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $DB;

        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
                $this->sql->params, IGNORE_MULTIPLE);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
            $this->define_columns(array_keys((array) $onerow));
            $this->define_headers(array_keys((array) $onerow));
        }
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->finish_output();

        return $this->o;
    }

    /**
     * This function is not part of the public api.
     */
    public function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
            //no data has been added to the table.
            $this->print_nothing_to_display();

        } else {
            // Print empty rows to fill the table to the current pagesize.
            // This is done so the header aria-controls attributes do not point to
            // non existant elements.
            $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            $this->o .= html_writer::end_tag('tbody');
            $this->o .= html_writer::end_tag('table');
            $this->o .= html_writer::end_tag('div');
            $this->wrap_html_finish();

        }
    }

    /**
     * Helper function, used by {@link print_initials_bar()} to output one initial bar.
     * @param array $alpha of letters in the alphabet.
     * @param string $current the currently selected letter.
     * @param string $class class name to add to this initial bar.
     * @param string $title the name to put in front of this initial bar.
     * @param string $urlvar URL parameter name for this initial.
     *
     * @deprecated since Moodle 3.3
     */
    protected function print_one_initials_bar($alpha, $current, $class, $title, $urlvar) {

    }

    /**
     * This function is not part of the public api.
     */
    public function print_initials_bar() {
        global $OUTPUT;

        if ((!empty($this->prefs['i_last']) || !empty($this->prefs['i_first']) || $this->use_initials)
            && isset($this->columns['fullname'])) {

            if (!empty($this->prefs['i_first'])) {
                $ifirst = $this->prefs['i_first'];
            } else {
                $ifirst = '';
            }

            if (!empty($this->prefs['i_last'])) {
                $ilast = $this->prefs['i_last'];
            } else {
                $ilast = '';
            }

            $prefixfirst = $this->request[TABLE_VAR_IFIRST];
            $prefixlast = $this->request[TABLE_VAR_ILAST];
            $this->o .= $OUTPUT->initials_bar($ifirst, 'firstinitial', get_string('firstname'), $prefixfirst, $this->baseurl);
            $this->o .= $OUTPUT->initials_bar($ilast, 'lastinitial', get_string('lastname'), $prefixlast, $this->baseurl);
        }

    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {

    }

    public function get_row_from_keyed($rowwithkeys) {
        if (is_object($rowwithkeys)) {
            $rowwithkeys = (array) $rowwithkeys;
        }
        $row = array();
        foreach (array_keys($this->columns) as $column) {
            if (isset($rowwithkeys[$column])) {
                $row[] = $rowwithkeys[$column];
            } else {
                $row[] = '';
            }
        }
        return $row;
    }

    /**
     * This function is not part of the public api.
     * You don't normally need to call this. It is called automatically when
     * needed when you start adding data to the table.
     *
     */
    public function start_output() {
        $this->started_output = true;
        if ($this->exportclass !== null) {
            $this->exportclass->start_table($this->sheettitle);
            $this->exportclass->output_headers($this->headers);
        } else {
            $this->start_html();
            $this->print_headers();
            $this->o .= html_writer::start_tag('tbody');
        }
    }

    /**
     * This function is not part of the public api.
     */
    public function print_row($row, $classname = '') {
        $this->o .= $this->get_row_html($row, $classname);
    }

    public function wrap_html_finish() {

    }

    public function download_buttons() {
        global $OUTPUT;
        return '';
    }

    protected function render_reset_button() {
        return '';
    }

    public function wrap_html_start() {
        // return '';

        if ($this->is_downloading() || !$this->includecheckboxes) {
            return;
        }

        $url = $this->options->get_url();

        $url->param('sesskey', sesskey());
        $url->param('display', 'full');

        $this->o .= '<div id="tablecontainer">';
        $this->o .= '<form id="attemptsform" method="post" action="' . $url->out_omit_querystring() . '">';

        $this->o .= html_writer::input_hidden_params($url);
        $this->o .= '<div>';
    }

    public function get_row_html($row, $classname = '') {
        static $suppress_lastrow = null;
        $rowclasses = array();

        if ($classname) {
            $rowclasses[] = $classname;
        }

        $rowid = $this->uniqueid . '_r' . $this->currentrow;
        $html = '';

        $html .= html_writer::start_tag('tr', array('class' => implode(' ', $rowclasses), 'id' => $rowid));

        // If we have a separator, print it
        if ($row === null) {
            $colcount = count($this->columns);
            $html .= html_writer::tag('td', html_writer::tag('div', '',
                array('class' => 'tabledivider')), array('colspan' => $colcount));

        } else {
            $colbyindex = array_flip($this->columns);
            foreach ($row as $index => $data) {
                $column = $colbyindex[$index];

                if (empty($this->prefs['collapse'][$column])) {
                    if ($this->column_suppress[$column] && $suppress_lastrow !== null && $suppress_lastrow[$index] === $data) {
                        $content = '&nbsp;';
                    } else {
                        $content = $data;
                    }
                } else {
                    $content = '&nbsp;';
                }

                $html .= html_writer::tag('td', $content, array(
                    'class' => 'cell c' . $index . $this->column_class[$column],
                    'id' => $rowid . '_c' . $index,
                    'style' => $this->make_styles_string($this->column_style[$column])));
            }
        }

        $html .= html_writer::end_tag('tr');

        $suppress_enabled = array_sum($this->column_suppress);
        if ($suppress_enabled) {
            $suppress_lastrow = $row;
        }
        $this->currentrow++;
        return $html;
    }

    public function start_html() {
        global $OUTPUT;

        $this->o .= html_writer::start_tag('div', array('class' => 'no-overflow'));
        $this->o .= html_writer::start_tag('table', $this->attributes);

    }

    public function print_headers() {
        global $CFG, $OUTPUT;

        $this->o .= html_writer::start_tag('thead');
        $this->o .= html_writer::start_tag('tr');
        foreach ($this->columns as $column => $index) {

            $icon_hide = '';
            if ($this->is_collapsible) {
                $icon_hide = $this->show_hide_link($column, $index);
            }

            $primarysortcolumn = '';
            $primarysortorder = '';

            switch ($column) {

                case 'fullname':
                    // Check the full name display for sortable fields.
                    $nameformat = $CFG->fullnamedisplay;
                    if ($nameformat == 'language') {
                        $nameformat = get_string('fullnamedisplay');
                    }

                    $alternatenames = [];
                    foreach (\core_user\fields::get_name_fields() as $field) {
                        $alternatenames[$field] = $field;
                    }

                    $requirednames = order_in_string($alternatenames, $nameformat);

                    if (!empty($requirednames)) {
                        if ($this->is_sortable($column)) {
                            // Done this way for the possibility of more than two sortable full name display fields.
                            $this->headers[$index] = '';
                            foreach ($requirednames as $name) {
                                $sortname = $this->sort_link(get_string($name),
                                    $name, $primarysortcolumn === $name, $primarysortorder);
                                $this->headers[$index] .= $sortname . ' / ';
                            }
                            $helpicon = '';
                            if (isset($this->helpforheaders[$index])) {
                                $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                            }
                            $this->headers[$index] = substr($this->headers[$index], 0, -3) . $helpicon;
                        }
                    }
                    break;

                case 'userpic':
                    // do nothing, do not display sortable links
                    break;

                default:
                    if ($this->is_sortable($column)) {
                        $helpicon = '';
                        if (isset($this->helpforheaders[$index])) {
                            $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                        }
                        $this->headers[$index] = $this->sort_link($this->headers[$index],
                            $column, $primarysortcolumn == $column, $primarysortorder) . $helpicon;
                    }
            }

            $attributes = array(
                'class' => 'header c' . $index . $this->column_class[$column],
                'scope' => 'col',
            );
            if ($this->headers[$index] === null) {
                $content = '&nbsp;';
            } else if (!empty($this->prefs['collapse'][$column])) {
                $content = $icon_hide;
            } else {
                if (is_array($this->column_style[$column])) {
                    $attributes['style'] = $this->make_styles_string($this->column_style[$column]);
                }
                $helpicon = '';
                if (isset($this->helpforheaders[$index]) && !$this->is_sortable($column)) {
                    $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                }
                $content = $this->headers[$index] . $helpicon . html_writer::tag('div',
                    $icon_hide, array('class' => 'commands'));
            }
            $this->o .= html_writer::tag('th', $content, $attributes);
        }

        $this->o .= html_writer::end_tag('tr');
        $this->o .= html_writer::end_tag('thead');
    }

    /**
     * Generate the HTML for the sort icon. This is a helper method used by {@link sort_link()}.
     * @param bool $isprimary whether an icon is needed (it is only needed for the primary sort column.)
     * @param int $order SORT_ASC or SORT_DESC
     * @return string HTML fragment.
     */
    protected function sort_icon($isprimary, $order) {
        global $OUTPUT;

        if (!$isprimary) {
            return '';
        }

        if ($order == SORT_ASC) {
            return $OUTPUT->pix_icon('t/sort_asc', get_string('asc'));
        } else {
            return $OUTPUT->pix_icon('t/sort_desc', get_string('desc'));
        }
    }

    /**
     * Generate the correct tool tip for changing the sort order. This is a
     * helper method used by {@link sort_link()}.
     * @param bool $isprimary whether the is column is the current primary sort column.
     * @param int $order SORT_ASC or SORT_DESC
     * @return string the correct title.
     */
    protected function sort_order_name($isprimary, $order) {
        if ($isprimary && $order != SORT_ASC) {
            return get_string('desc');
        } else {
            return get_string('asc');
        }
    }

    /**
     * Generate the HTML for the sort link. This is a helper method used by {@link print_headers()}.
     * @param string $text the text for the link.
     * @param string $column the column name, may be a fake column like 'firstname' or a real one.
     * @param bool $isprimary whether the is column is the current primary sort column.
     * @param int $order SORT_ASC or SORT_DESC
     * @return string HTML fragment.
     */
    protected function sort_link($text, $column, $isprimary, $order) {
        return html_writer::link($this->baseurl->out(false,
            array($this->request[TABLE_VAR_SORT] => $column)),
            $text . get_accesshide(get_string('sortby') . ' ' .
                $text . ' ' . $this->sort_order_name($isprimary, $order))) . ' ' .
        $this->sort_icon($isprimary, $order);
    }

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

        if ($this->lastaccess == 0) {
            $sort = 'min';
        } else {
            $sort = 'max';
        }
        $from .= "\nLEFT JOIN
                        (
                        select userid, " . $sort . "(uniqueid) uniqueid, quiz, id, preview, state,
                            attempt, timefinish, timestart, sumgrades
                        from {quiz_attempts}
                        group by userid, quiz
                        )
                        quiza ON
                            quiza.userid = u.id AND quiza.quiz = :quizid";

        $params = array('quizid' => $this->quiz->id);

        if ($this->qmsubselect && $this->options->onlygraded) {
            $from .= " AND (quiza.state <> :finishedstate OR $this->qmsubselect)";
            $params['finishedstate'] = mod_quiz\quiz_attempt::FINISHED;
        }

        switch ($this->options->attempts) {
            case mod_quiz\local\reports\attempts_report::ALL_WITH:
                // Show all attempts, including students who are no longer in the course.
                $where = 'quiza.id IS NOT NULL AND quiza.preview = 0';
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_WITH:
                // Show only students with attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "quiza.preview = 0 AND quiza.id IS NOT NULL AND " . $allowedstudentsjoins->wheres;
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_WITHOUT:
                // Show only students without attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "quiza.id IS NULL AND " . $allowedstudentsjoins->wheres;
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
            case mod_quiz\local\reports\attempts_report::ENROLLED_ALL:
                // Show all students with or without attempts.
                $from .= "\n" . $allowedstudentsjoins->joins;
                $where = "(quiza.preview = 0 OR quiza.preview IS NULL) AND " . $allowedstudentsjoins->wheres;
                $params = array_merge($params, $allowedstudentsjoins->params);
                break;
        }

        if ($this->options->states) {
            list($statesql, $stateparams) = $DB->get_in_or_equal($this->options->states,
                SQL_PARAMS_NAMED, 'state');
            $params += $stateparams;
            $where .= " AND (quiza.state $statesql OR quiza.state IS NULL)";
        }

        return array($fields, $from, $where, $params);
    }

    /**
     * Load information about the latest state of selected questions in selected attempts.
     *
     * The results are returned as an two dimensional array $qubaid => $slot => $dataobject
     *
     * @param qubaid_condition|null $qubaids used to restrict which usages are included
     * in the query. See {@link qubaid_condition}.
     * @return array of records. See the SQL in this function to see the fields available.
     */
    protected function load_question_latest_steps(qubaid_condition $qubaids = null) {
        if ($qubaids === null) {
            $qubaids = $this->get_qubaids_condition();
        }
        $dm = new question_engine_data_mapper_qiestionoverview();
        if ($this->lastaccess == 0) {
            $latesstepdata = $dm->load_questions_usages_firstest_steps(
                $qubaids, array_keys($this->questions));
        } else {
            $latesstepdata = $dm->load_questions_usages_latest_steps(
                $qubaids, array_keys($this->questions));
        }

        $lateststeps = array();
        foreach ($latesstepdata as $step) {
            $lateststeps[$step->questionusageid][$step->slot] = $step;
        }

        return $lateststeps;
    }

}
