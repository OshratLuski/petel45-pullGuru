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
 * Admin settings class for the quiz browser security option.
 *
 * @package   local_quizpreset
 * @copyright 2023 Devlion
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_types_default {

    private $type;
    private $expanded;
    private $values;
    private $globalname;
    private $settypes;
    private $instancename;
    private $coursecat;

    private $cmid;
    private $pagestate;
    private $viewall;
    private $ifchangestate;
    private $urlparams;

    public function __construct($instancename) {
        $this->set_types($instancename);
    }

    public function get_types() {
        return $this->settypes;
    }

    public function set_types($instancename) {

        switch ($instancename) {
            case 'physics':
                $this->settypes = array(2, 3, 4, 1);
                $this->instancename = $instancename;
                break;
            case 'chemistry':
                $this->settypes = array(3, 4, 1, 6, 2);
                $this->instancename = $instancename;
                break;
            case 'biology':
                $this->settypes = array(2, 3, 4, 1);
                $this->instancename = $instancename;
                break;
            default:
                $this->settypes = array(2, 3, 4, 1);
                $this->instancename = 'physics';
        }
    }

    public function predefine_setting($defaulttype) {

        $savedvaluetype = null;

        // Set relevant type in class.
        if (in_array($defaulttype, $this->settypes)) {
            $this->type = $defaulttype;
            $this->ifchangestate = ($savedvaluetype == $defaulttype) ? false : true;
        } else {
            return false;
        }

        $this->set_state();
        return true;
    }

    public function predefine($cmid, $defaulttype, $viewall, $pagestate, $urlparams) {
        global $DB, $USER;

        $this->cmid = $cmid;
        $this->pagestate = $pagestate;
        $this->urlparams = json_decode($urlparams, true);

        // If POST.
        if (empty($this->urlparams)) {

            if ($this->pagestate == 'update') {
                $sql = "SELECT * FROM {local_quizpreset} WHERE cmid = ? AND status = 0 ORDER BY id DESC LIMIT 1";
                $qp = $DB->get_record_sql($sql, array($cmid));
                $qp->viewall = 1;
            }

            if ($this->pagestate == 'new') {
                $sql = "SELECT * FROM {local_quizpreset} WHERE userid = ? AND state = 'new' ORDER BY id DESC LIMIT 1";
                $qp = $DB->get_record_sql($sql, array($USER->id));
                $qp->viewall = 1;
            }

        } else {
            $qp = $DB->get_record('local_quizpreset', array('cmid' => $cmid, 'status' => 1));
        }

        if (!empty($qp)) {
            $savedvaluetype = $qp->type;
            $savedvalueview = $qp->viewall;
        } else {
            $savedvaluetype = null;
            $savedvalueview = null;
        }

        // Set relevant type in class.
        if (in_array($defaulttype, $this->settypes)) {
            $this->type = $defaulttype;
            $this->ifchangestate = ($savedvaluetype == $defaulttype) ? false : true;
        } else {
            $this->type = (!empty($savedvaluetype)) ? $savedvaluetype : $this->settypes[0];
            $this->ifchangestate = false;
        }

        // Set relevant viewall in class.
        if ($viewall == 100) {
            $this->viewall = (!empty($savedvalueview)) ? $savedvalueview : 0;
        } else {
            $this->viewall = $viewall;
        }

        // Define values, expanded, global name.
        $this->set_state();
    }

    public function set_state() {

        switch ($this->type) {
            case QUIZ_TYPE_1:
                $this->expanded = $this->expanded_type1();
                $this->values = $this->merge_with_default($this->values_type1());
                $this->globalname = $this->global_name_type1();
                break;
            case QUIZ_TYPE_2:
                $this->expanded = $this->expanded_type2();
                $this->values = $this->merge_with_default($this->values_type2());
                $this->globalname = $this->global_name_type2();
                break;
            case QUIZ_TYPE_3:
                $this->expanded = $this->expanded_type3();
                $this->values = $this->merge_with_default($this->values_type3());
                $this->globalname = $this->global_name_type3();
                break;
            case QUIZ_TYPE_4:
                $this->expanded = $this->expanded_type4();
                $this->values = $this->merge_with_default($this->values_type4());
                $this->globalname = $this->global_name_type4();
                break;
            case QUIZ_TYPE_5:
                $this->expanded = array();
                $this->values = array();
                $this->globalname = array();
                break;
            case QUIZ_TYPE_6:
                $this->expanded = $this->expanded_type2();
                $this->values = $this->merge_with_default($this->values_type6());
                $this->globalname = $this->global_name_type6();
                break;
            default:
                $this->expanded = array();
                $this->values = array();
                $this->globalname = array();
        }
    }

    public function expanded_type1() {
        return array(
                'id_general' => true,
                'id_timing' => true,
                'id_modstandardgrade' => false,
                'id_layouthdr' => false,
                'id_interactionhdr' => false,
                'id_reviewoptionshdr' => false,
                'id_display' => false,
                'id_security' => false,
                'id_overallfeedbackhdr' => false,
                'id_modstandardelshdr' => false,
                'id_availabilityconditionsheader' => true,
                'id_activitycompletionheader' => true,
                'id_tagshdr' => false,
                'id_competenciessection' => true,
                'id_seb' => false,
        );
    }

    public function expanded_type2() {
        return array(
                'id_general' => true,
                'id_timing' => true,
                'id_modstandardgrade' => false,
                'id_layouthdr' => false,
                'id_interactionhdr' => false,
                'id_reviewoptionshdr' => false,
                'id_display' => false,
                'id_security' => false,
                'id_overallfeedbackhdr' => false,
                'id_modstandardelshdr' => false,
                'id_availabilityconditionsheader' => true,
                'id_activitycompletionheader' => true,
                'id_tagshdr' => false,
                'id_competenciessection' => true,
                'id_seb' => false,
        );

    }

    public function expanded_type3() {
        return array(
                'id_general' => true,
                'id_timing' => true,
                'id_modstandardgrade' => false,
                'id_layouthdr' => false,
                'id_interactionhdr' => false,
                'id_reviewoptionshdr' => false,
                'id_display' => false,
                'id_security' => false,
                'id_overallfeedbackhdr' => false,
                'id_modstandardelshdr' => false,
                'id_availabilityconditionsheader' => true,
                'id_activitycompletionheader' => true,
                'id_tagshdr' => false,
                'id_competenciessection' => true,
                'id_seb' => false,
        );
    }

    public function expanded_type4() {
        return array(
                'id_general' => true,
                'id_timing' => true,
                'id_modstandardgrade' => false,
                'id_layouthdr' => false,
                'id_interactionhdr' => false,
                'id_reviewoptionshdr' => false,
                'id_display' => false,
                'id_security' => false,
                'id_overallfeedbackhdr' => false,
                'id_modstandardelshdr' => false,
                'id_availabilityconditionsheader' => true,
                'id_activitycompletionheader' => true,
                'id_tagshdr' => false,
                'id_competenciessection' => true,
                'id_seb' => false,
        );
    }

    public function global_name_type1() {
        switch ($this->instancename) {
            case 'physics':
                $name = get_string('name_physics_1', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_physics_1', 'local_quizpreset');
                break;
            case 'chemistry':
                $name = get_string('name_chemistry_1', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_chemistry_1', 'local_quizpreset');
                break;
            case 'biology':
                $name = get_string('name_biology_1', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_biology_1', 'local_quizpreset');
                break;
        }

        return array(
                'name' => $name,
                'intro' => $introtext,
                'introformat' => 1,
                'introeditor' => array
                (
                        'text' => $introtext,
                        'format' => 1,
                ),
        );
    }

    public function values_type1() {
        $data = array(
            // Timing.
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,

            // Modstandardgrade.
                'gradecat' => 0,
                'gradepass' => 55.00,
                'attempts' => 1,
                'grademethod' => 4,

            // Layouthdr.
                'questionsperpage' => 0,
                'repaginatenow' => 0,
                'navmethod' => 'free',

            // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'deferredfeedback',
                'canredoquestions' => 0,
                'attemptonlast' => 0,

            // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => false,
                        'marksduring' => false,
                        'specificfeedbackduring' => false,
                        'generalfeedbackduring' => false,
                        'rightanswerduring' => false,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => false,
                        'correctnessimmediately' => false,
                        'marksimmediately' => false,
                        'specificfeedbackimmediately' => false,
                        'generalfeedbackimmediately' => false,
                        'rightanswerimmediately' => false,
                        'overallfeedbackimmediately' => false,

                        'attemptopen' => false,
                        'correctnessopen' => false,
                        'marksopen' => false,
                        'specificfeedbackopen' => false,
                        'generalfeedbackopen' => false,
                        'rightansweropen' => false,
                        'overallfeedbackopen' => false,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),

            // Display.
                'showuserpicture' => 0,
                'decimalpoints' => 0,
                'questiondecimalpoints' => 1,
                'showblocks' => 1,
        );

        switch ($this->instancename) {
            case 'physics':
                break;
            case 'chemistry':
                $data = $this->chemistry_activity_with_score();
                $data['attempts'] = 1;
                $data['preferredbehaviour'] = 'adaptive';
                $data['preferredbehaviour'] = 'interactive';
                $data['canredoquestions'] = 0;

                $data['area_checkboxes'] = array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => true,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => true,
                        'rightanswerduring' => true,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => false,
                        'rightanswerimmediately' => false,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => false,
                        'correctnessopen' => false,
                        'marksopen' => true,
                        'specificfeedbackopen' => false,
                        'generalfeedbackopen' => false,
                        'rightansweropen' => false,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                );
                break;
            case 'biology':
                break;
        }

        return $data;
    }

    public function global_name_type2() {
        switch ($this->instancename) {
            case 'physics':
                $name = get_string('name_physics_2', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_physics_2', 'local_quizpreset');
                break;
            case 'chemistry':
                $name = get_string('name_chemistry_2', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_chemistry_2', 'local_quizpreset');
                break;
            case 'biology':
                $name = get_string('name_biology_2', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_biology_2', 'local_quizpreset');
                break;
        }

        return array(
                'name' => $name,
                'intro' => $introtext,
                'introformat' => 1,
                'introeditor' => array
                (
                        'text' => $introtext,
                        'format' => 1,
                ),
        );
    }

    public function values_type2() {
        $data = array(
                // Modstandardgrade.
                'gradecat' => 0,
                'gradepass' => 0,
                'attempts' => 0,
                'grademethod' => 1,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'interactive',
                'canredoquestions' => 1,
                'attemptonlast' => 1,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => true,
                        'rightanswerduring' => true,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => false,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => false,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => false,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        switch ($this->instancename) {
            case 'physics':
                break;
            case 'chemistry':
                $data = $this->chemistry_activity_with_score();

                $data['attempts'] = 1;
                $data['preferredbehaviour'] = 'deferredfeedback';
                $data['questionsperpage'] = 10;
                $data['shuffleanswers'] = 1;

                $data['area_checkboxes'] = array(
                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => false,
                        'generalfeedbackimmediately' => false,
                        'rightanswerimmediately' => false,
                        'overallfeedbackimmediately' => false,

                        'attemptopen' => false,
                        'correctnessopen' => false,
                        'marksopen' => true,
                        'specificfeedbackopen' => false,
                        'generalfeedbackopen' => false,
                        'rightansweropen' => false,
                        'overallfeedbackopen' => false,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                );
                break;
            case 'biology':
                break;
        }

        return $data;
    }

    public function global_name_type3() {
        switch ($this->instancename) {
            case 'physics':
                $name = get_string('name_physics_3', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_physics_3', 'local_quizpreset');
                break;
            case 'chemistry':
                $name = get_string('name_chemistry_3', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_chemistry_3', 'local_quizpreset');
                break;
            case 'biology':
                $name = get_string('name_biology_3', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_biology_3', 'local_quizpreset');
                break;
        }

        return array(
                'name' => $name,
                'intro' => $introtext,
                'introformat' => 1,
                'introeditor' => array
                (
                        'text' => $introtext,
                        'format' => 1,
                ),
        );
    }

    public function values_type3() {
        $data = array(
                // Timing.
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,

                // Modstandardgrade.
                'gradecat' => 0,
                'gradepass' => 55.00,
                'attempts' => 0,
                'grademethod' => 1,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'adaptivenopenalty',
                'canredoquestions' => 1,
                'attemptonlast' => 0,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => false,
                        'rightanswerduring' => false,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => false,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => false,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => false,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        switch ($this->instancename) {
            case 'physics':
                break;
            case 'chemistry':
                $data = $this->chemistry_activity_without_score();

                $data['attempts'] = 0;

                $data['area_checkboxes'] = array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => true,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => true,
                        'rightanswerduring' => true,
                        'overallfeedbackduring' => true,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => true,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                );
                $data['preferredbehaviour'] = 'deferredfeedback';
                $data['questionsperpage'] = 10;
                $data['shuffleanswers'] = 1;
                $data['attemptonlast'] = 1;

                break;
            case 'biology':
                break;
        }

        return $data;
    }

    public function global_name_type4() {
        switch ($this->instancename) {
            case 'physics':
                $name = get_string('name_physics_4', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_physics_4', 'local_quizpreset');
                break;
            case 'chemistry':
                $name = get_string('name_chemistry_4', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_chemistry_4', 'local_quizpreset');
                break;
            case 'biology':
                $name = get_string('name_biology_4', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_biology_4', 'local_quizpreset');
                break;
        }

        return array(
                'name' => $name,
                'intro' => $introtext,
                'introformat' => 1,
                'introeditor' => array
                (
                        'text' => $introtext,
                        'format' => 1,
                ),
        );
    }

    public function values_type4() {
        $data = array(
                // Timing.
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,

                // Modstandardgrade.
                'gradecat' => 0,
                'gradepass' => 55.00,
                'attempts' => 0,
                'grademethod' => 3,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'adaptive',
                'canredoquestions' => 1,
                'attemptonlast' => 1,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => false,
                        'rightanswerduring' => false,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => true,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        switch ($this->instancename) {
            case 'physics':
                break;
            case 'chemistry':
                $data = $this->chemistry_activity_with_score();

                $data['attempts'] = 3;
                $data['grademethod'] = 1;
                $data['preferredbehaviour'] = 'deferredfeedback';
                $data['questionsperpage'] = 10;
                $data['shuffleanswers'] = 1;

                $data['area_checkboxes'] = array(
                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => false,
                        'rightanswerimmediately' => false,
                        'overallfeedbackimmediately' => false,

                        'attemptopen' => false,
                        'correctnessopen' => false,
                        'marksopen' => true,
                        'specificfeedbackopen' => false,
                        'generalfeedbackopen' => false,
                        'rightansweropen' => false,
                        'overallfeedbackopen' => false,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                );

                break;
            case 'biology':
                break;
        }

        return $data;
    }

    public function global_name_type6() {
        switch ($this->instancename) {
            case 'physics':
                $name = get_string('name_physics_1', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_physics_1', 'local_quizpreset');
                break;
            case 'chemistry':
                $name = get_string('name_chemistry_6', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_chemistry_6', 'local_quizpreset');
                break;
            case 'biology':
                $name = get_string('name_biology_1', 'local_quizpreset') . ' ' . date('d-m-Y');
                $introtext = get_string('intro_biology_1', 'local_quizpreset');
                break;
        }

        return array(
                'name' => $name,
                'intro' => $introtext,
                'introformat' => 1,
                'introeditor' => array
                (
                        'text' => $introtext,
                        'format' => 1,
                ),
        );
    }

    public function values_type6() {
        $data = array(
                // Modstandardgrade.
                'gradecat' => 17,
                'gradepass' => 0,
                'attempts' => 0,
                'grademethod' => 1,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'interactive',
                'canredoquestions' => 1,
                'attemptonlast' => 1,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => true,
                        'rightanswerduring' => true,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => false,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => false,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => false,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        switch ($this->instancename) {
            case 'physics':
                break;
            case 'chemistry':
                $data = $this->chemistry_activity_with_score();

                $data['attempts'] = 1;
                $data['preferredbehaviour'] = 'deferredfeedback';
                $data['questionsperpage'] = 10;
                $data['shuffleanswers'] = 1;

                $data['area_checkboxes'] = array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => true,
                        'rightanswerduring' => true,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => false,
                        'rightanswerimmediately' => false,
                        'overallfeedbackimmediately' => false,

                        'attemptopen' => false,
                        'correctnessopen' => false,
                        'marksopen' => true,
                        'specificfeedbackopen' => false,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => false,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                );
                break;
            case 'biology':
                break;
        }

        return $data;
    }

    public function chemistry_activity_with_score() {
        $data = array(
                // Timing.
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,

                // Modstandardgrade.
                'gradecat' => 0,
                'gradepass' => 55.00,
                'attempts' => 0,
                'grademethod' => 1,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'adaptive',
                'canredoquestions' => 1,
                'attemptonlast' => 1,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => false,
                        'rightanswerduring' => false,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => true,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => true,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => true,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        return $data;
    }

    public function chemistry_activity_without_score() {
        global $DB, $COURSE;

        // We have only one "activity with no grades" category in the course. PTL-1560.
        $nogradescat = $DB->get_record('grade_categories',
                ['courseid' => $COURSE->id, 'fullname' => get_string('activitieswithoutgrade', 'local_petel')]);

        $data = array(
                // Timing.
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,

                // Modstandardgrade.
                'gradecat' => isset($nogradescat->id) ? $nogradescat->id : 0,
                'gradepass' => 55.00,
                'attempts' => 0,
                'grademethod' => 1,

                // Interactionhdr.
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'adaptivenopenalty',
                'canredoquestions' => 1,
                'attemptonlast' => 0,

                // Reviewoptionshdr.
                'area_checkboxes' => array(
                        'attemptduring' => true,
                        'correctnessduring' => true,
                        'marksduring' => false,
                        'specificfeedbackduring' => true,
                        'generalfeedbackduring' => false,
                        'rightanswerduring' => false,
                        'overallfeedbackduring' => false,

                        'attemptimmediately' => true,
                        'correctnessimmediately' => true,
                        'marksimmediately' => false,
                        'specificfeedbackimmediately' => true,
                        'generalfeedbackimmediately' => true,
                        'rightanswerimmediately' => true,
                        'overallfeedbackimmediately' => true,

                        'attemptopen' => true,
                        'correctnessopen' => true,
                        'marksopen' => false,
                        'specificfeedbackopen' => true,
                        'generalfeedbackopen' => true,
                        'rightansweropen' => true,
                        'overallfeedbackopen' => true,

                        'attemptclosed' => true,
                        'correctnessclosed' => true,
                        'marksclosed' => false,
                        'specificfeedbackclosed' => true,
                        'generalfeedbackclosed' => true,
                        'rightanswerclosed' => true,
                        'overallfeedbackclosed' => true,
                ),
        );

        return $data;
    }

    public function default_values() {
        return array(
                'timeopen' => 0,
                'timelimit' => 0,
                'overduehandling' => 'autosubmit',
                'graceperiod' => 0,
                'gradecat' => 17,
                'gradepass' => 0,
                'attempts' => 0,
                'grademethod' => 1,
                'questionsperpage' => 0,
                'navmethod' => 'free',
                'shuffleanswers' => 1,
                'preferredbehaviour' => 'deferredfeedback',
                'canredoquestions' => 0,
                'attemptonlast' => 0,
                'showuserpicture' => 0,
                'decimalpoints' => 2,
                'questiondecimalpoints' => -1,
                'showblocks' => 0,
                'quizpassword' => '',
                'subnet' => '',
                'delay1' => 0,
                'delay2' => 0,
                'browsersecurity' => '-',
                'allowofflineattempts' => 0,
                'boundary_repeats' => 0,
                'visibleoncoursepage' => 1,
                'cmidnumber' => '',
                'groupmode' => 0,
                'groupingid' => 0,
                'availabilityconditionsjson' => '',
                'completionusegrade' => '',
                'completionexpected' => 0,
        );
    }

    public function merge_with_default($datatype) {
        $default = $this->default_values();

        foreach ($datatype as $name => $item) {
            $default[$name] = $item;
        }

        return $default;
    }

    public function get_details() {
        global $DB;

        // Prepare url.
        $viewall = ($this->viewall == 1) ? 0 : 1;

        $param = $this->urlparams;
        switch ($this->pagestate) {
            case 'view':
                $param['viewall'] = $viewall;
                $obj = new \moodle_url('/mod/quiz/view.php', $param);
                $url = $obj->out(false);
                break;
            case 'update':
                $param['viewall'] = $viewall;
                $obj = new \moodle_url('/course/modedit.php', $param);
                $url = $obj->out(false);
                break;

            case 'new':
                $param['viewall'] = $viewall;
                $obj = new \moodle_url('/course/modedit.php', $param);
                $url = $obj->out(false);
                break;
        }

        // If POST.
        if (empty($this->urlparams)) {
            $url = false;
        }

        // Enable/Disable grades.
        $enablegardes = false;
        $userexposure = false;
        if ($this->pagestate == 'update' && $this->type == QUIZ_TYPE_1) {

            $enablegardes = true;
            $cm = $DB->get_record('course_modules', array('id' => $this->cmid));

            if (!empty($cm)) {
                $quiz = $DB->get_record('quiz', array('id' => $cm->instance));

                if ($quiz->userexposure == 1) {
                    $userexposure = true;
                }
            }
        }

        // Button MORE/LESS must be just in setting mode.
        if ($this->pagestate == 'view') {
            $cm = $DB->get_record('course_modules', array('id' => $this->cmid));
            if (!empty($cm)) {
                $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
                if ($quiz->timelimit > 0) {
                    $url = false;
                }
            }
        }

        return array(
                'cmid' => $this->cmid,
                'type' => $this->type,
                'pagestate' => $this->pagestate,
                'instancename' => $this->instancename,
                'ifchangestate' => $this->ifchangestate,
                'viewall' => $this->viewall,
                'url_viewall' => $url,
                'enablegardes' => $enablegardes,
                'userexposure' => $userexposure,
        );
    }

    public function get_selector($isstudent = 0) {
        global $PAGE, $DB, $CFG;

        $items = array();
        $activedescribe = '';

        foreach ($this->settypes as $num) {
            $tmp = array();
            $tmp['typeId'] = $num;

            // Prepare url.
            $param = $this->urlparams;
            switch ($this->pagestate) {
                case 'view':
                    $url = new \moodle_url('/course/modedit.php', array(
                            'update' => $this->cmid,
                            'return' => 1,
                            'defaulttype' => $num,
                            'viewall' => $this->viewall
                    ));
                    break;
                case 'update':
                    $param['update'] = $this->cmid;
                    $param['return'] = 1;
                    $param['defaulttype'] = $num;
                    $param['viewall'] = $this->viewall;
                    $url = new \moodle_url('/course/modedit.php', $param);
                    break;

                case 'new':
                    $param['defaulttype'] = $num;
                    $param['viewall'] = $this->viewall;
                    $url = new \moodle_url('/course/modedit.php', $param);
                    break;
            }

            $tmp['typeUrl'] = $url->out(false);

            // If POST return error.
            if (empty($this->urlparams)) {
                $tmp['typeUrl'] = 'javascript:void(0)';
            }

            switch ($this->instancename) {
                case 'physics':
                    $tmp['typeDescribe'] = get_string('describe_physics_' . $num, 'local_quizpreset');
                    $tmp['typeName'] = get_string('name_physics_' . $num, 'local_quizpreset');
                    break;
                case 'chemistry':
                    $tmp['typeDescribe'] = get_string('describe_chemistry_' . $num, 'local_quizpreset');
                    $tmp['typeName'] = get_string('name_chemistry_' . $num, 'local_quizpreset');
                    break;
                case 'biology':
                    $tmp['typeDescribe'] = get_string('describe_biology_' . $num, 'local_quizpreset');
                    $tmp['typeName'] = get_string('name_biology_' . $num, 'local_quizpreset');
                    break;
            }

            // Prepare active tab.
            if ($this->type == $num) {
                $tmp['active'] = true;
                $activedescribe = $tmp['typeDescribe'];
            } else {
                $tmp['active'] = false;
            }

            $items[] = $tmp;
        }

        if ($isstudent == 1) {
            $items = array();
        }

        return array('items' => $items, 'activeDescribe' => $activedescribe);
    }

    public function get_expanded() {
        return ($this->viewall != 1) ? $this->expanded : array();
    }

    public function get_values() {
        return $this->values;
    }

    public function get_global_name() {
        return $this->globalname;
    }

}
