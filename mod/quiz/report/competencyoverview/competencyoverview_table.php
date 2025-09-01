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
 * Quiz competencyoverview report, table for showing competencyoverview of each question in the quiz.
 *
 * @package   quiz_competencyoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\output\html_writer;

require_once($CFG->libdir . '/tablelib.php');

/**
 * This table has one row for each question in the quiz, with sub-rows when
 * random questions and variants appear.
 *
 * There are columns for the various item and position competencyoverview.
 *
 * @copyright 2008 Jamie Pratt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_competencyoverview_flexible_table extends flexible_table {
    /** @var object the quiz settings. */
    protected $quiz;

    /** @var integer the quiz course_module id. */
    protected $cmid;

    public $gradesreportbuttonurl;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('mod-quiz-report-competencyoverview-report');
    }

    public function other_cols($column, $row) {

        $cell = '';

        if (strpos($column, 'skill_') !== false) {
            if ($row->description == 'classscore') {
                $cell .= html_writer::div(($row->$column)[1] . '/' . ($row->$column)[2], 'classscore-cell', ['']);
            } else if ($row->description == 'classsuccess') {
                $cell .= html_writer::div(round(($row->$column)[0]), 'classsuccess-cell');
            } else {
                $dataskillname = str_replace(' ', '-', explode('--', $column)[0]);
                $cellattr      = [
                    'data-description'       => $row->description,
                    'data-skill-name'        => $dataskillname,
                    'data-skill-grade'       => ($row->$column)[3] == 0 ? "-" : round(($row->$column)[0]),
                    'data-skill-grade-right' => ($row->$column)[1],
                    'data-skill-grade-all'   => ($row->$column)[2],
                    'data-skill-submitted'   => ($row->$column)[3],
                ];
                $cellclass = ' skill-grade-cell ';

                if (($row->$column)[3] == 1) {
                    $tooltip                 = new stdClass();
                    $tooltip->correctanswers = ($row->$column)[1];
                    $tooltip->allquestions   = ($row->$column)[2];
                    $strtooltip              = get_string('correctallration', 'quiz_competencyoverview', $tooltip);
                    $percent                 = '<span class="percent">%</span>';
                    $score                   = html_writer::tag('div', $percent, ['title' => $strtooltip,
                        'data-placement'                                                      => 'top', 'class' => 'percent-wrapper', 'data-toggle' => 'tooltip']);
                    $cell .= html_writer::div(round(($row->$column)[0]) . $score, $cellclass, $cellattr);
                } else if (($row->$column)[3] == 0) {
                    $score = html_writer::span('', 'text-muted no-data');
                    $cell .= html_writer::div($score, $cellclass, $cellattr);
                }

            }

        } else {
            return null;
        }

        return $cell;
    }

    public function col_description($row) {
        global $COURSE;

        $out = '';

        if ($row->userid == 'classsuccess' || $row->userid == 'classscore') {
            $out .= get_string($row->userid, 'quiz_competencyoverview');
        } else {
            if ($COURSE->id == SITEID) {
                $profileurl = new moodle_url('/user/profile.php', array('id' => $row->userid));
            } else {
                $profileurl = new moodle_url('/user/view.php',
                    array('id' => $row->userid, 'course' => $COURSE->id));
            }

            $out .= html_writer::checkbox('selected-users', '', false, '',
                ['data-selected-userid' => $row->userid, 'id' => 'user-name-' . $row->userid, 'class' => 'selected-users selected-comp custom-checkbox mr-1']);
            $out .= html_writer::tag('label', '', ['for' => 'user-name-' . $row->userid, 'class' => 'title']);
            $out .= '&nbsp';
            $out .= html_writer::link($profileurl, $row->description);
        }

        return $out;
    }

    public function format_and_add_array_of_rows($rowstoadd, $finish = true) {
        foreach ($rowstoadd as $row) {
            if (is_null($row)) {
                $this->add_separator();
            } else {
                $class = '';
                if ($row['description'] == 'classsuccess' || $row['description'] == 'classscore') {
                    $class .= 'gradedattempt';
                }
                $this->add_data_keyed($this->format_row($row), $class);
            }
        }
        if ($finish) {
            $this->finish_output(!$this->is_downloading());
        }
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_start() {
        echo '
        <div class="container-fluid">
            <div class="m-t-4" style="background-color: transparent;">
                <div class="mb-10">
                    <button type="button" title="' .
        get_string('assign_activities', 'quiz_competencyoverview') . '"id="user-action" class="btn btn-primary mb-3"
                    style="display: ;">'
        . get_string('assign_activities', 'quiz_competencyoverview') . '</button>
                     <a href="' . $this->gradesreportbuttonurl . '" class="btn btn-secondarymedium pull-right">' . get_string('teacheroverviewreport', 'quiz_competencyoverview') . '</a>
                </div>
            </div>
        </div>
        ';
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    public function wrap_html_finish() {
    }

}
