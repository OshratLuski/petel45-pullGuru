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

namespace quiz_advancedoverview;

use context_course;
use moodle_url;
use stdClass;
use core_user;
use quiz_competencyoverview_report;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/report/competencyoverview/report.php');

class quizdata {

    public $course;
    public $cm;
    public $groupid;
    public $groups;
    public $questions;
    public $participants;
    public $participantsids;
    public $usersattempts;
    public $chartaverage;
    public $chartstate;
    public $chartgrade;
    public $enrolleduserscount;
    public $usersfinished;
    public $usersinprogress;
    public $usersnotstarted;
    public $usersfinishedlist;
    public $usersinprogresslist;
    public $usersnotstartedlist;
    public $quizobj;
    public $students;
    public $questionids;
    public $skills;
    public $quiz;
    public $options;
    public $states;
    public $config;
    public $slots;
    public $openquestions = [];
    public $openquestionslist = [];
    public $childopenquestionslist = [];
    public $anonymouscount = 1;
    public $qdisabledviewtypes = ['description'];

    private $tablestudentsdata = [];

    public function __construct($cmid, $groupid = -1, $config = null) {
        global $USER, $CFG;

        $this->states = [];

        // Default state page.
        $defaultconfig = (object) [
                'anonymous_mode' => $this->get_anon_state_for_user($cmid),
                'participants' => (object) [
                        'full_view' => $this->get_full_view_state_for_user($cmid),
                        'states' => $this->get_states_for_user($cmid),
                        'score_ranges' => $this->get_score_ranges_state_for_user($cmid),
                ],
                'pills' => (object) $this->get_pills_state_for_user($cmid),
        ];

        if(isset($config->anonymous_mode)) {
            $this->set_anon_state_for_user($cmid, $config->anonymous_mode);
        }

        if(isset($config->participants->full_view)) {
            $this->set_full_view_state_for_user($cmid, $config->participants->full_view);
        }

        if(isset($config->participants->states)) {
            $this->set_states_for_user($cmid, $config->participants->states);
        }

        if(isset($config->participants->score_ranges)) {
            $this->set_score_ranges_state_for_user($cmid, $config->participants->score_ranges);
        }

        if(isset($config->pills)) {
            $this->set_pills_state_for_user($cmid, $config->pills);
        }

        $this->config = $config ? $config : $defaultconfig;

        $this->config->wwwroot = $CFG->wwwroot;

        $this->config->anonymous_mode = isset($this->config->anonymous_mode) && $this->config->anonymous_mode == "1" ? 1 : 0;
        $this->config->participants->show_score =
                isset($this->config->participants->show_score) && $this->config->participants->show_score == "1" ? 1 : 0;
        $this->config->participants->full_view =
                isset($this->config->participants->full_view) && $this->config->participants->full_view == "0" ? 0 : 1;
        $this->config->participants->attempts_range =
                isset($this->config->participants->attempts_range) && count($this->config->participants->attempts_range) > 0 ?
                        $this->config->participants->attempts_range : [];
        $this->config->participants->userattemptssortby =
                isset($this->config->participants->userattemptssortby) && $this->config->participants->userattemptssortby != "" ?
                        $this->config->participants->userattemptssortby : 'attempt_number|desc';

        // Options.
        $this->prepare_options();

        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);

        // Build groups.
        $coursecontext = context_course::instance($this->course->id);
        $roles = get_user_roles($coursecontext, $USER->id, false);
        $teacher = false;
        foreach ($roles as $role) {
            if ($role->shortname == 'teacher') {
                $teacher = true;
            }
        }

        $groups = [];

        if($groupid == -1) {
            $groupid = $this->get_groupid_for_user($cmid);
        }

        if ($teacher) {
            foreach (groups_get_all_groups($this->course->id, $USER->id) as $group) {
                $groups[] = [
                        'groupid' => $group->id,
                        'groupname' => $group->name,
                ];
            }

            // Default group id.
            if ($groupid == -1) {
                $groupid = isset($groups[0]) ?  $groups[0]['groupid'] : -2;
            }

            $this->groupid = $groupid;
        } else {
            $groups[] = [
                    'groupid' => '0',
                    'groupname' => get_string('allparticipants', 'quiz_advancedoverview'),
            ];

            foreach (groups_get_all_groups($this->course->id) as $group) {
                $groups[] = [
                        'groupid' => $group->id,
                        'groupname' => $group->name,
                ];
            }

            // Default group id.
            $this->groupid = ($groupid == -1) ? 0 : $groupid;
        }

        $this->set_groupid_for_user($cmid, $this->groupid);

        foreach ($groups as $key => $item) {
            $groups[$key]['selected'] = ($this->groupid == $item['groupid']) ? true : false;
        }

        $this->groups = $groups;

        $this->quizobj = \mod_quiz\quiz_settings::create($this->cm->instance);
        $this->quizobj->preload_questions();
        $this->quizobj->load_questions();

        list($this->participants, $this->participantsids) = $this->get_participants();

        $this->get_slots();

        $this->usersattempts = $this->get_attempts_per_users();

        list($this->enrolleduserscount,
                $this->usersfinished,
                $this->usersinprogress,
                $this->usersnotstarted,
                $this->usersfinishedlist,
                $this->usersinprogresslist,
                $this->usersnotstartedlist) = $this->quiz_submissions_stat();

        $this->set_timeout_settings($cmid);
    }

    public function get_config() {
        return $this->config;
    }

    private function set_timeout_settings($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_timeout_' . $cmid;
        return set_user_preference($name, time(), $USER->id);
    }

    private function check_timeout_wrong($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_timeout_' . $cmid;
        $timeout = get_user_preferences($name, 0, $USER->id);

        return $timeout + 10*60 <= time();
    }

    private function set_groupid_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_groupid_' . $cmid;
        return set_user_preference($name, (int) $state, $USER->id);
    }

    public function get_groupid_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_groupid_' . $cmid;
        if ($this->check_timeout_wrong($cmid)) {
            unset_user_preference($name, $USER->id);
        }

        return get_user_preferences($name, -1, $USER->id);
    }

    private function set_anon_state_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $cmid;
        return set_user_preference($name, (int) $state, $USER->id);
    }

    public function get_anon_state_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $cmid;
        return get_user_preferences($name, 0, $USER->id);
    }

    private function set_full_view_state_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_full_view_' . $cmid;
        return set_user_preference($name, (int) $state, $USER->id);
    }

    public function get_full_view_state_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_full_view_' . $cmid;
        if ($this->check_timeout_wrong($cmid)) {
            unset_user_preference($name, $USER->id);
        }

        return get_user_preferences($name, 1, $USER->id);
    }

    private function set_states_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_states_' . $cmid;
        return set_user_preference($name, json_encode($state), $USER->id);
    }

    public function get_states_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_states_' . $cmid;
        if ($this->check_timeout_wrong($cmid)) {
            unset_user_preference($name, $USER->id);
        }

        $value = get_user_preferences($name, json_encode(['all']), $USER->id);
        return json_decode($value, true);
    }

    private function set_score_ranges_state_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_score_ranges_' . $cmid;
        return set_user_preference($name, json_encode($state), $USER->id);
    }

    public function get_score_ranges_state_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_score_ranges_' . $cmid;
        if ($this->check_timeout_wrong($cmid)) {
            unset_user_preference($name, $USER->id);
        }

        $value = get_user_preferences($name, json_encode([]), $USER->id);
        return json_decode($value);
    }

    private function set_pills_state_for_user($cmid, $state) {
        global $USER;

        $name = 'quiz_advancedoverview_pills_' . $cmid;
        return set_user_preference($name, json_encode($state), $USER->id);
    }

    public function get_pills_state_for_user($cmid) {
        global $USER;

        $name = 'quiz_advancedoverview_pills_' . $cmid;

        if ($this->check_timeout_wrong($cmid)) {
            unset_user_preference($name, $USER->id);
        }

        $value = get_user_preferences($name, json_encode([]), $USER->id);
        return json_decode($value);
    }

    public function get_slots() {
        global $DB;

        $slots = [];
        foreach ($DB->get_records('quiz_slots', ['quizid' => $this->cm->instance]) as $item) {
            $slots[] = $item->slot;
        }
        $this->slots = $slots;
    }

    public function prepare_options() {

        $this->options = [
                'anonymous_mode' => [
                        '0' => 0,
                        '1' => 1,
                ],
                'questions' => [
                        'options' => [
                                'option1' => 1,
                                'option2' => 2,
                        ],
                ],
                'participants' => [
                        'states' =>
                                [
                                        [
                                                'name' => 'all',
                                                'label' => get_string('all', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'inprogress',
                                                'label' => get_string('inprogress', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'notstarted',
                                                'label' => get_string('notstarted', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'finished',
                                                'label' => get_string('submitted', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                        [
                                                'name' => 'late',
                                                'label' => get_string('late', 'quiz_advancedoverview'),
                                                'value' => 0,
                                        ],
                                ],
                        'score_ranges' => [
                                [
                                        'name' => 'range-0',
                                        'label' => '0-55',
                                ],
                                [
                                        'name' => 'range-1',
                                        'label' => '55-60',
                                ],
                                [
                                        'name' => 'range-2',
                                        'label' => '60-70',
                                ],
                                [
                                        'name' => 'range-3',
                                        'label' => '70-80',
                                ],
                                [
                                        'name' => 'range-4',
                                        'label' => '80-90',
                                ],
                                [
                                        'name' => 'range-5',
                                        'label' => '90-100',
                                ],
                        ],
                        'search' => '',
                        'show_score' => [
                                '0' => 0,
                                '1' => 1,
                        ],
                ],
        ];
    }

    public function get_grade_range($grade) {
        foreach ($this->options['participants']['score_ranges'] as $key => $range) {
            $label = $range['label'];
            $minmax = explode("-", $label);
            $min = intval($minmax[0]);
            $max = intval($minmax[1]);
            if ($grade >= $min && $grade <= $max) {
                return $range['name'];
            }
        }
        return null;
    }

    public function prepare_charts() {
        $this->chartaverage = $this->calculate_grades();
        $this->chartstate = $this->get_chart_state_data();
        $this->chartgrade = $this->get_chart_grade_data();
    }

    public function prepare_skills() {
        $this->skills = $this->get_skills();
    }

    public function get_skills() {
        global $CFG, $OUTPUT, $DB;

        $response = [
            'skills' => false,
            'competencybutton' => false,
            'competencyenabled' => false,
        ];

        // Competencies that were difficult for a class.
        $competencyreport = new quiz_competencyoverview_report();
        $quiz = $DB->get_record('quiz', array('id' => $this->cm->instance), '*', MUST_EXIST);
        $competencyenabled = false;
        $skills = $competencyreport->get_brief_competencies($quiz, $this->cm, $this->course);
        if (count($skills) != 0) {
            // Competencyoverview report button.
            $competencybuttonurl = new moodle_url($CFG->wwwroot . '/mod/quiz/report.php?id=' . $this->cm->id . '&mode=competencyoverview', array('display' => 'full'));
            $competencybuttonname = get_string('competencyoverview', 'quiz_advancedoverview');
            $competencybutton = '<a href="' . $competencybuttonurl . '" class="btn btn-outline-secondary">' . $competencybuttonname . '</a>';

            $response = [
                'skills' => $skills,
                'competencybutton' => $competencybutton,
                'competencyenabled' => true,
            ];

        }

        return $response;
    }

    public function prepare_questions() {
        $this->questions = $this->quizobj->get_questions();

        $numberview = 1;
        foreach ($this->questions as $key => $question) {
            if (in_array($question->qtype, $this->qdisabledviewtypes)) {
                continue;
            }

            $question->numberview = $numberview;
            $this->questions[$key] = $question;

            $numberview++;
        }
    }

    public function prepare_students() {
        global $DB;

        $this->quiz = $DB->get_record('quiz', array('id' => $this->cm->instance), '*', MUST_EXIST);
        $this->questionids = array_keys($this->questions);
    }

    public function get_students_table() {
        $tabledata = [];

        foreach ($this->participants as $student) {

            $userattempts = quiz_get_user_attempts($this->cm->instance, $student->id, 'all', false);

            if (!$userattempts) {
                $attempt = new stdClass;
                $attempt->userid = $student->id;
                $attempt->state = null;
                $attempt->attempt = null;
                $attempt->sumgrades = null;
                $attempt->timestart = null;
                $attempt->timefinish = null;
                $attempt->id = null;

                $userattempts = [$attempt];
            }

            $userattemptsinfo = $this->table_data_user($userattempts, $student->id);

            $userattemptsinfo =
                    count($userattemptsinfo) > 1 ? $this->sort_table_data_user($userattemptsinfo, true) : $userattemptsinfo;

            $tabledata = array_merge($tabledata, $userattemptsinfo);
        }

        if (isset($this->config->participants->helper_cells) && is_array($this->config->participants->helper_cells)
                && !empty($this->config->participants->helper_cells)) {

            $allusers = [];
            foreach ($this->config->participants->helper_cells as $cell) {
                switch ($cell->type) {
                    case 'flags':
                        list($unused, $users) = $this->get_question_flags($cell->questionid);
                        $allusers = array_merge($allusers, $users);
                        break;
                    case 'hints':
                        list($unused, $users) = $this->get_question_hints($cell->questionid);
                        $allusers = array_merge($allusers, $users);
                        break;
                    case 'chats':
                        list($unused, $users) = $this->get_question_chats($cell->questionid);
                        $allusers = array_merge($allusers, $users);
                        break;
                    case 'answered':
                        list($unused, $users) = $this->get_question_answered($cell->questionid);
                        $allusers = array_merge($allusers, $users);
                        break;
                    case 'wrong':
                        list($unused, $users) = $this->get_question_wrongs($cell->questionid);
                        $allusers = array_merge($allusers, $users);
                        break;
                }

            }

            $tabledatanew = [];
            foreach ($tabledata as $item) {
                if (in_array($item['userid'], $allusers)) {
                    $tabledatanew[] = $item;
                }
            }

            $tabledata = $tabledatanew;
        }

        return $tabledata;
    }

    public function get_students_table_summary() {

        $tabledata = [];
        $tabledata['fullname'] = get_string('summaryrow', 'quiz_advancedoverview');
        $tabledata['userid'] = 'summary';
        $tabledata['checkbox'] = false;
        $tabledata['usermenubtn'] = false;

        // Grade.
        $totalgrade = $countstudent = 0;
        foreach ($this->tablestudentsdata as $item) {
            if($item['grade'] && $item['grade'] >= 0) {
                $totalgrade += $item['grade'];
                $countstudent++;
            }
        }

        $tabledata['grade'] = $countstudent > 0 ? round($totalgrade/$countstudent, 2) : 0;

        // Per questions.
        if ($this->config->participants->full_view) {
            foreach ($this->questionids as $questionid) {
                $question = $this->questions[$questionid];

                if (in_array($question->qtype, $this->qdisabledviewtypes)) {
                    continue;
                }

                $totalgrade = $countstudent = 0;
                foreach ($this->tablestudentsdata as $item) {
                    if ($item['grade'] && $item['grade'] >= 0 && in_array($item[$question->id]['state'], ['correct', 'partiallycorrect', 'incorrect', 'notanswered'])) {
                        $totalgrade += $item[$question->id]['grade'];
                        $countstudent++;
                    }
                }

                if (isset($item[$question->id]['colname'])) {
                    $tabledata[$item[$question->id]['colname']] = $countstudent > 0 ? round($totalgrade / $countstudent, 2) : 0;
                }
            }
        }

        return $tabledata;
    }

    public function sort_table_data_user($data, $group = false) {
        $sortby = $this->config->participants->userattemptssortby;
        $sortbyparts = explode('|', $sortby);
        $sortfield = $sortbyparts[0];
        $sortdirection = $sortbyparts[1] ?? 'asc';

        // Sort the data.
        usort($data, function($a, $b) use ($sortfield, $sortdirection) {
            $aval = $a[$sortfield];
            $bval = $b[$sortfield];

            if ($sortdirection === 'desc') {
                return $bval <=> $aval;
            } else {
                return $aval <=> $bval;
            }
        });

        // Group the data if needed.
        if ($group) {
            $groupeddata = [];
            $maindata = true;
            $children = [];
            foreach ($data as $item) {
                if ($maindata) {
                    $groupeddata = $item;
                    $maindata = false;
                    $item['usermenubtn'] = $maindata;
                    $item['child'] = false;
                } else {
                    // Remove some info for nested rows.
                    $item['fullname'] = '';
                    $item['usermenubtn'] = $maindata;
                    $item['state'] = '';
                    $item['child'] = true;
                    $children[] = $item;
                }
            }

            $groupeddata['_children'] = $children;

            $data = $groupeddata;
        }

        return [$data];
    }

    public static function get_key_by_value($array, $property, $value) {
        foreach ($array as $key => $subarray) {
            foreach ($subarray as $subkey => $subvalue) {
                if ($subkey === $property && $subvalue === $value) {
                    return $key;
                }
            }
        }
        return null;
    }

    public static function get_item_with_max_value($array) {
        $maxvalueattempt = new stdClass;
        $maxvalueattempt->attempt = 0;
        foreach ($array as $item => $data) {
            if ($maxvalueattempt->attempt == 0 || $data->attempt > $maxvalueattempt->attempt) {
                $maxvalueattempt = $data;
            }
        }
        return $maxvalueattempt;
    }

    public function table_data_user($attempts, $userid) {
        $data = [];

        $firstname = $this->participants[$userid]->firstname;
        $lastname = $this->participants[$userid]->lastname;
        $fullname = htmlspecialchars($firstname . ' ' . $lastname, ENT_QUOTES, 'UTF-8');

        $resetpasswordlink = $this->get_resetpassword_link($userid);
        $userprofilelink = $this->get_userprofile_link($userid);
        $loginaslink = $this->get_loginas_link($userid);
        $completereportlink = $this->get_completereport_link($userid);
        $outlinereportlink = $this->get_outlinereport_link($userid);

        // Count only last attempts.
        $lastattempt = static::get_item_with_max_value($attempts);
        $state = $lastattempt->state ?: 'notstarted';
        foreach ($this->options['participants']['states'] as $key => $item) {
            if ($item['name'] == $state) {
                $this->options['participants']['states'][$key]['value']++;
            }
        }

        foreach ($attempts as $attempt) {
            $continue = false;
            foreach ($this->config->participants->attempts_range as $k => $arange) {
                switch ($arange) {
                    case '1':
                        if ($attempt->attempt == 1) {
                            $continue = true;
                        }
                        break;
                    case '2':
                        if ($attempt->attempt == 2) {
                            $continue = true;
                        }
                        break;
                    case '3+':
                        if ($attempt->attempt >= 3) {
                            $continue = true;
                        }
                        break;
                    case 'last':
                        if ($lastattempt->attempt === $attempt->attempt) {
                            $continue = true;
                        }
                        break;
                }
            }

            if (!$continue && count($this->config->participants->attempts_range) > 0) {
                continue;
            }

            if (isset($this->config->participants->states) && !in_array($state, $this->config->participants->states) &&
                    !in_array('all', $this->config->participants->states)) {
                continue;
            }

            if ($attempt->sumgrades) {
                if ($this->quiz->sumgrades == 0) {
                    $attemptgrade = '0';
                }else {
                    $attemptgrade = $attempt->sumgrades / $this->quiz->sumgrades * $this->quiz->grade;
                }
            } else {
                $attemptgrade = '0';
            }

            $range = $this->get_grade_range($attemptgrade);

            if (isset($this->config->participants->score_ranges) && !in_array($range, $this->config->participants->score_ranges) &&
                    count($this->config->participants->score_ranges) > 0) {
                continue;
            }

            if (isset($this->config->participants->search) && $this->config->participants->search != '' &&
                    strpos(strtolower($fullname), strtolower($this->config->participants->search)) === false) {
                continue;
            }

            $attemptnumber = $attempt->attempt ?: '—';
            $starttime = $attempt->timestart ? userdate($attempt->timestart, '%d/%m/%y | %H:%M') : '—';
            $endtime = $attempt->timefinish ? userdate($attempt->timefinish, '%d/%m/%y | %H:%M') : '—';

            // Duration.
            $delta = ($attempt->timestart && $attempt->timefinish) ? $attempt->timefinish - $attempt->timestart : -1;
            $durationtext =
                    ($attempt->timestart && $attempt->timefinish) ? format_time($attempt->timefinish - $attempt->timestart) : '—';

            $duration = $durationtext. '|' . $delta;

            $attempturl = $attempt->id ? new moodle_url('/mod/quiz/review.php', ['attempt' => $attempt->id]) : null;

            if ($attemptgrade != 0) {
                $attemptgradehtml = $attempturl ?
                        '<a target=`_blank` href=' . $attempturl->out(true) . '>' . round($attemptgrade, 2) . '</a>' :
                        round($attemptgrade, 2);
            } else {
                $attemptgradehtml = '—';
            }

            $rowdata = [
                    'checkbox' => '',
                    'attemptid' => $attempt->id,
                    'user_attempt_code' => $userid . '#' . $attemptnumber,
                    'userid' => $userid,
                    'fullname' => $attempturl ? '<a target=`_blank` href=' . $attempturl->out(true) . '>' . $fullname . '</a>' :
                            $fullname,
                    'firstname' =>  htmlspecialchars($this->participants[$userid]->firstname, ENT_QUOTES, 'UTF-8'),
                    'lastname' => htmlspecialchars($this->participants[$userid]->lastname, ENT_QUOTES, 'UTF-8'),
                    'usermenubtn' => '',
                    'team' => \local_teamwork\common::get_user_team($this->cm->id, $userid),
                    'state' => get_string($state, 'quiz_advancedoverview'),
                    'attempt_number' => $attemptnumber,
                    'grade' => $attemptgradehtml,
                    'starttime' => $starttime,
                    'endtime' => $endtime,
                    'duration' => $duration,
                    'resetpasswordlink' => $resetpasswordlink,
                    'userprofilelink' => $userprofilelink,
                    'loginaslink' => $loginaslink,
                    'completereportlink' => $completereportlink,
                    'outlinereportlink' => $outlinereportlink,
            ];

            // Total grade.
            $specialdata = [
                    'userid' => $userid,
                    'grade' => $attemptgrade
            ];

            if ($this->config->participants->full_view) {
                $att = $attempt->id ? \mod_quiz\quiz_attempt::create($attempt->id) : null;

                foreach ($this->questionids as $questionid) {
                    $question = $this->questions[$questionid];
                    $mark = $att ? $this->quiz_get_user_question_info($question, $att) : null;

                    if (in_array($question->qtype, $this->qdisabledviewtypes)) {
                        continue;
                    }

                    if ($this->quiz->sumgrades == 0) {
                        $questionmaxgrade = 0;
                    } else {
                        $questionmaxgrade = $question->maxmark / $this->quiz->sumgrades * $this->quiz->grade;
                    }

                    //$qnumberview = $question->slot;
                    $qindex = "Q " . $question->numberview . " / " . round($questionmaxgrade);

                    $rowdata[$qindex] = $mark ?: '—';

                    // Question grade.
                    $specialdata[$question->id]['gid'] = $question->id;
                    $specialdata[$question->id]['colname'] = $qindex;

                    $qgrade = $att ? $this->quiz_get_user_question_grade($question, $att) : 0;
                    $specialdata[$question->id]['grade'] = $qgrade;

                    // Question state.
                    $specialdata[$question->id]['state'] = $att ? $att->get_question_state_class($question->slot, true) : 'notyetanswered';
                }
            }
            $data[] = $rowdata;

            $this->tablestudentsdata[] = $specialdata;
        }

        return $data;
    }

    private function quiz_get_user_question_grade($question, $attempt) {

        if ($attempt->get_question_mark($question->slot)) {
            if ($this->quiz->sumgrades == 0) {
                $grade = 0;
            }else {
                $grade = $attempt->get_question_mark($question->slot) * $this->quiz->grade / $this->quiz->sumgrades;
            }
        } else {
            $grade = 0;
        }

        return $grade;
    }

    private function quiz_get_user_question_info($question, $attempt) {

        // Prepare questionlist requiresgrading.
        $this->add_to_openquestions($attempt, $question);

        $grade = $this->quiz_get_user_question_grade($question, $attempt);
        $fullquestionstate = $this->icon_score($attempt, $question->slot, round($grade, 2));

        return $fullquestionstate;
    }

    private function get_child_questions_for_grading($questionid, $qtypes = []) {
        global $DB;

        $params = [];
        $params['questionid'] = $questionid;
        list($sqlin, $inparams) = $DB->get_in_or_equal($qtypes, SQL_PARAMS_NAMED);
        $params += $inparams;

        $sql = "SELECT *
                FROM {question}
                WHERE parent = :questionid AND qtype $sqlin";

        $childquestions = $DB->get_records_sql($sql, $params);

        return $childquestions;
    }

    private function add_to_openquestions($attempt, $question) {

        $qtypes = [
            'essay',
            'opensheet',
            'mlnlpessay',
            'poodllrecording',
            'combined',
            'essayrubric',
            'multianswer',
        ];

        // Check for child in combined and multianswer.
        $childquestionsforgrading = [];
        switch ($question->qtype) {
            case 'combined':
            case 'multianswer':
                $childquestionsforgrading = $this->get_child_questions_for_grading($question->questionid, $qtypes);
                break;
            default:
                break;
        }

        if (in_array($question->qtype, $qtypes)) {
            $questionstateclass = $attempt->get_question_state_class($question->slot, true);
            if ($questionstateclass == 'requiresgrading' && $question->maxmark > 0) {
                if (count($childquestionsforgrading) != 0) {
                    foreach ($childquestionsforgrading as $key => $childquestion) {
                        $this->childopenquestionslist += $childquestionsforgrading;
                        $childquestionvid = $childquestion->id;
                        $childquestion->slot = $question->slot;
                        $childquestion->id = $question->id;
                        $childquestion->name = "$question->name — $childquestion->name:$childquestion->qtype";

                        if (!isset($this->openquestions[$childquestion->id])) {
                            $this->openquestions[$childquestionvid]['count'] = 1;
                        } else {
                            $this->openquestions[$childquestionvid]['count']++;
                        }

                        $this->openquestions[$childquestionvid]['qtype'] = $childquestion->qtype;
                    }
                } else {
                    if (!isset($this->openquestions[$question->id])) {
                        $this->openquestions[$question->id]['count'] = 1;
                    } else {
                        $this->openquestions[$question->id]['count']++;
                    }

                    $this->openquestions[$question->id]['qtype'] = $question->qtype;
                }
            }
        }
    }

    private function prepare_openquestions() {

        foreach ($this->openquestions as $key => $openq) {

            $question = $this->questions[$key] ?? $this->childopenquestionslist[$key];

            if (!in_array($openq['qtype'], ['opensheet'])) {
                $link = new moodle_url('/mod/quiz/report.php', [
                        'id' => $this->cm->id,
                        'mode' => 'assessmentdiscussion',
                        'qid' => $question->id,
                ]);
            } else {
                $link = new moodle_url('/mod/quiz/report.php', [
                        'id' => $this->cm->id,
                        'mode' => 'grading',
                        'slot' => $question->slot,
                        'qid' => $question->id,
                        'grade' => 'needsgrading',
                ]);
            }

            $item = new stdClass;
            $item->count_students = $openq['count'];
            $item->name = $question->name;
            $item->link = $link->out(false);
            $item->qnumber = isset($question->numberview) ? $question->numberview : '' ;

            $this->openquestionslist[] = $item;
        }
    }

    private function icon_score($attempt, $questionslot, $grade) {
        global $OUTPUT;

        $questionstateclass = $attempt->get_question_state_class($questionslot, true);
        $questionstate = $attempt->get_question_status($questionslot, true);

        // Link to attempt and render.
        if (in_array($questionstateclass, ['notyetanswered', 'answersaved', 'notchanged'])) {
            $questionstate = '—';
            $url = false;
        } else {
            $attemptid = $attempt->get_attempt()->id;

            if (!$attempt->is_finished()) {
                $link = $attemptid ?
                        new moodle_url('/mod/quiz/reviewquestion.php', ['attempt' => $attemptid, 'slot' => $questionslot]) : false;
            } else {
                $link = $attemptid ?
                        new moodle_url('/mod/quiz/comment.php',
                                ['attempt' => $attemptid, 'slot' => $questionslot]) : false;
            }

            $url = $link ? $link->out(false) : false;
        }

        $celltransform = in_array($questionstateclass, ['partiallycorrect', 'incorrect', 'correct']) ? true : false;

        $data = new stdClass;
        $data->questionstateclass = $questionstateclass;
        $data->celltransform = $celltransform;
        $data->questionstate = $questionstate;
        $data->grade = $grade;
        $data->link = $url;

        $html = $OUTPUT->render_from_template('quiz_advancedoverview/gradeicon', $data);

        // TODO Do not remove this comment.
        //if (!$attempt->is_finished()) {
        //    $html = "<span class='wrapped-value'>—</span>";
        //}

        return $html;
    }

    private function get_participants() {
        global $DB;

        $params = $conditions = [];

        $roles = [];
        foreach ($DB->get_records_sql("SELECT * FROM {role} WHERE shortname IN ('student', 'teachertraining')") as $role) {
            $roles[] = $role->id;
        }

        $sqlroles = implode(',', $roles);

        $sql = "
            SELECT u.* FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {user} u ON ue.userid = u.id
            JOIN {role_assignments} ra ON (u.id = ra.userid AND ra.roleid IN (".$sqlroles."))
            JOIN {context} c ON (ra.contextid = c.id AND c.instanceid = :instanceid)
            LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :cmid

        ";

        $params['instanceid'] = $this->course->id;
        $params['cmid'] = $this->cm->id;

        // Group.
        if ($this->groupid) {
            $sql .= " JOIN {groups_members} gm ON gm.userid = u.id ";
            $conditions[] = "gm.groupid = :groupid";
            $params['groupid'] = $this->groupid;
        }

        $conditions[] = "e.courseid = :courseid";
        $params['courseid'] = $this->course->id;

        $conditions[] = "ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND " .
                "(ue.timeend = 0 OR ue.timeend > :now2) AND (quiza.preview = 0 OR quiza.preview IS NULL) AND u.deleted = 0 AND u.id <> '1' ";

        $params['now1'] = round(time(), -2);
        $params['now2'] = $params['now1'];
        $params['active'] = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;

        $participants = $DB->get_records_sql($sql . ' WHERE ' . implode(' AND ', $conditions), $params);

        if (empty($participants)) {
            return [[], []];
        }

        $tmpids = [];
        foreach ($participants as $user) {
            $tmpids[] = $user->id;
        }

        // Get roles.
        $sql = "
            SELECT ra.*, r.name, r.shortname, COUNT(ra.userid) as count
              FROM {role_assignments} ra, {role} r, {context} c
             WHERE ra.userid IN (" . implode(',', $tmpids) . ") 
                   AND ra.roleid = r.id
                   AND ra.contextid = c.id
                   AND ra.contextid = ?  
             GROUP BY ra.userid          
        ";

        $userids = [];
        $context = context_course::instance($this->course->id);
        foreach ($DB->get_records_sql($sql, [$context->id]) as $item) {
            if ($item->count > 1) {
                unset($participants[$item->userid]);
            } else {
                $userids[] = $item->userid;
            }
        }

        // Anonymous mode.
        if ($this->config->anonymous_mode) {
            foreach ($participants as $key => $participant) {
                $participants[$key]->firstname = get_string('anon_user', 'quiz_advancedoverview') . ' ' . $this->anonymouscount;
                $participants[$key]->lastname = '';
                $this->anonymouscount++;
            }
        }

        return [$participants, $userids];
    }

    private function get_attempts_per_users() {
        global $DB;

        if (empty($this->participantsids)) {
            return [];
        }

        $sql = "
                SELECT
                DISTINCT CONCAT(u.id, '#', COALESCE(quiza.attempt, 0)) AS uniqueid,
                (CASE WHEN (quiza.state = 'finished' AND NOT EXISTS (
                                           SELECT 1 FROM {quiz_attempts} qa2
                                            WHERE qa2.quiz = quiza.quiz AND
                                                qa2.userid = quiza.userid AND
                                                 qa2.state = 'finished' AND (
                                COALESCE(qa2.sumgrades, 0) > COALESCE(quiza.sumgrades, 0) OR
                               (COALESCE(qa2.sumgrades, 0) = COALESCE(quiza.sumgrades, 0) AND qa2.attempt < quiza.attempt)
                                                ))) THEN 1 ELSE 0 END) AS gradedattempt,
                quiza.uniqueid AS usageid,
                quiza.id AS attempt,
                u.id AS userid,
                u.idnumber, u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.firstname,u.lastname,
                u.picture,
                u.imagealt,
                u.institution,
                u.department,
                u.email,
                quiza.state,
                quiza.sumgrades,
                quiza.timefinish,
                quiza.timestart,
                CASE WHEN quiza.timefinish = 0 THEN NULL
                     WHEN quiza.timefinish > quiza.timestart THEN quiza.timefinish - quiza.timestart
                     ELSE 0 END AS duration, COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded

                FROM  {user} u
                LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = ?
                JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = u.id
                JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = ?)

                WHERE u.id IN (" . implode(',', $this->participantsids) . ")

                ORDER BY quiza.id DESC
        ";

        $data = $DB->get_records_sql($sql, [$this->cm->instance, $this->course->id]);

        // Prepare questions per usageid.
        foreach ($data as $key => $item) {
            $item->questions_stat = $this->get_questions_per_usageid($item->usageid);
            $data[$key] = $item;
        }

        return $data;
    }

    private function get_questions_per_usageid($usageid = null) {
        global $DB;

        if (empty($this->slots) || $usageid == null) {
            return [];
        }

        $sql = "
            SELECT
                qas.id,
                qa.id AS questionattemptid,
                qa.questionusageid,
                qa.slot,
                qa.behaviour,
                qa.questionid,
                qa.variant,
                qa.maxmark,
                qa.minfraction,
                qa.maxfraction,
                qa.flagged,
                qa.questionsummary,
                qa.rightanswer,
                qa.responsesummary,
                qa.timemodified,
                qas.id AS attemptstepid,
                qas.sequencenumber,
                qas.state,
                qas.fraction,
                qas.timecreated,
                qas.userid

            FROM {question_attempts} qa
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
                    AND qas.sequencenumber = (
                            SELECT MAX(sequencenumber)
                            FROM {question_attempt_steps}
                            WHERE questionattemptid = qa.id
                        )

            WHERE qa.questionusageid = ? AND qa.slot IN (" . implode(',', $this->slots) . ")
        ";

        $data = $DB->get_records_sql($sql, [$usageid]);

        return $data;
    }

    private function get_chart_state_data() {

        // Encode data for js D3 pie chart.
        $data = [
                [
                        "label" => get_string('submitted', 'quiz_advancedoverview'),
                        "value" => $this->usersfinished,
                        "users" => $this->usersfinishedlist,
                ],
                [
                        "label" => get_string('notsubmitted', 'quiz_advancedoverview'),
                        "value" => $this->usersinprogress,
                        "users" => $this->usersinprogresslist,
                ],
                [
                        "label" => get_string('notstarted', 'quiz_advancedoverview'),
                        "value" => $this->usersnotstarted,
                        "users" => $this->usersnotstartedlist,
                ],
        ];
        return $data;
    }

    private function get_chart_grade_data() {
        global $DB;

        if ($this->quizobj->get_quiz()->grade == 100) {
            $scale = [
                    ['min' => 0, 'max' => 55],
                    ['min' => 55, 'max' => 60],
                    ['min' => 60, 'max' => 70],
                    ['min' => 70, 'max' => 80],
                    ['min' => 80, 'max' => 90],
                    ['min' => 90, 'max' => 100],
            ];
        } else {
            $scale = [
                    ['min' => 0, 'max' => 5],
                    ['min' => 5, 'max' => 6],
                    ['min' => 6, 'max' => 7],
                    ['min' => 7, 'max' => 8],
                    ['min' => 8, 'max' => 9],
                    ['min' => 9, 'max' => 10],
            ];
        }

        $labels = [];

        // Remove not submitted.
        //$labels[] = get_string('notsubmittedhist', 'quiz_advancedoverview');

        foreach ($scale as $item) {
            // If Hebrew.
            if (right_to_left()) {
                $labels[] = $item['min'] . '-' . $item['max'];
            } else {
                $labels[] = $item['min'] . '-' . $item['max'];
            }
        }

        if ($DB->record_exists('quiz_grades', ['quiz' => $this->quizobj->get_quiz()->id])) {

            list($bandsdata, $resultlist) = $this->quiz_advancedoverview_grade_bands($scale, $this->quizobj->get_quiz()->id,
                    $this->groupid, new \core\dml\sql_join(), $this->course->id);

            // Remove not submitted.
            //$notsubimtted = [$this->usersinprogress]; // Add users in progress / not submitted yet.
            //$notsubimttedlist = [$this->usersinprogresslist]; // Add users in progress / not submitted yet.
            //$chartdata = array_merge($notsubimtted, $bandsdata); // New merged array with data for chart.
            //$chartdatausers = array_merge($notsubimttedlist, $resultlist); // New merged array with data for chart.

            $chartdata = $bandsdata;
            $chartdatausers = $resultlist;

            $chart = self::get_chart($labels, $chartdata);
        } else {
            $chartdata = [0, 0, 0, 0, 0, 0, 0]; // Hardcoded value in case no one passed the quiz.
            $chartdatausers = [0, 0, 0, 0, 0, 0, 0];
        }

        // Encode data for js D3 pie chart.
        $values = [];
        if (isset($labels[0])) {
            $values[] = ["label" => $labels[0], "value" => $chartdata[0], "users" => $chartdatausers[0]];
        }

        if (isset($labels[1])) {
            $values[] = ["label" => $labels[1], "value" => $chartdata[1], "users" => $chartdatausers[1]];
        }

        if (isset($labels[2])) {
            $values[] = ["label" => $labels[2], "value" => $chartdata[2], "users" => $chartdatausers[2]];
        }

        if (isset($labels[3])) {
            $values[] = ["label" => $labels[3], "value" => $chartdata[3], "users" => $chartdatausers[3]];
        }

        if (isset($labels[4])) {
            $values[] = ["label" => $labels[4], "value" => $chartdata[4], "users" => $chartdatausers[4]];
        }

        if (isset($labels[5])) {
            $values[] = ["label" => $labels[5], "value" => $chartdata[5], "users" => $chartdatausers[5]];
        }

        if (isset($labels[6])) {
            $values[] = ["label" => $labels[6], "value" => $chartdata[6], "users" => $chartdatausers[6]];
        }

        $data = array(array("key" => "keyname", "values" => $values));

        return $data;
    }

    protected static function get_chart($labels, $data) {
        $chart = new \core\chart_bar();
        $chart->set_labels($labels);
        $chart->get_xaxis(0, true)->set_label(get_string('grades'));

        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_label(get_string('participants'));
        $yaxis->set_stepsize(max(1, round(max($data) / 10)));

        $series = new \core\chart_series(get_string('participants'), $data);
        $chart->add_series($series);
        return $chart;
    }

    protected function quiz_submissions_stat() {
        global $DB;

        $student = $DB->get_record('role', ['shortname' => 'student']);

        $params = [
                'quizid' => $this->quizobj->get_quiz()->id,
                'courseid' => $this->course->id,
                'roleid' => $student->id,
        ];

        // Count only one finished attempt for one user.
        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT
                    userid,
                    max(id) AS id,
                    state,
                    quiz,
                    max(attempt) AS attempt
                FROM
                    {quiz_attempts}
                WHERE
                    userid IN (" . implode(',', $this->participantsids) . ")
                    AND quiz = :quizid
                    AND id IS NOT NULL
                    AND state = 'finished'
                GROUP BY
                    userid
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersfinishedlist = static::users_list_w_link_to_attempt($data);
        $usersfinished = count($userskey);

        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT
                    userid,
                    max(id) AS id,
                    state,
                    quiz,
                    max(attempt) AS attempt
                FROM
                    {quiz_attempts}
                WHERE
                    userid IN (" . implode(',', $this->participantsids) . ")
                    AND quiz = :quizid
                    AND id IS NOT NULL
                    AND state = 'inprogress'
                GROUP BY
                    userid
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersinprogresslist = static::users_list_w_link_to_attempt($data);
        $usersinprogress = count($userskey);

        if (!empty($this->participantsids)) {
            $data = $DB->get_records_sql("
                SELECT u.id AS userid
                FROM {user} u            
                LEFT JOIN {quiz_attempts} quiza ON quiza.userid = u.id AND quiza.quiz = :quizid
                WHERE u.id IN (" . implode(',', $this->participantsids) . ") AND quiza.id IS NULL       
                ",
                    $params);
        } else {
            $data = [];
        }

        $userskey = array_unique(array_keys($data));
        $usersnotstartedlist = static::users_list_wo_link($userskey);
        $usersnotstarted = count($userskey);

        $enrolleduserscount = $usersfinished + $usersinprogress + $usersnotstarted;

        return [$enrolleduserscount, $usersfinished, $usersinprogress, $usersnotstarted, $usersfinishedlist, $usersinprogresslist,
                $usersnotstartedlist];
    }

    public function calculate_grades() {
        global $DB;

        if (!empty($this->participantsids)) {
            $params = [];
            $select =
                    "SELECT AVG(qa.sumgrades) AS averagegrade, MAX(qa.sumgrades) AS maxgrade, MIN(qa.sumgrades) AS mingrade, COUNT(qa.sumgrades) AS numgrades";
            $from = "FROM {quiz_attempts} qa JOIN {quiz} q ON qa.quiz = q.id";
            $where = "WHERE q.id = ? AND qa.userid IN (" . implode(',', $this->participantsids) .
                    ") AND qa.preview = 0 AND qa.state = 'finished' AND qa.sumgrades >= 0";
            $params[] = $this->quizobj->get_quiz()->id;

            if ($this->course) {
                $from .= " JOIN {course} c ON q.course = c.id";
                $where .= " AND c.id = ?";
                $params[] = $this->course->id;
            }

            $record = $DB->get_record_sql("$select $from $where", $params);
        } else {
            $record = new StdClass();
            $record->numgrades = 0;
            $record->averagegrade = 0;
            $record->maxgrade = 0;
            $record->mingrade = 0;
        }

        if ($record->numgrades == 0) {
            $record->averagegrade = '-';
            $record->maxgrade = '-';
            $record->mingrade = '-';
        } else {
            $record->averagegrade = round(quiz_rescale_grade($record->averagegrade, $this->quizobj->get_quiz(), false), 1);
            $record->maxgrade = round(quiz_rescale_grade($record->maxgrade, $this->quizobj->get_quiz(), false));
            $record->mingrade = round(quiz_rescale_grade($record->mingrade, $this->quizobj->get_quiz(), false));
        }

        $record->str_max_grade = 'max_grade';
        $record->title_max_grade = get_string('max_grade', 'quiz_advancedoverview');

        $record->str_min_grade = 'min_grade';
        $record->title_min_grade = get_string('min_grade', 'quiz_advancedoverview');

        $record->str_attempts_grade = 'attempts';
        $record->title_attempts_grade = get_string('attempts', 'quiz_advancedoverview');

        return $record;
    }

    public function users_list_w_link($userids) {
        $list = [];

        foreach ($userids as $item) {
            $user = new stdClass;
            $user->firstname = htmlspecialchars($this->participants[$item]->firstname, ENT_QUOTES, 'UTF-8');
            $user->lastname = htmlspecialchars($this->participants[$item]->lastname, ENT_QUOTES, 'UTF-8');
            $user->disabled = false;
            $user->link = (new moodle_url('/user/profile.php', ['id' => $item]))->out();
            $list[] = $user;
        }

        return $list;
    }

    public function users_list_wo_link($userids) {
        $list = [];

        foreach ($userids as $item) {
            $user = new stdClass;
            $user->firstname = htmlspecialchars($this->participants[$item]->firstname, ENT_QUOTES, 'UTF-8');
            $user->lastname = htmlspecialchars($this->participants[$item]->lastname, ENT_QUOTES, 'UTF-8');
            $user->disabled = true;
            $user->link = '';
            $list[] = $user;
        }

        return $list;
    }

    public function users_list_w_link_to_attempt($usersattempts) {
        $list = [];

        foreach ($usersattempts as $uk => $ua) {
            $user = new stdClass;
            $user->firstname = htmlspecialchars($this->participants[$uk]->firstname, ENT_QUOTES, 'UTF-8');
            $user->lastname = htmlspecialchars($this->participants[$uk]->lastname, ENT_QUOTES, 'UTF-8');
            $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $ua->id]))->out();
            $list[] = $user;
        }

        return $list;
    }

    public function get_render_data() {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        $data = [
                'title' => format_string($this->cm->name, true, ['context' => $context]),
        ];

        $data['groups'] = $this->groups;
        $data['groupsselectenable'] = count($this->groups) > 1 ? true : false;

        // Buttons.
        $data['href_edit_question'] = new moodle_url('/mod/quiz/edit.php', ['cmid' => $this->cm->id]);
        $data['href_preview_question'] =
                new moodle_url('/mod/quiz/startattempt.php', ['cmid' => $this->cm->id, 'sesskey' => sesskey()]);

        // Build users and questions table.

        // Students table.
        $data = array_merge($data, $this->get_render_students_data());

        // Table according to questions.
        $tablequestion = [];
        $questionTexts = [];
        $diagnosticQuestionsTable = [];

        foreach ($this->questions as $q) {

            if (in_array($q->qtype, $this->qdisabledviewtypes)) {
                continue;
            }

            list($questionanswerder, $unused) = $this->get_question_answered($q->questionid);
            list($questionwrongs, $unused) = $this->get_question_wrongs($q->questionid);
            list($questionflags, $unused) = $this->get_question_flags($q->questionid);
            list($questionhints, $unused) = $this->get_question_hints($q->questionid);
            list($questionchats, $unused) = $this->get_question_chats($q->questionid);

            $questiontitle = get_string('question');
            $qname = htmlspecialchars($q->name, ENT_QUOTES, 'UTF-8');
            $url = $this->get_question_link($q, $this->cm->id);
            $questiontext = $DB->get_field('question', 'questiontext', array('id' => $q->questionid));
            preg_match_all('/<[^>]*>([^<]*)<\/[^>]*>/', $questiontext, $matches);
            $parsedStr = implode(" ", $matches[1]);
            array_push($questionTexts, $parsedStr);

            $questionlink = "<a class=d-flex target=_blank href=" . $url . "><span class=qname>" . $questiontitle . " " . $q->numberview . "</span><span class=description>" . $qname . "</span></a>";
            $tablequestion[] = [
                '#' => $q->numberview,
                $questiontitle => $questionlink,
                get_string('answered', 'quiz_advancedoverview') => $this->prepare_button_question_table($questionanswerder, $q, 'answered'),
                get_string('wrong', 'quiz_advancedoverview') => $this->prepare_button_question_table($questionwrongs, $q, 'wrong'),
                get_string('raiseflag', 'quiz_advancedoverview') => $this->prepare_button_question_table($questionflags, $q, 'flags'),
                get_string('usehint', 'quiz_advancedoverview') => $this->prepare_button_question_table($questionhints, $q, 'hints'),
                get_string('usechat', 'quiz_advancedoverview') => $this->prepare_button_question_table($questionchats, $q, 'chats')
            ];

            $path = '/mod/quiz/report.php';
            $params = array('id' => $this->cm->id, 'mode' => 'diagnosticstats', 'questionid' => $q->questionid);
            $statsurl = $this->generate_link($path, $params);

            $diagnosticquestionlink = "<a class='d-flex' target='_blank' href='" . $statsurl . "'><span class='qname'>" . $questiontitle . " " . $q->numberview . "</span><span class='description'>" . $qname . "</span></a>";
            // Adding diagnostic questions to the array
            if ($q->qtype == 'diagnostic' || $q->qtype == 'diagnosticadv') {
                $diagnosticQuestionsTable[] = [
                    '#' => $q->numberview,
                    $questiontitle => $diagnosticquestionlink,
                    get_string('answered', 'quiz_advancedoverview') => $questionanswerder,
                    get_string('wrong', 'quiz_advancedoverview') => $questionwrongs,
                    get_string('raiseflag', 'quiz_advancedoverview') => $questionflags,
                    get_string('usehint', 'quiz_advancedoverview') => $questionhints,
                    get_string('usechat', 'quiz_advancedoverview') => $questionchats,
                ];
            }
        }

        $data['count_according_questions'] = count($tablequestion);
        $data['questionTexts'] = json_encode(['texts' => $questionTexts]);
        $data['enable_table_according_questions'] = count($tablequestion) > 0 ? true : false;
        $data['data_table_according_questions'] = json_encode($tablequestion, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $data['count_diagnostic_questions'] = count($diagnosticQuestionsTable);
        $data['enable_table_according_diagnostic_questions'] = count($diagnosticQuestionsTable) > 0 ? true : false;
        $data['data_table_according_diagnostic_questions'] = json_encode($diagnosticQuestionsTable, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $data['charts']['average'] = $this->chartaverage;
        $data['charts']['state'] = $this->chartstate;
        $data['charts']['grade'] = $this->chartgrade;

        $data['charts'] = json_encode($data['charts'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $data['charts_average_averagegrade'] = $this->chartaverage->averagegrade;
        $data['charts_average_maxgrade'] = $this->chartaverage->maxgrade;
        $data['charts_average_mingrade'] = $this->chartaverage->mingrade;

        $this->prepare_openquestions();

        $data['open_questions'] = $this->openquestionslist;
        $data['open_questions_count'] = count($this->openquestionslist);
        $data['enable_open_questions'] = count($this->openquestionslist) > 0 ? true : false;

        $data['cmid'] = $this->cm->id;
        $data['courseid'] = $this->course->id;
        $data['quizid'] = $this->quiz->id;
        $data['groupid'] = $this->groupid === null ? 0 : $this->groupid;

        $data['data_table_according_students_options'] = $this->options;

        $data['anonymous_mode'] = $this->config->anonymous_mode;
        $data['config'] = $this->config;

        $data['dir_rtl'] = right_to_left() == 'rtl' ? true : false;

        $response = [
                'skills' => false,
                'competencybutton' => false,
                'competencyenabled' => false,
        ];

        $data['skills'] = $this->skills ? $this->skills['skills'] : false;
        $data['competencybutton'] = $this->skills ? $this->skills['competencybutton'] : false;
        $data['competencyenabled'] = $this->skills ? $this->skills['competencyenabled'] : false;

        return $data;
    }

    public function get_render_students_data() {

        $tablestudent = $this->get_students_table();
        $tablestudentsummary = $this->get_students_table_summary();
        $data['count_according_students'] = count($this->participants);
        $data['data_table_according_students'] = json_encode($tablestudent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $data['data_table_students_summary'] = json_encode($tablestudentsummary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $allkey = static::get_key_by_value($this->options['participants']['states'], 'name', 'all');
        $this->options['participants']['states'][$allkey]['value'] = count($this->participants);

        return $data;
    }

    private function generate_link($path, $params) {

        $url = new moodle_url($path, $params);

        return $url->out(false);
    }

    public function get_resetpassword_link($userid) {
        global $USER;

        if (($this->is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !$this->is_user_have_course_update_privileges($this->course->id, $userid))
            || is_siteadmin()) {
            $path = '/report/roster/resetpassword.php';
            $params = ['userid' => $userid, 'courseid' => $this->course->id, 'sesskey' => sesskey(), 'layout' => 'embedded'];

            return $this->generate_link($path, $params);
        }

        return '';
    }

    /**
     * Get the user profile link for a given user ID based on privileges.
     *
     * @param int $userid The user ID.
     * @return string The user profile link HTML or an empty string.
     */
    public function get_userprofile_link($userid) {
        global $USER;

        if (($this->is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !$this->is_user_have_course_update_privileges($this->course->id, $userid))
            || is_siteadmin()) {

            $path   = '/user/view.php';
            $params = ['id' => $userid, 'courseid' => $this->course->id];

            return $this->generate_link($path, $params);
        }

        return '';
    }

    public function get_loginas_link($userid) {
        global $USER;

        $coursecontext = context_course::instance($this->course->id);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() &&
                has_capability('moodle/user:loginas', $coursecontext) &&
                !$this->is_user_have_course_update_privileges($this->course->id, $userid)
                || is_siteadmin()) {

            $path = '/course/loginas.php';
            $params = ['user' => $userid, 'sesskey' => sesskey()];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_completereport_link($userid) {
        global $USER;

        if (($this->is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !$this->is_user_have_course_update_privileges($this->course->id, $userid))
            || is_siteadmin()) {

            $path = '/report/outline/user.php';
            $params = ['id' => $userid, 'course' => $this->course->id, 'mode' => 'complete'];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_outlinereport_link($userid) {
        global $USER;

        if (($this->is_user_have_course_update_privileges($this->course->id, $USER->id) &&
            !$this->is_user_have_course_update_privileges($this->course->id, $userid))
            || is_siteadmin()) {

            $path = '/report/outline/user.php';
            $params = ['id' => $userid, 'course' => $this->course->id, 'mode' => 'outline'];
        } else {
            return '';
        }

        return $this->generate_link($path, $params);
    }

    public function get_question_chats($questionid) {
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'advancedoverview', 'data');

        $count = 0;
        if (($result = $cache->get('chats_count')) !== false) {
            if (isset($result[$questionid])) {
                $count = $result[$questionid];
            }
        }

        $users = [];
        if (($result = $cache->get('chats_users')) !== false) {
            if (isset($result[$questionid]) && is_array($result[$questionid])) {
                $users = $result[$questionid];
            }
        }

        return [$count, array_unique($users)];
    }

    public function get_question_hints($questionid) {
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'advancedoverview', 'data');

        $count = 0;
        if (($result = $cache->get('hints_count')) !== false) {
            if (isset($result[$questionid])) {
                $count = $result[$questionid];
            }
        }

        $users = [];
        if (($result = $cache->get('hints_users')) !== false) {
            if (isset($result[$questionid]) && is_array($result[$questionid])) {
                $users = $result[$questionid];
            }
        }

        return [$count, array_unique($users)];
    }

    public function get_question_flags($questionid) {
        global $DB;

        // Get question attempts.
        $sql = "SELECT CONCAT(qa.id, qas.userid, RAND()) as uniqueid, qa.*, qas.userid
            FROM {question_attempts} qa
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
            WHERE qa.questionid = :questionid";

        $params = [];
        $params['questionid'] = $questionid;

        $users = [];
        foreach ($DB->get_records_sql($sql, $params) as $qa) {

            // Get flags for all attempts.
            if ($qa->flagged) {
                $users[] = $qa->userid;
            }
        }

        $users = array_unique($users);

        return [count($users), $users];
    }

    public function get_question_wrongs($questionid) {

        $users = [];
        foreach ($this->tablestudentsdata as $item) {
            if (isset($item[$questionid]) && isset($item[$questionid]['state'])) {
                if (in_array($item[$questionid]['state'], ['incorrect'])) {
                    $users[] = $item['userid'];
                }
            }
        }

        $users = array_unique($users);

        return [count($users), $users];
    }

    public function get_question_answered($questionid) {

        $users = [];
        foreach ($this->tablestudentsdata as $item) {
            if (isset($item[$questionid]) && isset($item[$questionid]['state'])) {
                if (!in_array($item[$questionid]['state'], ['notanswered', 'notyetanswered', 'answersaved', 'notchanged'])) {
                    $users[] = $item['userid'];
                }
            }
        }

        $users = array_unique($users);

        return [count($users), $users];
    }

    public function quiz_advancedoverview_grade_bands($scale, $quizid, $currentgroup,
            \core\dml\sql_join $usersjoins = null, $courseid) {
        global $DB;

        if ($usersjoins && !empty($usersjoins->joins)) {
            $userjoin = " JOIN {user} u ON (u.id = qg.userid)
                        {$usersjoins->joins} ";
            $usertest = $usersjoins->wheres;
            $params = $usersjoins->params;
        } else {
            $userjoin = ' JOIN {user} u ON (u.id = qg.userid) ';
            $usertest = ' 1=1 ';
            $params = array();
        }

        if ($currentgroup > 0) {
            $sql = "
                SELECT UUID(), band, subquery.userid, subquery.uqaid
                FROM (
                    SELECT qg.grade AS band, u.id AS userid, uqa.id as uqaid
                    FROM {quiz_grades} AS qg
                    LEFT JOIN {groups_members} AS gm ON (qg.userid = gm.userid)
                    $userjoin
                    JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                    JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
                    LEFT JOIN (
                        SELECT
                            qa.userid,
                            max(qa.id) AS id,
                            qa.state,
                            qa.quiz,
                            max(qa.attempt) AS attempt
                        FROM
                            {quiz_attempts} qa
                        WHERE
                            qa.quiz = :quizid2
                            AND qa.id IS NOT NULL
                            AND qa.state = 'finished'
                        GROUP BY
                            qa.userid
                    ) uqa ON uqa.userid = u.id
                    WHERE $usertest AND qg.quiz = :quizid AND gm.groupid = :groupid AND u.suspended = 0 AND ue_d.status = 0
                        AND (
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
                ) subquery
                ORDER BY band ";
        } else {
            $sql = "
                SELECT UUID(), band, subquery.userid, subquery.uqaid
                FROM (
                    SELECT qg.grade AS band, u.id AS userid, uqa.id as uqaid
                    FROM {quiz_grades} qg
                    $userjoin
                    JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
                    JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = :courseid)
                    LEFT JOIN (
                        SELECT
                            qa.userid,
                            max(qa.id) AS id,
                            qa.state,
                            qa.quiz,
                            max(qa.attempt) AS attempt
                        FROM
                            {quiz_attempts} qa
                        WHERE
                            qa.quiz = :quizid2
                            AND qa.id IS NOT NULL
                            AND qa.state = 'finished'
                        GROUP BY
                            qa.userid
                    ) uqa ON uqa.userid = u.id
                    WHERE $usertest AND qg.quiz = :quizid AND u.suspended = 0 AND ue_d.status = 0
                        AND (
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
                ) subquery
                ORDER BY band ";
        }

        $params['quizid'] = $quizid;
        $params['quizid2'] = $quizid;
        $params['groupid'] = $currentgroup;
        $params['courseid'] = $courseid;

        $data = $DB->get_records_sql_menu($sql, $params);
        $datalist = $DB->get_records_sql($sql, $params);

        $result = [];
        $resultlist = [];
        foreach ($scale as $item) {
            $count = 0;
            $list = [];
            foreach ($datalist as $key => $grade) {

                $firstname = isset($this->participants[$datalist[$key]->userid]) ?
                        $this->participants[$datalist[$key]->userid]->firstname : '';

                $lastname = isset($this->participants[$datalist[$key]->userid]) ?
                        $this->participants[$datalist[$key]->userid]->lastname : '';

                if ($grade->band >= $item['min'] && $grade->band < $item['max']) {
                    $count++;
                    $user = new stdClass;
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $grade->uqaid]))->out();
                    $list[] = $user;
                }
                if (($item['max'] == 100 || $item['max'] == 10) && $grade->band == $item['max']) {
                    $count++;
                    $user = new stdClass;
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->link = (new moodle_url('/mod/quiz/review.php', ['attempt' => $grade->uqaid]))->out();
                    $list[] = $user;
                }
            }

            $resultlist[] = $list;
            $result[] = $count;
        }

        return [$result, $resultlist];
    }

    private function prepare_button_question_table($value, $q, $type) {

        $word = '';
        switch ($type) {
            case 'flags':
                $word = get_string('raiseflag', 'quiz_advancedoverview');
                break;
            case 'hints':
                $word = get_string('usehint', 'quiz_advancedoverview');
                break;
            case 'chats':
                $word = get_string('usechat', 'quiz_advancedoverview');
                break;
            case 'answered':
                $word = get_string('answered', 'quiz_advancedoverview');
                break;
            case 'wrong':
                $word = get_string('wrong', 'quiz_advancedoverview');
                break;
        }

        $title = get_string('question') ." ". $q->numberview ." - ".$word;

        $html = "<a href='javascript:void(0);' class='cellqlink helper-cell' data-action='helper-cells' data-type='".$type."' data-questionid='".$q->questionid."' data-title='".$title."'>";
        $html .= "<div class='numerical_value'>".$value."</div>";
        $html .= "</a>";

        return $html;
    }

    /**
     * Get question link.
     *
     * @param object $question Qusetion
     * @param int $cmid Course module
     * @return string
     */
    private function get_question_link($question, $cmid) {
        switch ($question->qtype) {
            case 'random':
                $params = [];
                $params['id'] = $cmid;
                $params['mode'] = 'randomsummary';
                $params['attempts'] = 'enrolled_with';
                $params['onlygraded'] = '0';
                $params['tsort'] = 'qsgrade' . $question->id;
                $editurl = new moodle_url('/mod/quiz/report.php', $params);
                break;

            default:
                $params = [];
                $params['id'] = $question->id;
                $params['cmid'] = $cmid;
                $editurl = new moodle_url('/question/bank/previewquestion/preview.php', $params);
        }

        return $editurl;
    }

    /**
     * If user have course update privileges.
     *
     * @param int $courseid Course id
     * @param int $userid User id
     * @return boolean
     */
    private function is_user_have_course_update_privileges($courseid, $userid = null) {
        global $USER;

        if ($userid !== null) {
            $user = \core_user::get_user($userid);
        } else {
            $user = $USER;
        }

        return has_capability('moodle/course:update', context_course::instance($courseid), $user);
    }
}
