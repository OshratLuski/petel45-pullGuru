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

define('QUIZ_TYPE_1', 1);
define('QUIZ_TYPE_2', 2);
define('QUIZ_TYPE_3', 3);
define('QUIZ_TYPE_4', 4);
define('QUIZ_TYPE_5', 5);
define('QUIZ_TYPE_6', 6);

define('QUIZ_TYPE_MYPRESET_START', 10);

/**
 * Admin settings class for the quiz browser security option.
 *
 * Just so we can lazy-load the choices.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_types {

    private $type;
    private $expanded;
    private $values;
    private $globalname;
    private $settypes;

    private $cmid;
    private $pagestate;
    private $viewall;
    private $viewallbuttonenable;
    private $ifchangestate;
    private $urlparams;
    private $mypresets;
    private $notmypresets = [];
    private $typesmypresets = [];

    public function __construct($cmid, $defaulttype, $viewall, $pagestate, $urlparams) {
        global $DB, $USER;

        $config = get_config('local_quizpreset');

        $this->cmid = $cmid;
        $this->pagestate = $pagestate;
        $this->urlparams = json_decode($urlparams, true);
        $this->settypes = range(1, $config->numberoftypes);

        // My presets.
        $mypresets = [];

        if ($config->mypresetenable == 1) {
            if ($res = $DB->get_records('local_quizpreset_mystates', [])) {
                foreach ($res as $obj) {
                    $counter = QUIZ_TYPE_MYPRESET_START + $obj->id;
                    if ($obj->userid == $USER->id) {
                        $mypresets[$counter] = $obj;
                    }
                }
            }
        }

        $this->mypresets = $mypresets;

        foreach ($this->mypresets as $key => $val) {
            $this->settypes[] = $key;
            $this->typesmypresets[] = $key;
        }

        // If POST.
        if (empty($this->urlparams)) {

            if ($this->pagestate == 'update') {
                $sql = "SELECT * FROM {local_quizpreset} WHERE cmid = ? AND status = 0 ORDER BY id DESC LIMIT 1";
                if (!$qp = $DB->get_record_sql($sql, array($cmid))){
                    $qp = new \StdClass();
                    $qp->viewall = 1;
                    $qp->type = null;
                }
            }

            if ($this->pagestate == 'new') {
                $sql = "SELECT * FROM {local_quizpreset} WHERE userid = ? AND state = 'new' ORDER BY id DESC LIMIT 1";
                if (!$qp = $DB->get_record_sql($sql, array($USER->id))) {
                    $qp = new \StdClass();
                    $qp->viewall = 1;
                    $qp->type = null;
                }
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
            $this->type = (!empty($savedvaluetype)) ? $savedvaluetype : $config->defaulttype;
            $this->ifchangestate = false;
        }

        // Type created not by user.
        if (!in_array($this->type, $this->settypes)) {
            if ($res = $DB->get_record('local_quizpreset_mystates', ['id' => ($this->type - QUIZ_TYPE_MYPRESET_START)])) {
                $this->mypresets[$this->type] = $res;
            }

            $this->settypes[] = $this->type;
            $this->typesmypresets[] = $this->type;
            $this->notmypresets[] = $this->type;
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

        // My presets.
        if (in_array($this->type, $this->typesmypresets)) {
            $obj = $this->mypresets[$this->type];

            $this->expanded = [
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
            ];

            $settigs = json_decode($obj->settings);
            $this->values = (array) $settigs->data;

            $typename = !empty($obj->typename) ? $obj->typename : get_string('defaulttypename', 'local_quizpreset');

            $this->globalname = [
                    'name' => $typename . ' ' . date('d-m-Y'),
                    'intro' => '',
                    'introformat' => 1,
                    'introeditor' => [
                            'text' => '',
                            'format' => 1,
                    ],
            ];
        } else {
            $config = get_config('local_quizpreset');

            $variable = 'quizpreset_' . $this->type;
            $preset = json_decode($config->$variable);

            $this->expanded = (array) $preset->sections;
            $this->values = (array) $preset->fields;

            $variablename = 'quiztypename_' . $this->type;
            $this->globalname = array(
                    'name' => $config->$variablename . ' ' . date('d-m-Y'),
                    'intro' => '',
                    'introformat' => 1,
                    'introeditor' => array
                    (
                            'text' => '',
                            'format' => 1,
                    ),
            );
        }

    }

    public function get_details($isstudent = 0) {
        global $DB;

        $config = get_config('local_quizpreset');
        $variable = 'quizpreset_' . $this->type;
        $preset = json_decode($config->$variable);

        // If empty default state.
        if (empty($preset)) {
            $arr = [
                    'functionality' => ['view_description' => true]
            ];

            $preset = json_decode(json_encode($arr));
        }

        // If adjustments disabled.
        if (!$config->enableadjustments) {
            $this->viewall = 1;
            $this->viewallbuttonenable = 0;
        } else {
            $this->viewallbuttonenable = 1;
        }

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
        $viewdescription = false;

        if ($this->pagestate == 'update' && $preset->functionality->exposure_grades) {

            $enablegardes = true;
            $cm = $DB->get_record('course_modules', array('id' => $this->cmid));

            if (!empty($cm)) {
                $quiz = $DB->get_record('quiz', array('id' => $cm->instance));

                if ($quiz->timeclose > 0) {
                    $userexposure = true;
                }
            }
        }

        if ($this->pagestate != 'view' && $preset->functionality->view_description) {
            $viewdescription = true;
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

        $config = get_config('local_quizpreset');
        $mypresetenable =
                $isstudent != 1 && in_array($this->pagestate, ['update', 'new']) && $config->mypresetenable == 1 ? true : false;

        return array(
                'cmid' => $this->cmid,
                'type' => $this->type,
                'pagestate' => $this->pagestate,
                'instancename' => '',
                'ifchangestate' => $this->ifchangestate,
                'viewall' => $this->viewall,
                'viewall_button_enable' => $this->viewallbuttonenable,
                'url_viewall' => $url,
                'enablegardes' => $enablegardes,
                'userexposure' => $userexposure,
                'viewdescription' => $viewdescription,
                'my_preset_enable' => $mypresetenable,
        );
    }

    public function get_selector($isstudent = 0) {
        global $DB, $USER;

        $items = array();
        $activedescribe = '';

        $config = get_config('local_quizpreset');

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

            // If POST return error or activity in research mode.
            if (class_exists('\community_oer\main_oer')) {
                if (empty($this->urlparams) || \community_oer\main_oer::if_activity_in_research_mode($this->cmid)) {
                    $tmp['typeUrl'] = 'javascript:void(0)';
                }
            }

            if (!in_array($num, $this->typesmypresets)) {
                $variablename = 'quiztypename_' . $num;
                $tmp['typeName'] = $config->$variablename;

                $variabledescription = $isstudent == 1 ? 'quiztypedescriptionstudent_' . $num : 'quiztypedescription_' . $num;

                // PTL_7673 add format_text for multilang filter.
                $tmp['typeDescribe'] = format_text($config->$variabledescription, FORMAT_HTML);
                $tmp['typeDBId'] = 0;
            }

            // My presets.
            if (in_array($num, $this->typesmypresets)) {

                $tmp['typeDBId'] = $this->mypresets[$num]->id;

                // Get my preset actually.
                if ($res = $DB->get_record('local_quizpreset_mystates', ['userid' => $USER->id])) {
                    $tmp['typeDBId'] = $res->id;
                }

                if (in_array($num, $this->notmypresets)) {
                    $tmp['typeName'] = get_string('other');

                    $settigs = json_decode($this->mypresets[$num]->settings);

                    $tmp['typeDescribe'] = $isstudent == 1 ? $settigs->student_description : $settigs->teacher_description;
                } else {
                    $tmp['typeName'] = !empty($this->mypresets[$num]->typename) ? $this->mypresets[$num]->typename :
                            get_string('defaulttypename', 'local_quizpreset');

                    $settigs = json_decode($this->mypresets[$num]->settings);

                    $tmp['typeDescribe'] = $isstudent == 1 ? $settigs->student_description : $settigs->teacher_description;
                }
            }

            // Prepare active tab.
            if ($this->type == $num) {
                $tmp['active'] = true;
                $activedescribe = $tmp['typeDescribe'];
                $activedbid = $tmp['typeDBId'];
            } else {
                $tmp['active'] = false;
            }

            if (in_array($num, $this->notmypresets)) {
                $tmp['notclickable'] = true;
            }

            $items[] = $tmp;
        }

        if ($isstudent == 1) {
            $items = array();
        }

        if (!empty($this->cmid) && is_number($this->cmid)) {
            $cmcontext = context_module::instance($this->cmid);
        } else {
            $cmcontext = context_system::instance();
        }

        $result = [
                'items' => $items,
                'activeDescribe' => $activedescribe,
                'activeDbId' => $activedbid,
                'cmcontextid' => $cmcontext->id
        ];

        $result['activeDescribeEnable'] = !empty($activedescribe) ? true : false;

        return $result;
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
