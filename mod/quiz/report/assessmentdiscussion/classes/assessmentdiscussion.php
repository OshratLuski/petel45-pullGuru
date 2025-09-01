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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/quizpreset/classes/custom_types.php');
require_once($CFG->dirroot . '/cohort/lib.php');

class assessmentdiscussion {

    const tab_waitgrade = 1;
    const tab_all = 2;
    const tab_discussion = 3;

    const sort_mosterrors = 1;
    const sort_vieworderinquiz = 2;

    const sort_first_attempt = 3;
    const sort_last_attempt = 4;

    public $quizinfo;
    public $course;
    public $cm;
    public $quiz;
    public $questions;
    public $teamworkenable;
    public $quizpreset;

    public $groupid;
    public $groupmodeenable;
    public $groups;

    public function __construct($cmid, $groupid, $anonymousmode = -1) {
        global $DB;

        $quizinfo = new \quiz_assessmentdiscussion\quizinfo($cmid, $groupid);

        $this->quizinfo = $quizinfo;

        $this->cm = $quizinfo->cm;
        $this->quiz = $quizinfo->quiz;
        $this->course = $quizinfo->course;

        $this->groupid = $quizinfo->groupid;
        $this->groupmodeenable = $quizinfo->groupmodeenable;
        $this->groups = $quizinfo->groups;

        if ($anonymousmode != -1) {
            $this->set_anon_state_for_user($anonymousmode);
        }

        $this->questions = $quizinfo->get_questions_for_report();

        $this->teamworkenable = $quizinfo->if_teamwork_enable();

        if ($qp = $DB->get_record('local_quizpreset', array('cmid' => $this->cm->id, 'status' => 1))) {
            $this->quizpreset = $qp->type;
        }
    }

    private function prepare_tabs_questions($tabid, $sort) {
        global $DB, $USER;

        // Prepare users and attempts.
        foreach ($this->questions as $key => $question) {
            $this->questions[$key] = $this->quizinfo->prepare_user_and_attempts_for_question($question);
        }

        switch ($sort) {
            case self::sort_mosterrors:

                $questions = $this->questions;
                usort($questions, function($a, $b) {

                    $field = 'attemptsfailed';

                    if ($a->$field == $b->$field) {
                        return 0;
                    }

                    // Desc.
                    return ($a->$field > $b->$field) ? -1 : 1;

                    // Asc.
                    //return ($a->$field < $b->$field) ? -1 : 1;

                    //return 0;
                });
                break;
            case self::sort_vieworderinquiz:
                $questions = $this->questions;
                break;
            default:
                $questions = $this->questions;
        }

        // Waitgrade.
        $waitgrade = [];
        foreach ($questions as $question) {
            if ($question->waitforgrade > 0) {
                $waitgrade[] = $question;
            }
        }

        // All.
        $all = $questions;

        // Discussion.
        $discussion = $qids = [];
        foreach ($DB->get_records('assessmentdiscussion_discus',
                ['userid' => $USER->id, 'cmid' => $this->cm->id, 'groupid' => $this->groupid]) as $item) {
            $qids[] = $item->qid;
        }

        foreach ($questions as $question) {
            if (in_array($question->id, $qids)) {
                $discussion[] = $question;
            }
        }

        $tabs = [];

        // Wait grade.
        $tabs[] = [
                'id' => self::tab_waitgrade,
                'name' => get_string('questionswaitingtobegraded', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => count($waitgrade) == 0 ? true : false,
                'clickable' => true,
                'questions' => $waitgrade,
                'count' => count($waitgrade),
        ];

        // All.
        $tabs[] = [
                'id' => self::tab_all,
                'name' => get_string('allquestions', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => count($all) == 0 ? true : false,
                'clickable' => true,
                'questions' => $all,
                'count' => count($all),
        ];

        // Discussion.
        $tabs[] = [
                'id' => self::tab_discussion,
                'name' => get_string('forclassdiscussion', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => count($discussion) == 0 ? true : false,
                'clickable' => true,
                'questions' => $discussion,
                'count' => count($discussion),
        ];

        // Check tabid.
        $disabledtabids = $activetabids = [];
        foreach ($tabs as $tab) {
            if ($tab['disable'] == true) {
                $disabledtabids[] = $tab['id'];
            } else {
                $activetabids[] = $tab['id'];
            }
        }

        if (!empty($disabledtabids) && !empty($activetabids) && in_array($tabid, $disabledtabids)) {
            $tabid = $activetabids[0];
        }

        // Prepare tab active and disable.
        foreach ($tabs as $key => $tab) {
            if ($tab['id'] == $tabid) {
                $tab['active'] = true;
            }

            if ($tab['active'] || $tab['disable']) {
                $tab['clickable'] = false;
            }

            $tabs[$key] = $tab;
        }

        return $tabs;
    }

    public function prepare_data_for_dashboard_template($tabactive, $anonymousmode, $sort, $qid = null) {
        $data = [];

        // Groups.
        $datagroups = $this->groups;

        foreach ($datagroups as $key => $group) {

            $datagroups[$key]['selected'] = $this->groupid == $group['value'] ? true : false;

            if ($group['value'] == $this->groupid) {
                $groupcurrentvalue = $this->groupid;
                $groupcurrentname = $group['name'];
            }
        }

        $data['group'] = [
                'dir_rtl' => right_to_left(),
                'currentname' => $groupcurrentname,
                'currentvalue' => $groupcurrentvalue,
                'data' => $datagroups,
                'groupmodeenable' => $this->groupmodeenable,
        ];

        // Sorting.
        if ($sort == null) {
            $sort = $this->get_default_dasboard_sort();
        }

        switch ($sort) {
            case self::sort_mosterrors:
                $sortcurrentname = get_string('mosterrors', 'quiz_assessmentdiscussion');
                $sortcurrentvalue = self::sort_mosterrors;
                break;
            case self::sort_vieworderinquiz:
                $sortcurrentname = get_string('vieworderinquiz', 'quiz_assessmentdiscussion');
                $sortcurrentvalue = self::sort_vieworderinquiz;
                break;
            default:
                $sortcurrentname = get_string('mosterrors', 'quiz_assessmentdiscussion');
                $sortcurrentvalue = self::sort_mosterrors;
        }

        $datasort = [
                [
                        'name' => get_string('mosterrors', 'quiz_assessmentdiscussion'),
                        'value' => self::sort_mosterrors,
                        'selected' => $sortcurrentvalue == self::sort_mosterrors ? true : false,
                ],
                [
                        'name' => get_string('vieworderinquiz', 'quiz_assessmentdiscussion'),
                        'value' => self::sort_vieworderinquiz,
                        'selected' => $sortcurrentvalue == self::sort_vieworderinquiz ? true : false,
                ]
        ];

        $data['sort'] = [
                'dir_rtl' => right_to_left(),
                'currentname' => $sortcurrentname,
                'currentvalue' => $sortcurrentvalue,
                'data' => $datasort,
        ];

        // Tabs.
        $tabs = $this->prepare_tabs_questions($tabactive, $sort);

        $data['tabs'] = $tabs;

        // Questions.
        $questions = [];
        foreach ($tabs as $tab) {
            if ($tab['active'] == true) {
                $questions = $tab['questions'];
                break;
            }
        }

        // Check if qid real.
        $arrqids = [];
        foreach ($questions as $question) {
            $arrqids[] = $question->id;
        }

        if (!in_array($qid, $arrqids)) {
            $qid = null;
        }

        // Get active question.
        if ($qid == null && !empty($questions)) {
            $activequestion = reset($questions);
            $qid = $activequestion->id;
        }

        foreach ($questions as $key => $question) {
            if ($question->id == $qid) {
                $question->active = true;
            } else {
                $question->active = false;
            }

            $questions[$key] = $question;
        }

        if ($qid == null || empty($qid)) {
            $qid = 0;
        }

        $data['questions'] = $questions;
        $data['activeqid'] = $qid;
        $data['ansersareaenable'] = false;
        $data['anonymous_mode'] = $anonymousmode;

        // Quizpreset.
        $data['quizpreset_type4_enable'] = $this->quizpreset == QUIZ_TYPE_4;
        $data['quizpreset_button_state'] = $this->quiz->timeclose > 0;

        return $data;
    }

    public function get_default_dasboard_sort() {
        return self::sort_mosterrors;
    }

    public function get_default_dasboard_tab() {
        return self::tab_waitgrade;
    }

    private function prepare_tabs_answers($question, $tabid, $sort) {
        global $DB, $USER;

        // Wait for grade.
        $waitforgrade = [];
        foreach ($question->users as $user) {
            $lastelem = end($user->attempts);
            if (in_array($lastelem->gradestate, ['requiresgrading', 'notanswered'])) {
                $waitforgrade[] = $user;
            }
        }

        // With grade.
        //$all = $question->users;

        $all = [];
        foreach ($question->users as $user) {
            $lastelem = end($user->attempts);
            if (!in_array($lastelem->gradestate, ['requiresgrading', 'notanswered'])) {
                $all[] = $user;
            }
        }

        // Discussion.
        $discussion = $usersattempts = [];
        foreach ($DB->get_records('assessmentdiscussion_discus',
                ['userid' => $USER->id, 'cmid' => $this->cm->id, 'groupid' => $this->groupid, 'qid' => $question->id]) as $item) {

            $usersattempts[$item->selecteduserid][] = $item->attemptid;
            $usersattempts[$item->selecteduserid] = array_filter($usersattempts[$item->selecteduserid]);
        }

        foreach ($question->users as $user) {
            foreach ($usersattempts as $userid => $attemptids) {

                if ($user->userid == $userid && count($attemptids) == 0) {
                    $discussion[] = $user;
                }

                if ($user->userid == $userid && count($attemptids) > 0) {
                    $attempts = [];
                    foreach ($user->attempts as $attempt) {
                        if (in_array($attempt->id, $attemptids)) {
                            $attempts[] = $attempt;
                        }
                    }

                    $user->attempts = $attempts;

                    $discussion[] = $user;
                }
            }
        }

        $tabs = [];

        // Wait for grade.
        $tabs[] = [
                'id' => self::tab_waitgrade,
                'name' => get_string('attemptswaitingtobegraded', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => $this->count_answers($waitforgrade) == 0 ? true : false,
                'clickable' => true,
                'users' => $this->sorting_answers($waitforgrade, $sort),
                'count' => $this->count_answers($waitforgrade),
        ];

        // All.
        $tabs[] = [
                'id' => self::tab_all,
                'name' => get_string('allattempts', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => $this->count_answers($all) == 0 ? true : false,
                'clickable' => true,
                'users' => $this->sorting_answers($all, $sort),
                'count' => $this->count_answers($all),
        ];

        // Discussion.
        $tabs[] = [
                'id' => self::tab_discussion,
                'name' => get_string('attemptsdiscussion', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => $this->count_answers($discussion) == 0 ? true : false,
                'clickable' => true,
                'users' => $this->sorting_answers($discussion, $sort),
                'count' => $this->count_answers($discussion),
        ];

        // Check tabid.
        $disabledtabids = $activetabids = [];
        foreach ($tabs as $tab) {
            if ($tab['disable'] == true) {
                $disabledtabids[] = $tab['id'];
            } else {
                $activetabids[] = $tab['id'];
            }
        }

        if (!empty($disabledtabids) && !empty($activetabids) && in_array($tabid, $disabledtabids)) {
            $tabid = $activetabids[0];
        }

        // Prepare tab active and disable.
        foreach ($tabs as $key => $tab) {
            if ($tab['id'] == $tabid) {
                $tab['active'] = true;
            }

            if ($tab['active'] || $tab['disable']) {
                $tab['clickable'] = false;
            }

            if ($tab['id'] == self::tab_discussion) {
                $tab['icon'] = '
                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="19" height="15"
                         viewBox="0 0 19 15" fill="none">
                        <g clip-path="url(#clip0_1191_4768)">
                            <path
                                    d="M16.2518 1.1998H6.35176C5.85395 1.1998 5.45176 1.60199 5.45176 2.0998V3.1123C5.16488 3.03918 4.86113 2.9998 4.55176 2.9998V2.0998C4.55176 1.10699 5.35895 0.299805 6.35176 0.299805H16.2518C17.2446 0.299805 18.0518 1.10699 18.0518 2.0998V10.1998C18.0518 11.1926 17.2446 11.9998 16.2518 11.9998H15.8018H11.3018H10.8518H9.52426C9.37519 11.6792 9.19238 11.3754 8.97582 11.0998H10.8518V9.7498C10.8518 9.00449 11.4564 8.3998 12.2018 8.3998H14.9018C15.6471 8.3998 16.2518 9.00449 16.2518 9.7498V11.0998C16.7496 11.0998 17.1518 10.6976 17.1518 10.1998V2.0998C17.1518 1.60199 16.7496 1.1998 16.2518 1.1998ZM15.3518 11.0998V9.7498C15.3518 9.5023 15.1493 9.2998 14.9018 9.2998H12.2018C11.9543 9.2998 11.7518 9.5023 11.7518 9.7498V11.0998H15.3518ZM6.35176 6.5998C6.35176 6.12242 6.16212 5.66458 5.82455 5.32701C5.48698 4.98945 5.02915 4.7998 4.55176 4.7998C4.07437 4.7998 3.61653 4.98945 3.27897 5.32701C2.9414 5.66458 2.75176 6.12242 2.75176 6.5998C2.75176 7.07719 2.9414 7.53503 3.27897 7.8726C3.61653 8.21016 4.07437 8.3998 4.55176 8.3998C5.02915 8.3998 5.48698 8.21016 5.82455 7.8726C6.16212 7.53503 6.35176 7.07719 6.35176 6.5998ZM1.85176 6.5998C1.85176 5.88372 2.13622 5.19696 2.64257 4.69062C3.14892 4.18427 3.83567 3.8998 4.55176 3.8998C5.26784 3.8998 5.9546 4.18427 6.46095 4.69062C6.96729 5.19696 7.25176 5.88372 7.25176 6.5998C7.25176 7.31589 6.96729 8.00265 6.46095 8.50899C5.9546 9.01534 5.26784 9.2998 4.55176 9.2998C3.83567 9.2998 3.14892 9.01534 2.64257 8.50899C2.13622 8.00265 1.85176 7.31589 1.85176 6.5998ZM0.95457 13.7998H8.14894C8.0702 12.2951 6.82707 11.0998 5.3027 11.0998H3.80082C2.27645 11.0998 1.03332 12.2951 0.95457 13.7998ZM0.0517578 13.9489C0.0517578 11.8789 1.73082 10.1998 3.80082 10.1998H5.29988C7.37269 10.1998 9.05176 11.8789 9.05176 13.9489C9.05176 14.3623 8.71707 14.6998 8.30082 14.6998H0.802695C0.386445 14.6998 0.0517578 14.3651 0.0517578 13.9489Z"
                                    fill="#554283" />
                        </g>
                        <defs>
                            <clipPath id="clip0_1191_4768">
                                <rect width="18" height="14.4" fill="white"
                                      transform="translate(0.0517578 0.299805)" />
                            </clipPath>
                        </defs>
                    </svg>                
                ';
            } else {
                $tab['icon'] = '';
            }

            $tabs[$key] = $tab;
        }

        return $tabs;
    }

    private function prepare_tabs_answers_overlay($question, $tabid, $sort) {
        global $DB, $USER;

        // Wait for grade.
        $waitforgrade = [];
        foreach ($question->users as $user) {
            $tmpattempts = [];
            foreach ($user->attempts as $attempt) {
                if (in_array($attempt->gradestate, ['requiresgrading', 'notanswered'])) {
                    $tmpattempts[] = $attempt;
                }
            }

            if (count($tmpattempts) > 0) {
                $tmp = $user;
                $tmp->attempts = $tmpattempts;
                $waitforgrade[] = $tmp;
            }
        }

        // All.
        $all = $question->users;

        // Discussion.
        $discussion = $usersattempts = [];
        foreach ($DB->get_records('assessmentdiscussion_discus',
                ['userid' => $USER->id, 'cmid' => $this->cm->id, 'groupid' => $this->groupid, 'qid' => $question->id]) as $item) {

            $usersattempts[$item->selecteduserid][] = $item->attemptid;
            $usersattempts[$item->selecteduserid] = array_filter($usersattempts[$item->selecteduserid]);
        }

        foreach ($question->users as $user) {
            foreach ($usersattempts as $userid => $attemptids) {

                if ($user->userid == $userid && count($attemptids) == 0) {
                    $discussion[] = $user;
                }

                if ($user->userid == $userid && count($attemptids) > 0) {
                    $attempts = [];
                    foreach ($user->attempts as $attempt) {
                        if (in_array($attempt->id, $attemptids)) {
                            $attempts[] = $attempt;
                        }
                    }

                    $user->attempts = $attempts;

                    $discussion[] = $user;
                }
            }
        }

        $tabs = [];

        // Discussion.
        $tabs[] = [
                'id' => self::tab_discussion,
                'name' => get_string('attemptsdiscussion', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => $this->count_answers($discussion) == 0 ? true : false,
                'clickable' => true,
                'users' => $this->sorting_answers($discussion, $sort),
                'count' => $this->count_answers($discussion),
        ];

        // All.
        $tabs[] = [
                'id' => self::tab_all,
                'name' => get_string('allattempts', 'quiz_assessmentdiscussion'),
                'active' => false,
                'disable' => $this->count_answers($all) == 0 ? true : false,
                'clickable' => true,
                'users' => $this->sorting_answers($all, $sort),
                'count' => $this->count_answers($all),
        ];

        // Check tabid.
        $disabledtabids = $activetabids = [];
        foreach ($tabs as $tab) {
            if ($tab['disable'] == true) {
                $disabledtabids[] = $tab['id'];
            } else {
                $activetabids[] = $tab['id'];
            }
        }

        if (!empty($disabledtabids) && !empty($activetabids) && in_array($tabid, $disabledtabids)) {
            $tabid = $activetabids[0];
        }

        // Prepare tab active and disable.
        foreach ($tabs as $key => $tab) {
            if ($tab['id'] == $tabid) {
                $tab['active'] = true;
            }

            if ($tab['active'] || $tab['disable']) {
                $tab['clickable'] = false;
            }

            if ($tab['id'] == self::tab_discussion) {
                $tab['icon'] = '
                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="19" height="15"
                         viewBox="0 0 19 15" fill="none">
                        <g clip-path="url(#clip0_1191_4768)">
                            <path
                                    d="M16.2518 1.1998H6.35176C5.85395 1.1998 5.45176 1.60199 5.45176 2.0998V3.1123C5.16488 3.03918 4.86113 2.9998 4.55176 2.9998V2.0998C4.55176 1.10699 5.35895 0.299805 6.35176 0.299805H16.2518C17.2446 0.299805 18.0518 1.10699 18.0518 2.0998V10.1998C18.0518 11.1926 17.2446 11.9998 16.2518 11.9998H15.8018H11.3018H10.8518H9.52426C9.37519 11.6792 9.19238 11.3754 8.97582 11.0998H10.8518V9.7498C10.8518 9.00449 11.4564 8.3998 12.2018 8.3998H14.9018C15.6471 8.3998 16.2518 9.00449 16.2518 9.7498V11.0998C16.7496 11.0998 17.1518 10.6976 17.1518 10.1998V2.0998C17.1518 1.60199 16.7496 1.1998 16.2518 1.1998ZM15.3518 11.0998V9.7498C15.3518 9.5023 15.1493 9.2998 14.9018 9.2998H12.2018C11.9543 9.2998 11.7518 9.5023 11.7518 9.7498V11.0998H15.3518ZM6.35176 6.5998C6.35176 6.12242 6.16212 5.66458 5.82455 5.32701C5.48698 4.98945 5.02915 4.7998 4.55176 4.7998C4.07437 4.7998 3.61653 4.98945 3.27897 5.32701C2.9414 5.66458 2.75176 6.12242 2.75176 6.5998C2.75176 7.07719 2.9414 7.53503 3.27897 7.8726C3.61653 8.21016 4.07437 8.3998 4.55176 8.3998C5.02915 8.3998 5.48698 8.21016 5.82455 7.8726C6.16212 7.53503 6.35176 7.07719 6.35176 6.5998ZM1.85176 6.5998C1.85176 5.88372 2.13622 5.19696 2.64257 4.69062C3.14892 4.18427 3.83567 3.8998 4.55176 3.8998C5.26784 3.8998 5.9546 4.18427 6.46095 4.69062C6.96729 5.19696 7.25176 5.88372 7.25176 6.5998C7.25176 7.31589 6.96729 8.00265 6.46095 8.50899C5.9546 9.01534 5.26784 9.2998 4.55176 9.2998C3.83567 9.2998 3.14892 9.01534 2.64257 8.50899C2.13622 8.00265 1.85176 7.31589 1.85176 6.5998ZM0.95457 13.7998H8.14894C8.0702 12.2951 6.82707 11.0998 5.3027 11.0998H3.80082C2.27645 11.0998 1.03332 12.2951 0.95457 13.7998ZM0.0517578 13.9489C0.0517578 11.8789 1.73082 10.1998 3.80082 10.1998H5.29988C7.37269 10.1998 9.05176 11.8789 9.05176 13.9489C9.05176 14.3623 8.71707 14.6998 8.30082 14.6998H0.802695C0.386445 14.6998 0.0517578 14.3651 0.0517578 13.9489Z"
                                    fill="#554283" />
                        </g>
                        <defs>
                            <clipPath id="clip0_1191_4768">
                                <rect width="18" height="14.4" fill="white"
                                      transform="translate(0.0517578 0.299805)" />
                            </clipPath>
                        </defs>
                    </svg>                
                ';
            } else {
                $tab['icon'] = '';
            }

            $tabs[$key] = $tab;
        }

        return $tabs;
    }

    private function sorting_answers($users, $sort) {

        // Sorting.
        $result = [];
        foreach ($users as $user) {
            $user->open_block = [];
            $user->collapsed_block = [];

            $attempts = array_values($user->attempts);

            if (empty($attempts)) {
                continue;
            }

            switch ($sort) {
                case self::sort_first_attempt:
                    break;
                case self::sort_last_attempt:
                    $attempts = array_reverse($attempts);
                    $attempts = array_values($attempts);
                    break;
            }

            // Prepare open and collapsed blocks.
            $user->open_block[] = $attempts[0];
            unset($attempts[0]);

            $user->collapsed_block = array_values($attempts);
            $user->collapsed_block_enable = !empty($user->collapsed_block);

            $result[] = $user;
        }

        return $result;
    }

    private function count_answers($users) {

        //$count = 0;
        //foreach ($users as $user) {
        //    $count += count($user->attempts);
        //}

        $count = count($users);

        return $count;
    }

    public function prepare_data_for_answer_area($qid, $tabactive, $anonymousmode, $sort) {
        $question = new \StdClass();
        $question->ansersareaenable = false;

        $tabs = [];
        foreach ($this->questions as $item) {
            if ($item->id == $qid) {
                $question = $this->quizinfo->prepare_user_and_attempts_for_question($item);
                $question->ansersareaenable = true;

                $tabs = $this->prepare_tabs_answers($question, $tabactive, $sort);
                break;
            }
        }

        $data = (array) $question;

        // Sorting.
        switch ($sort) {
            case self::sort_last_attempt:
                $currentname = get_string('lastattempt', 'quiz_assessmentdiscussion');
                $currentvalue = self::sort_last_attempt;
                break;
            case self::sort_first_attempt:
                $currentname = get_string('firstattempt', 'quiz_assessmentdiscussion');
                $currentvalue = self::sort_first_attempt;
                break;
            default:
                $currentname = get_string('lastattempt', 'quiz_assessmentdiscussion');
                $currentvalue = self::sort_last_attempt;
        }

        $datasort = [
                [
                        'name' => get_string('lastattempt', 'quiz_assessmentdiscussion'),
                        'value' => self::sort_last_attempt,
                        'selected' => $currentvalue == self::sort_last_attempt ? true : false,
                ],
                [
                        'name' => get_string('firstattempt', 'quiz_assessmentdiscussion'),
                        'value' => self::sort_first_attempt,
                        'selected' => $currentvalue == self::sort_first_attempt ? true : false,
                ]
        ];

        $data['sort'] = [
                'dir_rtl' => right_to_left(),
                'currentname' => $currentname,
                'currentvalue' => $currentvalue,
                'data' => $datasort,
        ];

        // User data.
        $usersdata = [];
        foreach ($tabs as $tab) {
            if ($tab['active'] == true) {
                $usersdata = $tab['users'];
            }
        }

        $data['tabs'] = $tabs;
        $data['usersdata'] = $usersdata;

        return $data;
    }

    public function get_default_answers_sort() {
        return self::sort_last_attempt;
    }

    public function get_default_answers_tab() {
        return self::tab_waitgrade;
    }

    private function set_anon_state_for_user($state) {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $this->cm->id;
        return set_user_preference($name, (int) $state, $USER->id);
    }

    public function get_anon_state_for_user() {
        global $USER;

        $name = 'quiz_advancedoverview_anon_' . $this->cm->id;
        return get_user_preferences($name, 0, $USER->id);
    }

    public function get_groupid() {
        return $this->groupid;
    }

    public function prepare_data_for_overlay_area($qid, $tabactive, $anonymousmode, $viewlist, $showanswers) {

        $tabs = [];
        foreach ($this->questions as $item) {
            if ($item->id == $qid) {
                $question = $this->quizinfo->prepare_user_and_attempts_for_question($item);
                $tabs = $this->prepare_tabs_answers_overlay($question, $tabactive, self::sort_first_attempt);
                break;
            }
        }

        $data = (array) $question;

        // Question dropdown.
        $tmp = [];
        foreach ($this->questions as $question) {
            if ($question->id != $data['id']) {
                $tmp[] = [
                        'value' => $question->id,
                        'name' => get_string('question') . ' ' . $question->numberview . ' ' . $question->name
                ];
            }
        }

        $qdropdown = [
                'currentvalue' => $data['id'],
                'currentname' => get_string('question') . ' ' . $data['numberview'] . ' ' . $data['name'],
                'questions' => $tmp,
        ];

        $data['qdropdown'] = $qdropdown;

        // User data.
        $usersdata = [];
        foreach ($tabs as $tab) {
            if ($tab['active'] == true) {
                $usersdata = $tab['users'];
            }
        }

        $data['tabs'] = $tabs;
        $data['anonymous_mode'] = $anonymousmode;
        $data['viewlist'] = $viewlist;
        $data['showanswers'] = $showanswers;

        // Prepare render data.
        $renderdata = [];
        $counter = 1;
        foreach ($usersdata as $user) {
            foreach ($user->attempts as $attempt) {
                $tmp = [];
                $tmp['userid'] = $user->userid;
                $tmp['firstname'] = $user->firstname;
                $tmp['lastname'] = $user->lastname;
                $tmp['active'] = ($counter == 1) ? 1 : 0;
                $tmp['teamworkenable'] = $user->teamworkenable;
                $tmp['teamworkusers'] = $user->teamworkusers;
                $tmp['counter'] = $counter;

                $tmp['attemptid'] = $attempt->id;
                $tmp['cmid'] = $attempt->cmid;
                $tmp['qid'] = $attempt->qid;
                $tmp['slot'] = $attempt->slot;

                $renderdata[] = $tmp;

                $counter++;
            }
        }

        $data['renderdata'] = $renderdata;
        $data['emptydata'] = empty($renderdata);
        $data['unique'] = time().rand(0, 10000);

        // Viewing number answer into answers in carousela.
        $data['maxanswerscount'] = count($renderdata);
        $data['firstanswernumber'] = count($renderdata) > 0 ? 1 : 0;

        return $data;
    }

    public static function check_access() {
        global $USER, $COURSE;

        if (get_config('quiz_assessmentdiscussion', 'accesscohort') == -1 &&
                get_config('quiz_assessmentdiscussion', 'accesscapability') == 0) {

            // TODO true;
            return false;
        }

        $access = false;

        // Cohort.
        if (get_config('quiz_assessmentdiscussion', 'accesscohort') != -1) {
            $usercohorts = cohort_get_user_cohorts($USER->id);
            foreach ($usercohorts as $cohort) {
                if ($cohort->id == get_config('quiz_assessmentdiscussion', 'accesscohort')) {
                    $access = true;
                    break;
                }
            }
        }

        // Capability.
        if (get_config('quiz_assessmentdiscussion', 'accesscapability') == 1) {
            if (has_capability('quiz/assessmentdiscussion:viewassessmentdiscussion',
                    \context_course::instance($COURSE->id), $USER->id)) {
                $access = true;
            }
        }

        return $access;
    }
}
