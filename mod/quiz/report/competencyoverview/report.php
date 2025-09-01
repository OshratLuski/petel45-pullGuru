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
 * This file defines the quiz overview report class.
 *
 * @package     quiz_competencyoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/quiz/report/competencyoverview/competencyoverview_table.php';
require_once $CFG->dirroot . '/mod/quiz/report/competencyoverview/locallib.php';

/**
 * Quiz report subclass for the competencyoverview report.
 *
 * @package     quiz_competencyoverview
 * @copyright   2020 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_competencyoverview_report extends mod_quiz\local\reports\attempts_report {

    protected $displayfull = true;
    public $lastaccess = 0;
    public $notgraded = 0;
    protected $hlfilterranges = [];
    protected $hlfilterrangescolor = [];
    protected $fullskills = [];
    protected $competencies = [];
    protected $questions = [];
    protected $quiz;
    protected $cm;
    protected $course;
    protected $users;
    public $actualusers = [];

    private function get_users() {
        global $DB;

        // Get enrolled users only with role 'student'.
        $sql = "SELECT u.*
                FROM {course} c
                JOIN {context} ct ON c.id = ct.instanceid
                JOIN {role_assignments} ra ON ra.contextid = ct.id
                JOIN {user} u ON u.id = ra.userid
                JOIN {role} r ON r.id = ra.roleid
                WHERE c.id = ? AND u.id NOT IN (
                    SELECT u.id
                    FROM {course} c
                            JOIN {context} ct ON c.id = ct.instanceid
                            JOIN {role_assignments} ra ON ra.contextid = ct.id
                            JOIN {user} u ON u.id = ra.userid
                            JOIN {role} r ON r.id = ra.roleid
                    WHERE c.id = ?
                    AND r.shortname != 'student'
                )
            ";

        return $DB->get_records_sql($sql, [$this->course->id, $this->course->id]);
    }

    public function display($quiz, $cm, $course) {
        global $DB, $OUTPUT, $PAGE, $CFG, $USER;

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;
        $this->users = $this->get_users();

        $highstring = get_string('high', 'quiz_competencyoverview');
        $medstring = get_string('med', 'quiz_competencyoverview');
        $lowstring = get_string('low', 'quiz_competencyoverview');

        // Ranges for filtering.
        $this->hlfilterranges = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            // 1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 75, $lowstring, 'grade-low-cell'], // Low.
        ];

        // Ranges for color highlihting.
        $this->hlfilterrangescolor = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 55, $lowstring, 'grade-low-cell'], // Low.
        ];

        if (optional_param('display', 'basic', PARAM_TEXT) == 'basic') {
            $this->displayfull = false;
        }

        if (optional_param('lastaccess', 0, PARAM_INT) == 1) {
            $this->lastaccess = 1;
        }

        if (optional_param('notgraded', 0, PARAM_INT) == 1) {
            $this->notgraded = 1;
        }

        $this->mode = 'competencyoverview';
        $this->context = context_module::instance($cm->id);
        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) = $this->get_students_joins($cm, $course);
        $this->print_header_and_tabs($cm, $course, $quiz, $this->mode);

        $hasstudents = false;
        if (!empty($studentsjoins->joins)) {
            $sql = "SELECT DISTINCT u.id
                    FROM {user} u
                    $studentsjoins->joins
                    WHERE $studentsjoins->wheres";
            $hasstudents = $DB->record_exists_sql($sql, $studentsjoins->params);
        }

        $hasquestions = quiz_has_questions($quiz->id);
        if (!$hasquestions) {
            echo quiz_no_questions_message($quiz, $cm, $this->context);
            return;
        } else if (!$hasstudents) {
            echo $OUTPUT->notification(get_string('nostudentsyet'), 'warning');
            return;
        } else if ($currentgroup && !$this->hasgroupstudents) {
            echo $OUTPUT->notification(get_string('nostudentsingroup'), 'warning');
            return;
        }
        echo get_string('pleaseselectstudents', 'quiz_competencyoverview');

        // Data.

        // All competencies.
        $this->questions = quiz_report_get_significant_questions($quiz);
        $attempts = $this->get_attempts($quiz, $this->lastaccess);

        $questionusageids = [];
        $questionswithgrades = [];
        if ($attempts) {
            foreach ($attempts as $att) {
                $questionusageids[] = $att->maxuniqueid;
            }
            $questionswithgrades = $this->get_graded_questions($questionusageids);
        } else {
            // Empty report.
            echo $OUTPUT->notification(get_string('noattempts', 'quiz_competencyoverview'), 'warning');
        }

        $this->competencies = quiz_competencyoverview_get_competencies_by_questions($this->questions);
        if (!$this->competencies) {
            echo $OUTPUT->notification(get_string('nocompetency', 'quiz_competencyoverview'), 'warning');
            return;
        }

        $this->fullskills = $this->get_full_skills($this->competencies, $this->questions, $questionswithgrades, $this->users);

        $head = [];
        $columns = [];
        $keys = [];
        $colid = 0;

        $head[] = $OUTPUT->render_from_template('quiz_competencyoverview/head_table', ['colid' => $colid]);

        $columns[] = 'description';

        $complist = [];

        $counter = 0;
        foreach ($this->fullskills as $key => $skill) {
            $kkeys = str_replace(' ', '-', explode('--', $key)[0]);
            $compid = str_replace(' ', '-', explode('--', $key)[1]);
            $complist[$compid] = explode('--', $key)[0];
            list($numquest, $qset) = $this->get_num_questions_by_competency($compid);

            $competency = $this->get_competency($compid);
            $colid = "";
            if ($competency->parentid) {
                $competencyparent = $this->get_competency($competency->parentid);
                $colid = $competencyparent->shortname;
            }
            // HL filter ranges.
            $htmlranges = get_string('select', 'quiz_competencyoverview') . ' ';
            $c = 0;
            foreach ($this->hlfilterranges as $k => $range) {
                $htmlranges .= '<a style="text-decoration: none;" href="#" class="selected-ranges" data-range="' . $k . '" class="m-r-2">' . $range[2] . '</a>';
                $c += 1;
                if (count($this->hlfilterranges) != $c) {
                    $htmlranges .= ' | ';
                }
            }
            $counter = $counter + 1;
            $key = $this->clean_competency_name($key);
            $colid = $this->clean_competency_name($colid);
            $head[] = $OUTPUT->render_from_template('quiz_competencyoverview/head_table_skill', [
                'colid' => $colid,
                'columnid' => $counter,
                'key' => explode('--', $key)[0],
                'htmlranges' => $htmlranges,
                'keys' => $kkeys,
                'numquest' => $numquest,
                'compid' => $compid,
                'qset' => $qset,
                'classsuccess' => round($skill['classsuccess'][0]),
            ]);
            $columns[] = 'skill_' . $kkeys;
        }
        foreach (reset($this->fullskills) as $kk => $name) {
            if (isset($this->users[$kk])) {
                $keys[$kk] = $this->users[$kk]->firstname . ' ' . $this->users[$kk]->lastname;
            } else {
                $keys[$kk] = $kk;
            }
        }

        $data = [];

        foreach ($keys as $userid => $name) {
            $row = [];
            $i = 0;
            $row[$columns[$i]] = format_string($name); // Column index.
            $row['userid'] = $userid;
            foreach ($this->fullskills as $skill => $grade) {
                $i++;
                $row[$columns[$i]] = $grade[$userid];
            }
            array_push($data, $row);
        }

        // Table.
        $table = new quiz_competencyoverview_flexible_table('mod-quiz-report-competencyverview-report');

        $table->gradesreportbuttonurl = new moodle_url($CFG->wwwroot . '/mod/quiz/report.php?id=' . $cm->id . '&mode=advancedoverview', array('display' => 'full', 'lastaccess' => $this->lastaccess));
        $table->define_columns($columns);
        $table->define_headers($head);
        $table->define_baseurl($CFG->wwwroot . '/mod/quiz/report.php?id=' . $cm->id . '&mode=competencyoverview');
        $table->sortable(false);
        $table->collapsible(false);
        $table->set_attribute('id', 'mod-quiz-report-competencyoverview-report-table');
        $table->pagesize = 0;
        $table->setup();
        $table->format_and_add_array_of_rows($data); // Column index.

        // Table.
        $assign = get_string('assign_activities', 'quiz_competencyoverview');

        // Only ID/FN/LN fileds in users list.
        $filteredusers = [];
        foreach ($this->users as $key => $u) {
            $filteredusers[$key] = array_intersect_key(get_object_vars($u), array_flip(['id', 'firstname', 'lastname']));
        }

        // Top skills.
        $topskills = array_map(function ($a) {
            return explode('--', $a)[1];
        }, array_keys(array_slice($this->fullskills, 0, 3)));

        echo $OUTPUT->render_from_template('quiz_competencyoverview/modal', []);

        $PAGE->requires->js_call_amd(
            'quiz_competencyoverview/table',
            'load',
            [
                'quizid' => $quiz->id,
                'cmid' => $cm->id,
                'courseid' => $course->id,
                'lastaccess' => $this->lastaccess,
                'actualusers' => implode(',', $this->actualusers),
            ]
        );

        return true;
    }

    public function get_init_params_report($quiz, $cm, $course) {
        global $DB, $OUTPUT, $PAGE, $CFG, $USER;

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;
        $this->users = $this->get_users();

        $highstring = get_string('high', 'quiz_competencyoverview');
        $medstring = get_string('med', 'quiz_competencyoverview');
        $lowstring = get_string('low', 'quiz_competencyoverview');

        // Ranges for filtering.
        $this->hlfilterranges = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            // 1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 75, $lowstring, 'grade-low-cell'], // Low.
        ];

        // Ranges for color highlihting.
        $this->hlfilterrangescolor = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 55, $lowstring, 'grade-low-cell'], // Low.
        ];

        if (optional_param('lastaccess', 0, PARAM_INT) == 1) {
            $this->lastaccess = 1;
        }

        $this->mode = 'competencyoverview';
        $this->context = context_module::instance($cm->id);

        // All competencies.
        $this->questions = quiz_report_get_significant_questions($quiz);
        $attempts = $this->get_attempts($quiz, $this->lastaccess);

        $questionusageids = [];
        $questionswithgrades = [];
        if ($attempts) {
            foreach ($attempts as $att) {
                $questionusageids[] = $att->maxuniqueid;
            }
            $questionswithgrades = $this->get_graded_questions($questionusageids);
        }

        $this->competencies = quiz_competencyoverview_get_competencies_by_questions($this->questions);

        $this->fullskills = $this->get_full_skills($this->competencies, $this->questions, $questionswithgrades, $this->users);

        $complist = [];

        $counter = 0;
        foreach ($this->fullskills as $key => $skill) {
            $compid = str_replace(' ', '-', explode('--', $key)[1]);
            $complist[$compid] = explode('--', $key)[0];
        }
        foreach (reset($this->fullskills) as $kk => $name) {
            if (isset($this->users[$kk])) {
                $keys[$kk] = $this->users[$kk]->firstname . ' ' . $this->users[$kk]->lastname;
            } else {
                $keys[$kk] = $kk;
            }
        }

        // Table.
        $assign = get_string('assign_activities', 'quiz_competencyoverview');

        // Only ID/FN/LN fileds in users list.
        $filteredusers = [];
        foreach ($this->users as $key => $u) {
            $filteredusers[$key] = array_intersect_key(get_object_vars($u), array_flip(['id', 'firstname', 'lastname']));
        }

        // Top skills.
        $topskills = array_map(function ($a) {
            return explode('--', $a)[1];
        }, array_keys(array_slice($this->fullskills, 0, 3)));

        $params =
            [
            'hlfilterranges' => $this->hlfilterranges,
            'hlfilterrangescolor' => $this->hlfilterrangescolor,
            'assign' => $assign,
            'users' => $filteredusers,
            'courseid' => $cm->course,
            'topskills' => $topskills,
            'quizid' => $this->cm->instance,
            'cmid' => $this->cm->id,
            'complist' => $complist,
            'lastaccess' => $this->lastaccess,
        ];

        return $params;
    }

    /*
     * Clean "chapter X.Y.Z.N ..." from Hebrew competency name.
     */
    public function clean_competency_name($competency_name) {
        $pattern = '/(\d|\.\d)/mu';
        $competency_name = preg_replace($pattern, '', $competency_name);
        $competency_name = str_replace('פרק ', ' - ', $competency_name);
        return $competency_name;
    }

    public function get_num_questions_by_competency($compid) {
        $numquest = count($this->competencies[$compid]);
        $qset = implode(',', $this->competencies[$compid]);

        return [$numquest, $qset];
    }

    protected function get_attempts($quiz, $result) {
        global $DB;

        switch ($result) {
            case 0:
                $sql = "
                    select userid, min(uniqueid) maxuniqueid
                    from {quiz_attempts}
                    where quiz = :quiz1
                    and state = 'finished'
                    group by userid
                    ";
                break;
            case 1:
                $sql = "
                    select userid, max(uniqueid) maxuniqueid
                    from {quiz_attempts}
                    where quiz = :quiz1
                    and state = 'finished'
                    group by userid
                    ";
                break;
            default:
                break;
        }

        $params = array('quiz1' => $quiz->id, 'quiz2' => $quiz->id);

        $attempts = $DB->get_records_sql($sql, $params);

        return $attempts;
    }

    protected function get_graded_questions($questionusageids) {
        global $DB;

        $sort = $this->lastaccess == 1 ? "DESC" : "ASC";
        $params = [];
        $params[] = $this->quiz->id;

        $sql = "SELECT
                    steps.id AS id,
                    steps.questionattemptid,
                    steps.sequencenumber,
                    steps.state,
                    steps.fraction,
                    qv.questionid,
                    qs.slot,
                    qa.userid AS userid
                FROM
                    {question_attempts} att
                    JOIN {question_attempt_steps} steps ON steps.questionattemptid = att.id
                        AND steps.sequencenumber = (
                                SELECT MAX(sequencenumber)
                                FROM {question_attempt_steps} st2
                                WHERE st2.questionattemptid = att.id
                            )
                    JOIN {quiz_attempts} qa ON qa.uniqueid = att.questionusageid
                        AND att.questionusageid IN (" . implode(', ', $questionusageids) . ")
                    JOIN {quiz_slots} qs ON qs.quizid = qa.quiz and att.slot = qs.slot
                    JOIN {question_references} qr ON qr.itemid = qs.id
                        AND qr.component = 'mod_quiz'
                        AND qr.questionarea = 'slot'
                    JOIN {question_versions} qv ON qv.questionbankentryid = qr.questionbankentryid
                        AND (
                            (
                                qr.version IS NULL
                                AND qv.version = (
                                    SELECT
                                        MAX(version)
                                    FROM
                                        {question_versions} qv2
                                    WHERE
                                        qv2.questionbankentryid = qr.questionbankentryid
                                )
                            )
                            OR (
                                qr.version IS NOT NULL
                                AND qv.version = qr.version
                            )
                        )
                WHERE
                    steps.fraction IS NOT NULL
                ORDER BY steps.questionattemptid " . $sort . ", steps.id " . $sort;

        $attempts = $DB->get_records_sql($sql, $params);

        $result = [];
        $seenUsersQuestions = [];
        foreach ($attempts as $attempt) {
            $key = $attempt->userid . '-' . $attempt->questionid;
            if (!isset($seenUsersQuestions[$key])) {
                $seenUsersQuestions[$key] = true;
                $result[] = $attempt;
            }
        }

        return $result;
    }

    protected function get_competency($competencyid) {
        global $DB;

        $competency = $DB->get_record('competency', array('id' => $competencyid));

        return $competency;
    }

    protected function get_full_skills($competencies, $questions, $questionswithgrades, $users) {

        $fullskills = [];
        $defaultskillgrade = 0;
        $deafultgradedright = 0;

        // Max grade in Quiz.
        $quizmaxgrade = 100;

        foreach ($competencies as $compid => $questset) {
            $countcountquestset = count($questset);
            $skillgrade = [];

            // Max mark quiz slot count for competency.
            $allmaxmarks = 0;
            foreach ($questset as $q) {
                foreach ($questions as $key => $question) {
                    if ($q == $question->id) {
                        $maxmark = $question->maxmark;
                    }
                }
                $allmaxmarks = $allmaxmarks + $maxmark;
                $gradedright = 1;
                foreach ($questionswithgrades as $qg) {
                    $questweight = $maxmark * $qg->fraction;

                    $filter = array_filter($users, function ($u) use ($qg) {
                        return ($u->id == $qg->userid);
                    });
                    if ($filter) {
                        if (!isset($skillgrade[$qg->userid][0])) {
                            $skillgrade[$qg->userid][0] = $defaultskillgrade;
                            $skillgrade[$qg->userid][1] = $deafultgradedright;
                            $skillgrade[$qg->userid][2] = $q == 0 ? 0 : $countcountquestset;
                            $skillgrade[$qg->userid][3] = 1;
                        }
                        if ($qg->questionid == $q) {
                            $actualquestweight = $questweight;
                            $actualgradedright = $gradedright;
                            $skillgrade[$qg->userid][0] = $skillgrade[$qg->userid][0] + $actualquestweight;
                            $skillgrade[$qg->userid][1] = $skillgrade[$qg->userid][1] + $actualgradedright;
                        }
                    }
                }
            }

            // Convert skill grade according quiz max grade.
            foreach ($skillgrade as $key => $sg) {
                if ($allmaxmarks != 0) {
                    $skillgrade[$key][0] = $skillgrade[$key][0] / $allmaxmarks * $quizmaxgrade;
                } else {
                    $skillgrade[$key][0] = 0;
                }
            }

            $competency = $this->get_competency($compid);
            $parentcompetencyshortname = '';
            // Check for parent competency.
            if ($parentcompetency = $this->get_competency($competency->parentid)) {
                $parentcompetencyshortname = $this->get_competency($competency->parentid)->shortname . ' ';
            }
            $fullskills[$parentcompetencyshortname . $competency->shortname . "--" . $compid] = $skillgrade;
        }

        reset($fullskills);
        $firstfullskill = $fullskills[key($fullskills)];
        $diff = array_diff_key($users, $firstfullskill);

        if ($this->notgraded == 1) {
            // Add other not graded users.
            foreach ($fullskills as $skillid => $stud) {
                foreach ($diff as $user) {
                    $stud[$user->id][0] = 0;
                    $stud[$user->id][1] = 0;
                    $stud[$user->id][2] = 0;
                    $stud[$user->id][3] = 0;
                }
                $fullskills[$skillid] = $stud;
            }
        }

        // Class Success.
        $submitteduserscount = 0;
        $skillcount = 0;
        foreach ($fullskills as $skillid => $stud) {
            $sumsuccessclassstud = 0;
            $skillcount++;
            foreach ($users as $user) {
                if (isset($stud[$user->id][0]) && $stud[$user->id][3] != 0) {
                    if ($skillcount == 1) {
                        $submitteduserscount++;
                    }
                    $sumsuccessclassstud = $stud[$user->id][0] + $sumsuccessclassstud;
                }
            }
            if ($submitteduserscount != 0) {
                $fullskills[$skillid]['classsuccess'][0] = $sumsuccessclassstud / $submitteduserscount;
            } else {
                $fullskills[$skillid]['classsuccess'][0] = 0;
            }
        }

        // Class Score.
        foreach ($fullskills as $skillid => $stud) {
            $score = 0;
            foreach ($users as $user) {
                if (isset($stud[$user->id][0]) && $stud[$user->id][0] >= 70) {
                    $score++;
                }
            }
            if ($submitteduserscount != 0) {
                $fullskills[$skillid]['classscore'][1] = $score;
                $fullskills[$skillid]['classscore'][2] = $submitteduserscount;
                $a = $fullskills[$skillid]['classscore'][1];
                $b = $fullskills[$skillid]['classscore'][2];
            } else {
                $fullskills[$skillid]['classscore'][1] = 0;
                $fullskills[$skillid]['classscore'][2] = 0;
            }

        }

        // Reoreder stats.
        $newfullskills = [];
        foreach ($fullskills as $name => $skill) {
            $newfullskill = [];
            foreach ($skill as $key => $value) {
                if ($key == 'classsuccess') {
                    $newfullskill[$key] = $value;
                    unset($skill[$key]);
                } else if ($key == 'classscore') {
                    $newfullskill[$key] = $value;
                    unset($skill[$key]);
                }
            }
            foreach ($skill as $key => $value) {
                $newfullskill[$key] = $value;
                array_push($this->actualusers, $key);
            }
            $newfullskills[$name] = $newfullskill;
        }

        // Sort by classsuccess.
        uasort($newfullskills, function ($item1, $item2) {
            return $item1['classsuccess'] <=> $item2['classsuccess'];
        });
        $this->actualusers = object_array_unique($this->actualusers);
        return $newfullskills;
    }

    /**
     * get_brief_competencies
     *
     * @param  mixed $quiz
     * @param  mixed $cm
     * @param  mixed $course
     *
     * @return array
     */
    public function get_brief_competencies($quiz, $cm, $course) {

        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;
        $this->users = $this->get_users();

        $skills = [];

        $highstring = get_string('high', 'quiz_competencyoverview');
        $medstring = get_string('med', 'quiz_competencyoverview');
        $lowstring = get_string('low', 'quiz_competencyoverview');

        // Ranges for filtering.
        $this->hlfilterranges = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            // 1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 75, $lowstring, 'grade-low-cell'], // Low.
        ];

        // Ranges for color highlihting.
        $this->hlfilterrangescolor = [
            0 => [75, 100, $highstring, 'bg-success'], // High.
            1 => [55, 75, $medstring, 'grade-med-cell'], // Med.
            2 => [0, 55, $lowstring, 'grade-low-cell'], // Low.
        ];

        if (optional_param('display', 'basic', PARAM_TEXT) == 'basic') {
            $this->displayfull = false;
        }

        $this->mode = 'competencyoverview';
        $this->context = context_module::instance($cm->id);

        // Data.

        // All competecies.
        $this->questions = quiz_report_get_significant_questions($quiz);
        $attempts = $this->get_attempts($quiz, $this->lastaccess);

        $questionusageids = [];
        $questionswithgrades = [];
        if ($attempts) {
            foreach ($attempts as $att) {
                $questionusageids[] = $att->maxuniqueid;
            }
            $questionswithgrades = $this->get_graded_questions($questionusageids);
        } else {
            // Empty report.
            return [];
        }

        $this->competencies = quiz_competencyoverview_get_competencies_by_questions($this->questions);
        if (!$this->competencies) {
            //echo $OUTPUT->notification(get_string('nocompetency', 'quiz_competencyoverview'), 'warning');
            return [];
        }

        $this->fullskills = $this->get_full_skills($this->competencies, $this->questions, $questionswithgrades, $this->users);

        foreach ($this->fullskills as $key => $skill) {
            $compid = str_replace(' ', '-', explode('--', $key)[1]);
            list($numquest, $qset) = $this->get_num_questions_by_competency($compid);
            $rate = $skill['classsuccess'][0];
            foreach ($this->hlfilterrangescolor as $range) {
                if ($rate >= $range[0] && $rate <= $range[1]) {
                    $class = $range[3];
                }
            }
            $sk = new stdClass();
            $key = $this->clean_competency_name($key);
            $sk->name = explode('--', $key)[0];
            $sk->numquestions = $numquest;
            $sk->rate = round($rate);
            $sk->colorclass = $class;
            $skills[] = $sk;
        }

        return $skills;
    }

}
