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
 * Renderers for outputting parts of the question engine.
 *
 * @package    theme_petel
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel\output;

use core_tag_tag;
use core_useragent;
use core\output\html_writer;
use moodle_url;
use mod_quiz\output\navigation_panel_base;
use mod_quiz\quiz_attempt;
use confirm_action;
use single_button;
use stdClass;
use question_state_todo;
use mod_quiz\output\navigation_question_button;
use renderable;
use mod_quiz\output\view_page;
use mod_quiz\question\display_options;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Course renderer class.
 *
 * @package    theme_petel
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quiz_renderer extends \mod_quiz\output\renderer {

    // variables left and variables right
    public $tagnameright = array('splitted-right',  'מפוצל-ימינה');
    public $tagnameleft = array('מפוצל', 'splitted', 'splitted-left', 'מפוצל-שמאלה');
    public $firstsplittedtag = null;

    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
        global $PAGE, $OUTPUT, $DB;

        $output = '';

        $splittedquestions = static::is_quiz_have_splitted_question($attemptobj->get_quizobj()->get_quiz()->id, $attemptobj->get_cmid());
        $ismobile          = core_useragent::get_device_type() == core_useragent::DEVICETYPE_MOBILE ? 1 : 0;

        $splittedmode = $splittedquestions && !$ismobile;

        //Course name
        $coursename = html_writer::link(new moodle_url('/course/view.php', array('id' => $attemptobj->get_quizobj()->get_course()->id)), $attemptobj->get_quizobj()->get_course()->shortname);

        //Quiz name
        $quizname = html_writer::link(new moodle_url('/mod/quiz/view.php', array('q' => $attemptobj->get_quizobj()->get_quiz()->id)), $attemptobj->get_quizobj()->get_quiz()->name);

        //Quiz type
        // $quiztype = self::get_quiz_type($attemptobj->get_quizobj()->get_cm()->id);

        //TODO: put it in correct place remove hardcoded image.
        //Heading image block.
        $output .= html_writer::start_tag('div', array('class' => ' container-fluid'));
        $output .= html_writer::start_tag('div', array('class' => 'col-12 quizheading-block p-0'));

        //$output .= $OUTPUT->heading($coursename, 1, 'coursename mb-0');
        // PTL-9578.
        // $output .= $OUTPUT->heading($quizname, 1, 'quizname mb-0');

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        // Navbar.
        // PTL-9578.
        // $output .= $OUTPUT->navbar_petel();

        // Start the form.
        $output .= html_writer::start_tag('form',
            array('action' => new moodle_url($attemptobj->processattempt_url(),
                array('cmid' => $attemptobj->get_cmid())), 'method' => 'post',
                'enctype'      => 'multipart/form-data', 'accept-charset'         => 'utf-8',
                'id'           => 'responseform', 'class'                         => 'container-fluid'));
        $output .= html_writer::start_tag('div');

        $sections = array_values($DB->get_records('quiz_sections',
        array('quizid' => $attemptobj->get_quizobj()->get_quiz()->id), 'firstslot'));

        // Print all the questions.
        if ($splittedmode) {
            $firstcol = '';
            $secondcol = '';

            foreach ($slots as $slot) {
                $splq = static::is_splitted_question($attemptobj->get_question_attempt($slot)->get_question()->id, $attemptobj->get_cmid());
                if (!$splq) {
                    $secondcol .= $attemptobj->render_question($slot, false, $this,
                        $attemptobj->attempt_url($slot, $page), $this);
                } else {
                    $firstcol .= $attemptobj->render_question($slot, false, $this,
                        $attemptobj->attempt_url($slot, $page), $this);
                }
            }

            if(strlen(trim($firstcol)) != 0 && strlen(trim($secondcol)) != 0){

                $firstcol = html_writer::start_tag('div', ['class' => 'pr-1', 'id' => 'firstcol']) .
                            $firstcol . html_writer::end_tag('div');

                $secondcol = html_writer::start_tag('div', ['class' => 'pl-1', 'id' => 'secondcol']) .
                            $secondcol . html_writer::end_tag('div');

                $output .= html_writer::start_tag('div', ['class' => 'split', 'id' => 'splitpanel']);

                $direction = $this->get_splited_question_type($this->firstsplittedtag);

                if($direction == 'rtl'){
                    $output .= $secondcol . $firstcol;
                } else {
                    $output .= $firstcol . $secondcol;
                }

                $output .= html_writer::end_tag('div');

                $PAGE->requires->js_call_amd('theme_petel/splitter');

            }else{
                $output .= $firstcol . $secondcol;
            }

        } else {
            foreach ($slots as $slot) {
                $output .= $attemptobj->render_question($slot, false, $this,
                    $attemptobj->attempt_url($slot, $page), $this);
            }
        }

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $output .= html_writer::start_tag('div', array('class' => 'w-100'));
        $output .= html_writer::end_tag('div');
        $output .= $this->attempt_navigation_buttons($page, $attemptobj->is_last_page($page), $navmethod, $attemptobj->get_num_pages());

        // Some hidden fields to trach what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
            'value'                                                 => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
            'value'                                                 => $page, 'id'      => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
            'value'                                                 => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
            'value'                                                 => '0', 'id'        => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
            'value'                                                 => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
            'value'                                                 => '', 'id'         => 'scrollpos'));

        // Add a hidden field with questionids. Do this at the end of the form, so
        // if you navigate before the form has finished loading, it does not wipe all
        // the student's answers.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
            'value'                                                 => implode(',', $attemptobj->get_active_slots($page))));

        // Finish the form.
        $output .= html_writer::end_tag('div');


        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        return $output;
    }

    public function get_quiz_type($cmid) {
        global $CFG, $DB;

        $type = 0;

        $sql = "SELECT type
                FROM {local_quizpreset}
                WHERE cmid = ? AND status = 1
                ORDER BY id DESC";

        if($local_quizpreset = $DB->get_record_sql($sql, [$cmid])) {
            $type = $local_quizpreset->type;
        } else {
            return null;
        }

        if (isset($CFG->instancename) && !empty($CFG->instancename)) {
            if (in_array($CFG->instancename, array('physics', 'chemistry', 'biology'))) {
                $instancename = $CFG->instancename;
            } else {
                $instancename = 'physics';
            }
        } else {
            $instancename = 'physics';
        }

        $result = array();

        switch ($instancename) {
            case 'physics':
                $result['typeDescribe'] = get_string('describe_physics_' . $type, 'local_quizpreset');
                $result['typeName'] = get_string('name_physics_' . $type, 'local_quizpreset');
                break;
            case 'chemistry':
                $result['typeDescribe'] = get_string('describe_chemistry_' . $type, 'local_quizpreset');
                $result['typeName'] = get_string('name_chemistry_' . $type, 'local_quizpreset');
                break;
            case 'biology':
                $result['typeDescribe'] = get_string('describe_biology_' . $type, 'local_quizpreset');
                $result['typeName'] = get_string('name_biology_' . $type, 'local_quizpreset');
                break;
        }

        return $result['typeName'];
    }

    public function get_splited_question_type($firstsplittedtag) {
        $dir = null;
        if (in_array($firstsplittedtag, $this->tagnameleft)) {
            $dir = 'ltr';
        } else if(in_array($firstsplittedtag, $this->tagnameright)){
            $dir = 'rtl';
        }
        return $dir;
    }

    public function is_quiz_have_splitted_question($quizid, $cmid) {
        global $DB;

        $response = false;

        $context = \context_module::instance($cmid);

        $params           = [];
        $params['quizid'] = $quizid;

        $sql = "SELECT q.id, q.id AS questionid
                FROM {quiz_slots} slot
                JOIN {question_references} qre ON qre.itemid = slot.id
                JOIN {question_bank_entries} qbe ON qbe.id = qre.questionbankentryid
                JOIN {question_versions} qve ON qve.questionbankentryid = qbe.id
                JOIN {question} q ON q.id = qve.questionid
                WHERE slot.quizid = ?";

        if ($questions = $DB->get_records_sql($sql, $params)) {
            foreach ($questions as $key => $question) {

                // Check for tag splitted.
                $tags = [];
                foreach(core_tag_tag::get_item_tags('core_question', 'question', $question->questionid) as $tag){
                    if($tag->taginstancecontextid == $context->id){
                        $tags[] = $tag->name;
                    }
                }

                $tagnames = array_merge($this->tagnameright, $this->tagnameleft);

                foreach($tagnames as $tagname){
                    if (in_array($tagname, $tags)) {
                        $response = true;
                        break;
                    }
                }
            }
        }

        return $response;
    }

    public function is_splitted_question($qid, $cmid) {
        global $DB;

        $response = false;

        $context = \context_module::instance($cmid);

        $tags = [];
        foreach(core_tag_tag::get_item_tags('core_question', 'question', $qid) as $tag){
            if($tag->taginstancecontextid == $context->id){
                $tags[] = $tag->name;
            }
        }

        $tagnames = array_merge($this->tagnameright, $this->tagnameleft);

        foreach($tagnames as $key => $tagname){
            if (in_array($tagname, $tags)) {

                if(is_null($this->firstsplittedtag)) {
                    $this->firstsplittedtag = $tagname;
                }

                $response = true;
                break;
            }
        }

        return $response;
    }

    public function summary_page_controls($attemptobj) {
        $output = '';

        $output .= $this->countdown_timer($attemptobj, time());

        $output .= html_writer::start_tag('div', ['class'=>'container row ']);
        // Return to place button.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button = new single_button(
                new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                get_string('returnattempt',
                    'quiz'
                )
            );
            $output .= $this->container($this->container(
                $this->render($button),
                'controls'
            ), 'submitbtns mdl-align col-md-6');
        }

        // Finish attempt button.
        $options = array(
            'attempt'       => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup'        => 0,
            'slots'         => '',
            'cmid'          => $attemptobj->get_cmid(),
            'sesskey'       => sesskey(),
        );

        $button = new single_button(
            new moodle_url($attemptobj->processattempt_url(), $options),
            get_string('submitallandfinish', 'quiz')
        );
        $button->id      = 'responseform';
        $button->primary = true;
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(
                get_string('confirmclose', 'quiz'),
                null,
                get_string('submitallandfinish', 'quiz')
            ));
        }

        $duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));
        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }

        $output .= $this->container($message . $this->container(
            $this->render($button),
            'controls'
        ), 'submitbtns mdl-align col-md-6');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    public function navigation_panel(navigation_panel_base $panel) {
        global $OUTPUT, $PAGE;
        $output      = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag(
                'div',
                $this->render($userpicture) . $fullname,
                array('id' => 'user-picture', 'class' => 'clearfix')
            );
        }
        $output .= $panel->render_before_button_bits($this);

        $data = new stdClass;
        $output .= $OUTPUT->render_from_template('theme_petel/quiz_nav_panel_title', $data);

        // #6823 Hack. Use Reflection to access the protected property
        $reflection = new \ReflectionClass($panel);
        $property = $reflection->getProperty('attemptobj');
        $property->setAccessible(true);
        $attemptobj = $property->getValue($panel);
        $output .= $this->countdown_timer($attemptobj, time());

        $bcc = $panel->get_button_container_class();

        $output .= html_writer::tag(
            'div',
            $panel->render_end_bits($this),
            array('class' => 'othernav')
        );

        $output .= html_writer::start_tag('div', array('class' => "qn_buttons d-flex flex-wrap $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        $this->page->requires->js_init_call(
            'M.mod_quiz.nav.init',
            null,
            false,
            quiz_get_js_module()
        );

        return $output;
    }

    public function get_number_completed_questions($attemptobj) {
        $result = 0;

        $questions = $attemptobj->get_active_slots();
        foreach ($questions as $key => $question) {
            if (!($attemptobj->get_question_state($question) instanceof question_state_todo) && $attemptobj->is_real_question($question)) {
                $result++;
            }
        }

        return $result;
    }

    public function countdown_timer(quiz_attempt $attemptobj, $timenow) {
        global $OUTPUT, $USER, $PAGE;

        $output = '';

        $timeleft = $attemptobj->get_time_left_display($timenow);
        $ispreview       = $attemptobj->is_preview();
        $timerstartvalue = $timeleft;
        if (!$ispreview) {
            // Make sure the timer starts just above zero. If $timeleft was <= 0, then
            // this will just have the effect of causing the quiz to be submitted immediately.
            $timerstartvalue = max($timerstartvalue, 1);
        }

        // Progress.
        $totalquestions = 0;
        foreach ($attemptobj->get_active_slots() as $key => $slot) {
            if ($attemptobj->is_real_question($slot)) {
                $totalquestions++;
            }
        }

        $answered = self::get_number_completed_questions($attemptobj);

        $timelimit = $attemptobj->get_quizobj()->get_quiz()->timelimit;
        $progress = 0;
        if (!$ispreview && $timelimit != 0) {
            $progress = ceil($timeleft / $timelimit * 100) > 0 ? ceil($timeleft / $timelimit * 100) : 0;
        }

        $params                    = [];
        $params['timerstartvalue'] = $timerstartvalue;
        $params['timeleft']        = $timeleft;
        $params['timelimit']       = $timelimit;
        $params['ispreview']       = $ispreview;
        $params['progress']        = $progress;
        $params['attemptid']       = $attemptobj->get_attempt()->id;
        $params['cmid']            = $attemptobj->get_quizobj()->get_cm()->id;
        $params['userid']          = $USER->id;
        $params['answered']        = $answered;
        $params['totalquestions']  = $totalquestions;
        $params['timer_enabled']   = $timeleft !== false && $PAGE->pagetype == 'mod-quiz-attempt';
        $params['active']          = $PAGE->pagetype == 'mod-quiz-attempt';

        if(($PAGE->pagetype == 'mod-quiz-attempt' || $PAGE->pagetype ==  'mod-quiz-review') && $timeleft !== false){
            $output .= $OUTPUT->render_from_template('theme_petel/time_block', $params);
        }

        if($PAGE->pagetype == 'mod-quiz-attempt' || $PAGE->pagetype ==  'mod-quiz-review' || $PAGE->pagetype ==  'mod-quiz-summary'  || $PAGE->pagetype ==  'mod-quiz-report') {
            $this->page->requires->js_call_amd('theme_petel/quiz_timer', 'init', $params);
        }

        return $output;
    }

    protected function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free', $totalpages = null) {
        $output = '';

        $output .= html_writer::start_tag('div', array('class' => 'wrapper d-flex align-items-center'));
        $output .= html_writer::start_tag('div', array('class' => 'quiz_pagination d-flex align-items-center submitbtns'));

        // Prev.
        $prev = '';
        if ($page > 0 && $navmethod == 'free') {
            $previnput = html_writer::empty_tag(
                'input',
                array(
                    'type'  => 'submit',
                    'name'  => 'previous',
                    'value' => 'previous',
                    'class' => 'btn fa-angle-left fas mod_quiz-prev-nav',
                )
            );
            $prev .= html_writer::div($previnput, 'arr arr-left');
        }

        // Next.
        $next = '';
        $finish = '';
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
            $nextinput = html_writer::empty_tag('input', array(
                'type'  => 'submit', 'name'    => 'next',
                'value' => $nextlabel, 'class' => 'mod_quiz-next-nav btn btn-primary',
            ));
            $finish .= html_writer::div($nextinput, 'arr arr-right');
        } else {
            $nextinput = html_writer::empty_tag(
                'input',
                array(
                    'type'  => 'submit',
                    'name'  => 'next',
                    'value' => 'next',
                    'class' => 'btn fas fa-angle-right fas mod_quiz-next-nav',
                )
            );
            $next .= html_writer::div($nextinput, 'arr arr-right');
        }

        // Virtual button.
        $virtual = html_writer::empty_tag(
                'input',
                array(
                        'type'  => 'submit',
                        'name'  => '',
                        'value' => '',
                        'class' => 'mod_quiz-virtual-nav',
                )
        );
        $virtualbutton = html_writer::div($virtual, 'd-none');

        // Page numbers.
        $pagesnum = '';
        // Disable if nav method not "free" (sequental...).
        if ($navmethod == 'free') {
            $pagesnum .= static::theme_petel_pbar($totalpages, $page + 1);
        }
        $output .= $prev . $pagesnum . $next;
        $output .= $virtualbutton;

        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', array('class' => 'quiz_pagination_finish d-flex align-items-center'));
        $output .= $finish;
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');

        return $output;
    }

    public function theme_petel_pbar($totalpages = 1, $page = 1, $numberofmaximumbuttons = 5, $blockid = 'pbar') {
        global $OUTPUT;

        if ($totalpages <= 1) {
            return '';
        }

        $numberofminimumsidebuttons = 1;
        $numberofstartbuttons       = $numberofminimumsidebuttons;

        $startrow    = false;
        $middlelist  = false;
        $middlerow   = false;
        $middle2list = false;
        $endrow      = false;

        $startrowitems    = [];
        $middlelistitems  = [];
        $middlerowitems   = [];
        $middle2listitems = [];
        $endrowitems      = [];

        if ($totalpages <= $numberofmaximumbuttons) {
            foreach (range(1, $totalpages) as $number) {
                $startrowitems[] = static::theme_petel_pbar_item($number, $page);
            }
        } else {
            if ($page <= 3 && ($totalpages - $page) >= 3) {
                foreach (range(1, $page + $numberofminimumsidebuttons) as $number) {
                    $startrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($page + $numberofminimumsidebuttons + 1, $totalpages - $numberofminimumsidebuttons) as $number) {
                    $middle2listitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($totalpages - $numberofminimumsidebuttons + 1, $totalpages) as $number) {
                    $endrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
            }

            if ($page > 3 && ($totalpages - $page) > 3
            ) {
                foreach (range(1, $numberofminimumsidebuttons) as $number) {
                    $startrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($numberofminimumsidebuttons + 1, $page - $numberofminimumsidebuttons - 1) as $number) {
                    $middlelistitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($page - $numberofminimumsidebuttons, $page + $numberofminimumsidebuttons) as $number) {
                    $middlerowitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($page + $numberofminimumsidebuttons + 1, $totalpages - $numberofminimumsidebuttons) as $number) {
                    $middle2listitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($totalpages - $numberofminimumsidebuttons + 1, $totalpages) as $number) {
                    $endrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
            }

            if ($page > 3 && ($totalpages - $page) <= 3
            ) {
                foreach (range(1, $numberofminimumsidebuttons) as $number) {
                    $startrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($numberofminimumsidebuttons + 1, $page - $numberofminimumsidebuttons - 1) as $number) {
                    $middlelistitems[] = static::theme_petel_pbar_item($number, $page);
                }
                foreach (range($page - $numberofminimumsidebuttons, $totalpages) as $number) {
                    $endrowitems[] = static::theme_petel_pbar_item($number, $page);
                }
            }
        }

        $startrow    = $startrowitems ? true : false;
        $middlelist  = $middlelistitems ? true : false;
        $middlerow   = $middlerowitems ? true : false;
        $middle2list = $middle2listitems ? true : false;
        $endrow      = $endrowitems ? true : false;

        $output = '';

        $data = new stdClass();

        $data->startrowitems    = $startrowitems;
        $data->middlelistitems  = $middlelistitems;
        $data->middlerowitems   = $middlerowitems;
        $data->middle2listitems = $middle2listitems;
        $data->endrowitems      = $endrowitems;

        $data->startrow    = $startrow;
        $data->middlelist  = $middlelist;
        $data->middlerow   = $middlerow;
        $data->middle2list = $middle2list;
        $data->endrow      = $endrow;

        $data->blockid = $blockid;

        $output = $OUTPUT->render_from_template("theme_petel/pbar", $data);

        return $output;
    }

    public static function theme_petel_pbar_item($number = 1, $page = 1, $title = null) {

        $item          = new stdClass();
        $item->title   = $title ?? $number;
        $item->page    = $number - 1;
        $item->current = $page == $number;

        return $item;
    }

    protected function render_navigation_question_button(navigation_question_button $button) {

        // Not yet answered state.
        if (in_array($button->stateclass, ['notyetanswered', 'answersaved', 'notchanged'])) {
            $button->stateclass = 'notyetanswered';
        }

        $classes = array('qnbutton', $button->stateclass, $button->navmethod, 'btn', 'btn-secondary');
        $extrainfo = array();

        if ($button->currentpage) {
            $classes[] = 'thispage';
            $extrainfo[] = get_string('onthispage', 'quiz');
        }

        // Flagged.
        if ($button->flagged) {
            $classes[] = 'flagged';
            $flaglabel = get_string('flagged', 'question');
        } else {
            $flaglabel = '';
        }
        $extrainfo[] = html_writer::tag('span', $flaglabel, array('class' => 'flagstate'));

        $a = new stdClass();
        $a->number = $button->number;
        $a->attributes = implode(' ', $extrainfo);
        if (is_numeric($button->number)) {
            $tooltip = html_writer::start_tag('div', array('class' => 'petel-custom-tooltip px-2 py-1 position-absolute ',
                'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));

            switch ($button->stateclass) {
                case 'notyetanswered':
                    $tooltiptext = get_string('tooltipnotyetanswered', 'theme_petel');
                    break;
                case 'correct':
                    $tooltiptext = get_string('tooltipcorrect', 'theme_petel');
                    break;
                case 'incorrect':
                    $tooltiptext = get_string('tooltipincorrect', 'theme_petel');
                    break;
                case 'partiallycorrect':
                    $tooltiptext = get_string('tooltippartiallycorrect', 'theme_petel');
                    break;
                case 'notanswered':
                    $tooltiptext = get_string('tooltipnotanswered', 'theme_petel');
                    break;
                case 'requiresgrading':
                    $tooltiptext = get_string('tooltiprequiresgrading', 'theme_petel');
                    break;
                default:
                    //code block
            }

            $tooltip .= html_writer::tag('div', get_string('question').' '.$button->number, array('class' => 'questionnumber'));
            $tooltip .= html_writer::tag('div', $tooltiptext, array('class' => 'statestring'));

            //$tooltip .= html_writer::tag('div', $button->statestring, array('class' => 'statestring'));

            $tooltip .= html_writer::end_tag('div');
            $tagcontents = html_writer::tag('span', '', array('class' => 'thispageholder')) .
                html_writer::tag('span', '', array('class' => 'trafficlight')) .
                get_string('questionnonav', 'theme_petel', $a);

            $tagattributes = array('class' => implode(' ', $classes), 'id' => $button->id,
                'title' => $tooltiptext, 'data-quiz-page' => $button->page);

            //$tagattributes = array('class' => implode(' ', $classes), 'id' => $button->id,
            //        'title' => $button->statestring, 'data-quiz-page' => $button->page);
        } else {
            $tooltip = html_writer::start_tag('div', array('class' => 'petel-custom-tooltip px-2 py-1 position-absolute ',
                'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));

            $tooltip .= html_writer::tag('div', get_string('tooltipopenquestion', 'theme_petel'), array('class' => 'questionname'));

            //$tooltip .= html_writer::tag('div', $button->questionname, array('class' => 'questionname'));
            //$tooltip .= html_writer::tag('div', $button->statestring, array('class' => 'statestring'));

            $tooltip .= html_writer::end_tag('div');
            $tagcontents = get_string('questionnonavinfo', 'theme_petel', $a);

            $tagattributes = array('class' => implode(' ', $classes) . ' d-flex align-items-center justify-content-center',
                'id' => $button->id, 'title' => get_string('tooltipopenquestion', 'theme_petel'), 'data-quiz-page' => $button->page);

            //$tagattributes = array('class' => implode(' ', $classes) . ' d-flex align-items-center justify-content-center',
            //        'id' => $button->id, 'title' => $button->statestring, 'data-quiz-page' => $button->page);
        }


        if ($button->url) {
            $link = html_writer::start_tag('div', array('class' => 'qnbutton-wrapper position-relative d-flex mr-3 mb-2'));
            $link .= html_writer::link($button->url, $tagcontents, $tagattributes);
            $link .= $tooltip;
            $link .= html_writer::end_tag('div');
            return $link;

        } else {
            return html_writer::tag('span', $tagcontents, $tagattributes);
        }
    }

    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {
        $output = '';
        $output .= $this->header();
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Render a button which allows students to redo a question in the attempt.
     *
     * @param int $slot the number of the slot to generate the button for.
     * @param bool $disabled if true, output the button disabled.
     * @return string HTML fragment.
     */
    public function redo_question_button($slot, $disabled) {
        global $PAGE;
        $attributes = array('type' => 'submit', 'name' => 'redoslot' . $slot, 'id' => 'redoslot' . $slot,
                'value' => get_string('redoquestion', 'quiz'),
                'class' => 'mod_quiz-redo_question_button btn btn-secondary');
        if ($disabled) {
            $attributes['disabled'] = 'disabled';
        } else {
            $PAGE->requires->js_init_call('M.core_question_engine.init_submit_button',
                    array($attributes['id'], $slot));
        }
        return html_writer::div(html_writer::empty_tag('input', $attributes));
    }

    /**
     * Generates the table of summarydata
     *
     * @param quiz_attempt $attemptobj
     * @param display_options $displayoptions
     */
    public function summary_table($attemptobj, $displayoptions) {
        // Prepare the summary table header.
        $data = [];

        $markscolumn = $displayoptions->marks >= display_options::MARK_AND_MAX;
        $data['markscolumn'] = (bool) $markscolumn;

        // Get the summary info for each question.
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            // Add a section headings if we need one here.
            $row['heading'] = $attemptobj->get_heading_before_slot($slot);

            // Don't display information items.
            if (!$attemptobj->is_real_question($slot)) {
                continue;
            }

            // Real question, show it.
            $flag = '';
            if ($attemptobj->is_question_flagged($slot)) {
                // Quiz has custom JS manipulating these image tags - so we can't use the pix_icon method here.
                $flag = html_writer::empty_tag('img', array('src' => $this->image_url('flagged_red', 'theme_petel'),
                        'alt' => get_string('flagged', 'question'), 'class' => 'questionflag icon-post ml-1', 'style' => 'height: 1rem;'));
            }
            if ($attemptobj->can_navigate_to($slot)) {
                $row['question'] = html_writer::link($attemptobj->attempt_url($slot),
                        get_string('question', 'quiz') . ' ' . $attemptobj->get_question_number($slot) . $flag);
                $row['status'] = $attemptobj->get_question_status($slot, $displayoptions->correctness);
            } else {
                $row['question'] = $attemptobj->get_question_number($slot) . $flag;
                $row['status'] = $attemptobj->get_question_status($slot, $displayoptions->correctness);
            }
            if ($markscolumn) {
                $row['markscolumn'] = $attemptobj->get_question_mark($slot);
            }
            $row['class'] =
                    'quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class($slot, $displayoptions->correctness);

            $data['rows'][] = $row;
        }

        return $this->render_from_template('theme_petel/mod_quiz/summary_table', $data);
    }


    /**
     * Outputs the table containing data from summary data array
     *
     * @param array $summarydata contains row data for table
     * @param int $page contains the current page number
     */
    public function review_summary_table($summarydata, $page) {
        $summarydata = $this->filter_review_summary_table($summarydata, $page);
        if (empty($summarydata)) {
            return '';
        }

        foreach ($summarydata as $rowdata) {
            $row = new stdClass;
            if ($rowdata['title'] instanceof renderable) {
                $row->title = $this->render($rowdata['title']);
            } else {
                $row->title = $rowdata['title'];
            }
            if ($rowdata['content'] instanceof renderable) {
                $row->content = $this->render($rowdata['content']);
            } else {
                $row->content = $rowdata['content'];
            }
            $rows[] = $row;
        }

        return $this->render_from_template('theme_petel/mod_quiz/review_summary_table', ['rows' => $rows]);
    }

    /**
     * Render the tertiary navigation for pages during the attempt.
     *
     * @param string|moodle_url $quizviewurl url of the view.php page for this quiz.
     * @return string HTML to output.
     */
    public function during_attempt_tertiary_nav($quizviewurl): string {
        $output = '';
        $output .= html_writer::start_div('container-fluid tertiary-navigation');
            $output .= html_writer::start_div('row');
                $output .= html_writer::start_div('navitem');
                //$output .= html_writer::link($quizviewurl, get_string('back'),
                //        ['class' => 'btn btn-secondary']);
                $output .= html_writer::end_div();
            $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }

    // PTL-9578.
    public function summary_page($attemptobj, $displayoptions) {
        $output = '';
        $output .= $this->header();
        $output .= $this->during_attempt_tertiary_nav($attemptobj->view_url());
        // PTL-9578.
        // $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= $this->heading(get_string('summaryofattempt', 'quiz'), 3);
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    public function view_page_tertiary_nav(view_page $viewobj): string {
        global $USER, $CFG;

        $content = '';

        if ($viewobj->buttontext) {
            $attemptbtn = $this->start_attempt_button($viewobj->buttontext,
                    $viewobj->startattempturl, $viewobj->preflightcheckform,
                    $viewobj->popuprequired, $viewobj->popupoptions);
            $content .= $attemptbtn;

            // PTL-9889. Add start new preview button.
            $unfinished = false;
            if (isset($viewobj->attemptobjs) && isset($viewobj->attemptobjs[0])) {
                if ($unfinishedattempt = quiz_get_user_attempt_unfinished($viewobj->attemptobjs[0]->get_quiz()->id, $USER->id)) {
                    $unfinished = $unfinishedattempt->state == quiz_attempt::IN_PROGRESS ||
                            $unfinishedattempt->state == quiz_attempt::OVERDUE;
                }
            }

            if ($unfinished) {
                $content .= '
                    <form method="post" action="'.$CFG->wwwroot.'/mod/quiz/startattempt.php">
                        <input type="hidden" name="cmid" value="'.$viewobj->attemptobjs[0]->get_cmid().'">
                        <input type="hidden" name="sesskey" value="'.sesskey().'">
                        <input type="hidden" name="forcenew" value="1">
                        <button type="submit" class="btn btn-primary">'.get_string('startnewpreview', 'mod_quiz').'</button>
                    </form>                
                ';
            }
        }

        if ($viewobj->canedit && !$viewobj->quizhasquestions) {
            $content .= html_writer::link($viewobj->editurl, get_string('addquestion', 'quiz'),
                    ['class' => 'btn btn-secondary']);
        }

        if ($content) {
            return html_writer::div(html_writer::div($content, 'row'), 'container-fluid tertiary-navigation');
        } else {
            return '';
        }
    }

    /**
     * Work out, and render, whatever buttons, and surrounding info, should appear
     * at the end of the review page.
     *
     * @param view_page $viewobj the information required to display the view page.
     * @return string HTML to output.
     */
    public function view_page_buttons(view_page $viewobj) {
        global $USER;

        $output = '';

        if (!$viewobj->quizhasquestions) {
            $output .= html_writer::div(
                    $this->notification(get_string('noquestions', 'quiz'), 'warning', false),
                    'text-left mb-3');
        }
        $output .= $this->access_messages($viewobj->preventmessages);

        $viewobj->showbacktocourse = 1;

        if ($viewobj->showbacktocourse) {

            // PTL-11648.
            $viewobj->backtocourseurl = new \moodle_url('/course/view.php', [
                    'id' => $this->page->course->id,
                    'section' => $this->page->cm->sectionnum
            ]);

            $output .= $this->single_button($viewobj->backtocourseurl,
                    get_string('backtocourse', 'quiz'), 'get',
                    array('class' => 'continuebutton'));
        }

        return $output;
    }

    /**
     * Returns the same as {@link quiz_num_attempt_summary()} but wrapped in a link
     * to the quiz reports.
     *
     * @param stdClass $quiz the quiz object. Only $quiz->id is used at the moment.
     * @param stdClass $cm the cm object. Only $cm->course, $cm->groupmode and $cm->groupingid
     * fields are used at the moment.
     * @param context $context the quiz context.
     * @param bool $returnzero if false (default), when no attempts have been made '' is returned
     *      instead of 'Attempts: 0'.
     * @param int $currentgroup if there is a concept of current group where this method is being
     *      called (e.g. a report) pass it in here. Default 0 which means no current group.
     * @return string HTML fragment for the link.
     */
    public function quiz_attempt_summary_link_to_reports($quiz, $cm, $context,
            $returnzero = false, $currentgroup = 0) {
        global $CFG;
        $summary = quiz_num_attempt_summary($quiz, $cm, $returnzero, $currentgroup);
        if (!$summary) {
            return '';
        }

        //$mode = quiz_report_default_report($context);
        $mode = 'advancedoverview';

        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
        $url = new moodle_url('/mod/quiz/report.php', array(
                'id' => $cm->id, 'mode' => $mode));
        return html_writer::link($url, $summary);
    }

    /**
     * Generates data pertaining to quiz results
     *
     * @param array $quiz Array containing quiz data
     * @param int $context The page context ID
     * @param int $cm The Course Module Id
     * @param view_page $viewobj
     */
    public function view_result_info($quiz, $context, $cm, $viewobj) {
        $output = '';
        if (!$viewobj->numattempts && !$viewobj->gradecolumn && is_null($viewobj->mygrade)) {
            return $output;
        }
        $resultinfo = '';

        if ($viewobj->overallstats) {
            if ($viewobj->moreattempts) {
                $a = new stdClass();
                $a->method = quiz_get_grading_option_name($quiz->grademethod);
                $a->mygrade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->quizgrade = quiz_format_grade($quiz, $quiz->grade);
                $resultinfo .= $this->heading(get_string('gradesofar', 'quiz', $a), 3);
            } else {
                $a = new stdClass();
                $a->grade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->maxgrade = quiz_format_grade($quiz, $quiz->grade);
                $a = get_string('outofshort', 'quiz', $a);
                $resultinfo .= $this->heading(get_string('yourfinalgradeis', 'quiz', $a), 3);
            }
        }

        if ($viewobj->mygradeoverridden) {

            $resultinfo .= html_writer::tag('p', get_string('overriddennotice', 'grades'),
                            array('class' => 'overriddennotice'))."\n";
        }
        if ($viewobj->gradebookfeedback) {
            $resultinfo .= $this->heading(get_string('comment', 'quiz'), 3);
            $resultinfo .= html_writer::div($viewobj->gradebookfeedback, 'quizteacherfeedback') . "\n";
        }
        if ($viewobj->feedbackcolumn) {
            $resultinfo .= $this->heading(get_string('overallfeedback', 'quiz'), 3);
            $resultinfo .= html_writer::div(
                            quiz_feedback_for_grade($viewobj->mygrade, $quiz, $context),
                            'quizgradefeedback') . "\n";
        }

        if ($resultinfo && $viewobj->mygrade) {
            $output .= $this->box($resultinfo, 'generalbox', 'feedback');
        }
        return $output;
    }
}
