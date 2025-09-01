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
 * @package     quiz_assessmentdiscussion
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_assessmentdiscussion;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/local/teamwork/locallib.php');

class quizinfo {

    public $qtypes;
    public $course;
    public $cm;
    public $groupid;
    public $groups;
    public $groupmodeenable;
    public $quizobj;
    public $quiz;

    public function __construct($cmid, $groupid = 0) {
        global $DB;

        $filterqtypes = get_config('quiz_assessmentdiscussion', 'filter_qtypes');

        $qtypes = [];
        foreach (json_decode($filterqtypes, true) as $qname => $value) {
            if ($value == 1) {
                $qtypes[] = $qname;
            }
        }

        $this->qtypes = $qtypes;

        list($this->course, $this->cm) = get_course_and_cm_from_cmid($cmid);

        $this->quizobj = \mod_quiz\quiz_settings::create($this->cm->instance);
        $this->quizobj->preload_questions();
        $this->quizobj->load_questions();
        $this->quiz = $DB->get_record('quiz', array('id' => $this->cm->instance), '*', MUST_EXIST);

        // Groups.
        $this->prepare_groups($groupid);
    }

    public static function get_disabled_qtypes() {
        return ['description'];
    }

    public function if_teamwork_enable() {
        global $DB;

        $flag1 = false;

        $teamwork = $DB->get_record('local_teamwork', ['moduleid' => $this->cm->id, 'type' => 'quiz']);
        if (!empty($teamwork) && $teamwork->active == 1) {
            $flag1 = true;
        }

        $lqsoptions = \local_quiz_summary_option\funcs::get_quiz_config($this->cm->id);
        $flag2 = ($lqsoptions->summary_teamwork == 1) ? false : true;

        return $flag1 && $flag2;
    }

    public function get_questions_for_report() {
        global $DB, $USER;

        $assessmentcache = new \quiz_assessmentdiscussion\assessmentcache($this->cm->id);

        if ($assessmentcache->questions()->check_cache($this->cm->id)) {
            $questions = $assessmentcache->questions()->get($this->cm->id);
        } else {
            $questions = $this->get_questions();
            $assessmentcache->questions()->set($this->cm->id, $questions);
        }

        foreach ($questions as $key => $question) {

            // Discussion enable.
            $tmp = $DB->get_records('assessmentdiscussion_discus', [
                    'userid' => $USER->id,
                    'cmid' => $this->cm->id,
                    'groupid' => $this->groupid,
                    'qid' => $question->id
            ]);

            $questions[$key]->discussion_enable = count($tmp) ? true : false;
        }

        return $questions;
    }

    private function get_questions() {
        global $OUTPUT;

        $questions = [];
        $numberview = 0;

        foreach ($this->quizobj->get_questions() as $question) {
            if (in_array($question->qtype, self::get_disabled_qtypes())) {
                continue;
            }

            $numberview++;

            if (!in_array($question->qtype, $this->qtypes)) {
                continue;
            }

            $question->numberview = $numberview;

            $question->qtypename = get_string('pluginname', 'qtype_' . $question->qtype);

            $question->qicon = $OUTPUT->pix_icon('icon', $question->qtypename, 'qtype_'.$question->qtype);

            $question->qmark = round($question->maxmark, 2);

            // Default discussion enable.
            $question->discussion_enable = false;

            // Preview description.
            $options = new \stdClass;
            $options->noclean = true;
            $options->para = false;

            $questiontext = question_rewrite_question_preview_urls($question->questiontext, $question->id,
                    $question->contextid, 'question', 'questiontext', $question->id,
                    $question->contextid, 'core_question');

            $questiontext = format_text($questiontext, $question->questiontextformat, $options);

            $question->questiondescriptionlist = strip_tags($questiontext);
            $question->questiondescription = $questiontext;

            // Link report grading.
            $linkreportgrading = new \moodle_url('/mod/quiz/report.php', [
                    'id' => $this->cm->id,
                    'mode' => 'grading',
                    'slot' => $question->slot,
                    'qid' => $question->id,
                    'grade' => 'needsgrading',
            ]);

            $question->linkreportgrading = $linkreportgrading->out();

            // Preview question.
            $preview = new \quiz_assessmentdiscussion\preview($this->cm->id, $question->id);
            $pdata = $preview->preview_question_data();

            $question->preview_question = $pdata->preview_question;
            $question->preview_question_link = $pdata->preview_question_link;
            $question->iframeenable = $pdata->iframeenable;

            // Need run function prepare_user_and_attempts_for_question.
            $question->users = [];
            $question->waitforgrade = 0;
            $question->attemptsfailed = 0;

            $questions[] = $question;
        }

        return $questions;
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

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $params['roleid'] = $studentrole->id;

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
            return [];
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

        $context = \context_course::instance($this->course->id);
        foreach ($DB->get_records_sql($sql, [$context->id]) as $item) {
            if ($item->count > 1) {
                unset($participants[$item->userid]);
            }
        }

        // Add default teamworkusers array and profile link.
        foreach ($participants as $key => $participant) {
            $participants[$key]->teamworkusers = [];

            $linkprofile = new \moodle_url('/user/profile.php', ['id' => $participant->id]);
            $participants[$key]->linkprofile = $linkprofile->out();
        }

        // Teamwork mode.
        if ($this->if_teamwork_enable()) {
            $participantsnew = [];
            foreach (get_cards($this->cm->id, 'quiz', $this->course->id, $this->groupid) as $card) {

                $teamworkusers = [];
                foreach ($card['users'] as $user) {
                    if (isset($participants[$user->userid])) {
                        $teamworkusers[] = $user;

                        $tmp = $participants[$user->userid];
                        $tmp->teamworkusers = $teamworkusers;

                        $tmp->firstname = $card['cardname'];
                        $tmp->lastname = '';
                        $tmp->linkprofile = false;
                    }
                }

                $participantsnew[$user->userid] = $tmp;
            }

            $participants = $participantsnew;
        }

        // Anonymous mode.
        if ($this->get_anon_state_for_user()) {
            if (!$this->if_teamwork_enable()) {
                $anonymouscount = 1;
                foreach ($participants as $key => $participant) {
                    $participants[$key]->firstname = get_string('anonuser', 'quiz_assessmentdiscussion') . ' ' . $anonymouscount;
                    $participants[$key]->lastname = '';
                    $participants[$key]->linkprofile = false;
                    $anonymouscount++;
                }
            } else {
                $anonymouscount = 1;
                foreach ($participants as $key => $participant) {

                    $teamworkusers = [];
                    foreach ($participant->teamworkusers as $teamworkuser) {
                        $teamworkuser->name = get_string('anonuser', 'quiz_assessmentdiscussion') . ' ' . $anonymouscount;
                        $anonymouscount++;

                        $teamworkusers[] = $teamworkuser;
                    }

                    $participants[$key]->teamworkusers = $teamworkusers;
                }
            }
        }

        return $participants;
    }

    public function prepare_user_and_attempts_for_question($question) {

        // Get users and attempts.
        $question->users = $this->get_user_and_attempts($question);

        $waitforgrade = $attemptsfailed = [];
        foreach ($question->users as $user) {
            foreach ($user->attempts as $attempt) {

                // Count wait for grade.
                if (in_array($attempt->gradestate, ['requiresgrading', 'notanswered'])) {
                    $waitforgrade[] = $attempt->userid;
                }

                // Count failed attempt.
                if ($attempt->gradestate == 'incorrect') {
                    $attemptsfailed[] = $user->userid;
                }
            }
        }

        $waitforgrade = array_unique($waitforgrade);
        $question->waitforgrade = count($waitforgrade);

        $question->attemptsfailed = count($attemptsfailed);

        return $question;
    }

    public function recache_grade($qid, $userid, $grade, $comment) {

        $assessmentcache = new \quiz_assessmentdiscussion\assessmentcache($this->cm->id);

        $uniquecache = $qid . $userid;

        if ($assessmentcache->user_attempts_grade()->check_cache($uniquecache) && !empty($grade) ) {

            if ($grade > 0) {
                if ($decimalpoints = get_config('quiz', 'decimalpoints')) {
                    $grade = round($grade, $decimalpoints);
                    $grade = number_format($grade, $decimalpoints);
                }
            }

            $res = $assessmentcache->user_attempts_grade()->get($uniquecache);

            $res['gradearea']['mark'] = $grade;
            $res['gradearea']['comment'] = $comment;

            $a = new \StdClass();
            $a->grade = $grade;
            $a->maxgrade = $res['gradearea']['maxmark'];

            $res['qpoints'] = get_string('staticticgrades', 'quiz_assessmentdiscussion', $a);

            // Attempts.
            $el = end($res['attempts']);
            $el->gradestate = 'correct';

            $res['attempts'][count($res['attempts']) - 1] = $el;

            $assessmentcache->user_attempts_grade()->set($uniquecache, $res);
        }
    }

    private function get_user_and_attempts($question) {
        global $DB, $USER;

        $participants = $this->get_participants();

        $assessmentcache = new \quiz_assessmentdiscussion\assessmentcache($this->cm->id);

        // Get all attempts.
        if ($assessmentcache->all_attempts()->check_cache($this->cm->instance)) {
            $allattempts = $assessmentcache->all_attempts()->get($this->cm->instance);
        } else {
            $allattempts = $this->quiz_get_all_attempts($this->cm->instance, 'finished', false);
            $assessmentcache->all_attempts()->set($this->cm->instance, $allattempts);
        }

        // Get all discussion.
        $alldiscussion = $DB->get_records('assessmentdiscussion_discus', [
                'userid' => $USER->id,
                'cmid' => $this->cm->id,
                'groupid' => $this->groupid,
                'qid' => $question->id,
        ]);

        $data = [];
        foreach ($participants as $student) {

            $userattempts = [];
            foreach ($allattempts as $attempt) {
                if ($attempt->userid == $student->id) {
                    $userattempts[] = $attempt;
                }
            }

            if ($userattempts) {
                $user = new \StdClass();
                $user->userid = $student->id;
                $user->username = $student->username;
                $user->idnumber = $student->idnumber;
                $user->firstname = $student->firstname;
                $user->lastname = $student->lastname;
                $user->email = $student->email;
                $user->linkprofile = $student->linkprofile;
                $user->teamworkenable = $this->if_teamwork_enable();
                $user->teamworkusers = $student->teamworkusers;

                // Discussion enable for user.
                $discussionenable = false;
                foreach ($alldiscussion as $discussion) {
                    if ($discussion->selecteduserid == $student->id) {
                        $discussionenable = true;
                        break;
                    }
                }

                $user->discussion_user_enable = $discussionenable;

                // Prepare attempts and gradearea.
                $uniquecache = $question->id . $student->id;
                if ($assessmentcache->user_attempts_grade()->check_cache($uniquecache)) {
                    $res = $assessmentcache->user_attempts_grade()->get($uniquecache);
                } else {
                    list($attempts, $gradearea, $qpoints) = $this->get_attempts_grade_area($question, $userattempts, $student);
                    $res = ['attempts' => $attempts, 'gradearea' => $gradearea, 'qpoints' => $qpoints];
                    $assessmentcache->user_attempts_grade()->set($uniquecache, $res);
                }

                $user->attempts = $res['attempts'];
                $user->gradearea = $res['gradearea'];
                $user->qpoints = $res['qpoints'];

                // Add unique id for gradearea.
                $user->gradearea['uniqueid'] = 'comment'.rand(1000000, 9999999).$student->id.$question->id;

                // Discussion enable for attempt.
                foreach ($user->attempts as $key => $attempt) {
                    $discussionenable = false;
                    foreach ($alldiscussion as $discussion) {
                        if ($discussion->selecteduserid == $student->id && $discussion->attemptid == $attempt->id) {
                            $discussionenable = true;
                            break;
                        }
                    }

                    $user->attempts[$key]->discussion_attempt_enable = $discussionenable;
                }

                $data[] = $user;
            }
        }

        return $data;
    }

    private function get_attempts_grade_area($question, $userattempts, $student) {
        global $PAGE;

        $qid = $question->id;
        $slot = $question->slot;

        $lastattemptid = null;
        foreach ($userattempts as $key => $attempt) {
            $lastattemptid = $attempt->id;

            $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);
            $state = $attemptobj->get_question_state_class($slot, true);

            if ($attemptobj->get_question_mark($slot)) {
                if ($this->quiz->sumgrades == 0) {
                    $grade = 0;
                }else {
                    $grade = $attemptobj->get_question_mark($slot) * $this->quiz->grade / $this->quiz->sumgrades;
                }
            } else {
                $grade = 0;
            }

            // States: correct, incorrect, requiresgrading, partiallycorrect.
            $attempt->gradestate = $state;
            $attempt->grade = $grade;

            // Unique id.
            $attempt->unique = $attempt->id.$slot.$student->id;

            // Slot.
            $attempt->slot = $slot;

            $attempt->cmid = $this->cm->id;
            $attempt->qid = $qid;

            $attempt->userid = $student->id;

            // Discussion attempt disable by default.
            $attempt->discussion_attempt_enable = false;

            $userattempts[$key] = $attempt;
        }

        // Grade area.
        $lastgrade = '';
        $gradearea = ['enable' => false];

        if ($lastattemptid !== null) {
            $fattempt = "quiz_create_attempt_handling_errors";
            $attemptobj = $fattempt($lastattemptid, $this->cm->id);
            $questionattempt = $attemptobj->get_question_attempt($slot);

            $lastattemptstate = $attemptobj->get_question_state_class($slot, true);

            $grade = $questionattempt->get_mark();
            if ($grade == '0') {
                $grade = 0;
            }

            if ($grade > 0) {
                if ($decimalpoints = get_config('quiz', 'decimalpoints')) {
                    $grade = round($grade, $decimalpoints);
                    $grade = number_format($grade, $decimalpoints);
                }
            }

            $lastgrade = $grade;

            $gradeinfo = '';
            if (isset($questionattempt->get_question()->graderinfo)) {
                $gradeinfo =
                        $questionattempt->rewrite_pluginfile_urls($questionattempt->get_question()->graderinfo,
                                'qtype_' . $questionattempt->get_question()->get_type_name(), 'graderinfo',
                                $questionattempt->get_question()->id);
            }

            // Comment area.
            $comment = $questionattempt->get_manual_comment();
            $comment = !empty($comment[0]) ? $comment[0] : " ";

            $templatedata = [
                    'enable' => true,
                    'attemptid' => $attemptobj->get_attemptid(),
                    'slot' => $slot,
                    'slots' => $slot,
                    'sesskey' => sesskey(),
                    'mark' => in_array($lastattemptstate, ['requiresgrading', 'notanswered']) ? '' : $grade,
                    'maxmark' => number_format($questionattempt->get_max_mark(), 2, '.', ''),
                    'minfraction' => $questionattempt->get_min_fraction(),
                    'maxfraction' => $questionattempt->get_max_fraction(),
                    'graderinfo' => $gradeinfo,
                    'comment' => $comment,
                    'opened' => in_array($lastattemptstate, ['requiresgrading', 'notanswered']) ? true : false,
            ];

            // Type of view grade area.
            $typeeditor = 'textarea';

            switch ($typeeditor) {
                case 'textarea':
                    $templatedata['editor_textarea'] = true;
                    break;
                case 'iframe':
                    $templatedata['editor_iframe'] = true;

                    $templatedata['prefix'] = 'q' . $attemptobj->get_uniqueid() . ':' . $slot . '_';;

                    $link = new \moodle_url('/mod/quiz/report/assessmentdiscussion/iframecomment.php',
                            ['attempt' => $attempt->id, 'slot' => $attempt->slot]) ;
                    $templatedata['link'] = $link->out();
                    break;
            }

            $gradearea = $templatedata;
        }

        $a = new \StdClass();
        $a->grade = $lastgrade;
        $a->maxgrade = $question->qmark;

        $qpoints = get_string('staticticgrades', 'quiz_assessmentdiscussion', $a);

        return [array_values($userattempts), $gradearea, $qpoints];
    }

    private function get_anon_state_for_user() {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $this->cm->id;
        return get_user_preferences($name, 0, $USER->id);
    }

    // Create custom function from function quiz_get_user_attempts().
    private function quiz_get_all_attempts($quizids, $status = 'finished', $includepreviews = false) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $params = array();
        switch ($status) {
            case 'all':
                $statuscondition = '';
                break;

            case 'finished':
                $statuscondition = ' AND state IN (:state1, :state2)';
                $params['state1'] = \mod_quiz\quiz_attempt::FINISHED;
                $params['state2'] = \mod_quiz\quiz_attempt::ABANDONED;
                break;

            case 'unfinished':
                $statuscondition = ' AND state IN (:state1, :state2)';
                $params['state1'] = \mod_quiz\quiz_attempt::IN_PROGRESS;
                $params['state2'] = \mod_quiz\quiz_attempt::OVERDUE;
                break;
        }

        $quizids = (array) $quizids;
        list($insql, $inparams) = $DB->get_in_or_equal($quizids, SQL_PARAMS_NAMED);
        $params += $inparams;

        $previewclause = '';
        if (!$includepreviews) {
            $previewclause = ' AND preview = 0';
        }

        return $DB->get_records_select('quiz_attempts',
                "quiz $insql " . $previewclause . $statuscondition,
                $params, 'quiz, attempt ASC');
    }

    private function prepare_groups($groupid) {
        global $USER;

        // If group enable.
        $this->groupmodeenable = true;
        if ($this->course->groupmode == 0) {
            $this->groupmodeenable = false;
        } else {
            if ($this->cm->groupmode == 0) {
                $this->groupmodeenable = false;
            }
        }

        if (!$this->groupmodeenable) {
            $groupid = 0;
        }

        $this->groupid = $groupid;

        // Check states.
        $coursecontext = \context_course::instance($this->course->id);
        $roles = get_user_roles($coursecontext, $USER->id);

        // Get user groups.
        $usergroups = [];
        $d = groups_get_user_groups($this->course->id, $USER->id);
        if (isset($d[0])) {
            $usergroups = $d[0];
        }

        // If editing teacher.
        $flageditingteacher = false;
        foreach ($roles as $role) {
            if ($role->shortname == 'teacher') {
                $flageditingteacher = true;
            }
        }

        if ($this->groupmodeenable && $flageditingteacher && !empty($usergroups)) {
            $groups = [];

            foreach (groups_get_all_groups($this->course->id) as $group) {
                if (in_array($group->id, $usergroups)) {
                    $tmp = [
                            'name' => $group->name,
                            'value' => $group->id,
                    ];

                    $groups[] = $tmp;
                }
            }

            if (!in_array($groupid, $usergroups)) {
                $this->groupid = $groups[0]['value'];
            }
        } else {
            // Prepare standart groups.
            $groups = [];
            $groups[] = [
                    'name' => get_string('allusers', 'quiz_assessmentdiscussion'),
                    'value' => 0,
            ];

            if ($this->groupmodeenable) {
                foreach (groups_get_all_groups($this->course->id) as $group) {
                    $tmp = [
                            'name' => $group->name,
                            'value' => $group->id,
                    ];

                    $groups[] = $tmp;
                }
            }
        }

        $this->groups = $groups;
    }

}
