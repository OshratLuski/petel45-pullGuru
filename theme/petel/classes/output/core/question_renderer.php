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
 * @package    moodlecore
 * @subpackage questionengine
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel\output\core;

use core\output\html_writer;
use moodle_url;
use qbehaviour_renderer;
use qtype_renderer;
use question_attempt;
use question_display_options;
use theme_petel\question_flags as question_flags;
use qtype_description;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * This renderer controls the overall output of questions. It works with a
 * {@link qbehaviour_renderer} and a {@link qtype_renderer} to output the
 * type-specific bits. The main entry point is the {@link question()} method.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_renderer extends \core_question_renderer {

    /**
     * Generate the display of a question in a particular state, and with certain
     * display options. Normally you do not call this method directly. Intsead
     * you call {@link question_usage_by_activity::render_question()} which will
     * call this method with appropriate arguments.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return string HTML representation of the question.
     */
    public function question(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
            qtype_renderer $qtoutput, question_display_options $options, $number) {
        global $PAGE;

        $output = '';
        $name = $qa->get_question()->name;

        $output .= html_writer::start_tag('div', array(
            'id' => $qa->get_outer_question_div_unique_id(),
            'class' => implode(' ', array(
                'que row',
                $qa->get_question(false)->get_type_name(),
                $qa->get_behaviour_name(),
                $qa->get_state_class($options->correctness && $qa->has_marks()),
            ))
        ));

        // q points except info.
        $questionpointstext = '';
        if ($qa->get_question(false)->get_type_name() != 'description' && $qa->get_max_mark() != 0) {
            $questionpoints = $qa->get_max_mark() ? : '';
            $a = new stdClass();
            $a->questionpoints = $questionpoints;
            $questionpointstext = html_writer::tag('span', get_string('questionpointstext', 'theme_petel', $a),
                                    array('class' => 'question-points mr-3'));
        }

        // Build question title.
        $arrtitle = [];
        if (in_array($PAGE->pagetype,['mod-quiz-attempt', 'mod-quiz-report', 'mod-quiz-review'])) {
            $lqsoptions = \local_quiz_summary_option\funcs::get_quiz_config($this->page->cm->id);

            if (!$lqsoptions->summary_numbering) {
                $arrtitle[] = $this->number($number);
            }

            if (!$lqsoptions->summary_questionname) {
                $arrtitle[] = $this->qname($name);
            }

            if (!$options->marks && !$lqsoptions->summary_grade) {
                $arrtitle[] = $questionpointstext;
            }
        }else{
            $arrtitle[] = $this->number($number);
        }

        $questiontitle = implode('', $arrtitle);

        $btnsblock = html_writer::tag('div', $this->info($qa, $behaviouroutput, $qtoutput, $options, $number),
        array('class' => 'quiz-btns-block d-flex align-items-center justify-content-between w-100'));

        $output .= html_writer::tag('div', $this->output->heading($questiontitle, 3, 'm-0 d-flex flex-wrap flex-shrink-0') . $btnsblock,
                        array('class' => 'info col-12 d-flex'));

        $output .= html_writer::start_tag('div', array('class' => 'content col-12 m-0 p-0 container-fluid'));
        $output .= html_writer::start_tag('div', array('class' => 'content-inner d-flex w-100 m-0 p-0 row'));

        $output .= html_writer::tag('div', $this->add_part_heading($qtoutput->formulation_heading(),
                $this->formulation($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'formulation clearfix mr-auto col-12'));

        $output .= html_writer::end_tag('div');
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('feedback', 'question'),
                    $this->outcome($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'outcome clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('comments', 'question'),
                    $this->manual_comment($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'comment clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->response_history($qa, $behaviouroutput, $qtoutput, $options),
                array('class' => 'history clearfix border p-2'));
        $PAGE->requires->js_call_amd('theme_petel/quiz_comment');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    protected function number($number) {
        if (trim($number) === '') {
            return '';
        }
        $numbertext = '';
        if (trim($number) === 'i') {
            // Disable "Info" in question title get_string('information', 'question')
            //$numbertext = html_writer::tag('span', '', array('class' => 'question-number mr-3'));
        } else {
            $numbertext = html_writer::tag('span', get_string('questionx', 'question', $number),
                array('class' => 'question-number mr-3'));
        }
        return $numbertext;
    }

    protected function qname($name) {
        if (trim($name) === '') {
            return '';
        }
        if (trim($name) === 'i') {
            return html_writer::tag('span', get_string('information', 'question'),
                array('class' => 'question-name mr-3'));
        } else {
            return html_writer::tag('span', $name,
                array('class' => 'question-name mr-3'));
        }
    }

    // /**
    //  * Generate the information bit of the question display that contains the
    //  * metadata like the question number, current state, and mark.
    //  * @param question_attempt $qa the question attempt to display.
    //  * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
    //  *      specific parts.
    //  * @param qtype_renderer $qtoutput the renderer to output the question type
    //  *      specific parts.
    //  * @param question_display_options $options controls what should and should not be displayed.
    //  * @param string|null $number The question number to display. 'i' is a special
    //  *      value that gets displayed as Information. Null means no number is displayed.
    //  * @return HTML fragment.
    //  */
    protected function info(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput, question_display_options $options, $number) {
        global $PAGE;

        $output = '';

        if (!in_array($PAGE->pagetype,['mod-quiz-attempt', 'mod-quiz-report', 'mod-quiz-review'])) {
            return $output;
        }

        if ($PAGE->pagetype == 'mod-quiz-report') {
            $output .= $this->fullscreen_link($qa, $options);
            return $output;
        }

        // Hide buttons if qtype == description
        if (!self::is_qtype_description($qa)) {

            $lqsoptions = \local_quiz_summary_option\funcs::get_quiz_config($this->page->cm->id);

            if($options->marks && !$lqsoptions->summary_grade) {
                $output .= $this->mark_summary($qa, $behaviouroutput, $options);
            }

            if(!$lqsoptions->summary_mark) {
                $output .= $this->question_flag($qa, $options->flags);
            }

            $output .= $this->edit_question_link($qa, $options);

            if(!$lqsoptions->summary_teacherdialog && !\core_user::awaiting_action()) {
                $output .= $this->send_message_teacher($qa, $options, $number);
            }

            $output .= $this->fullscreen_link($qa, $options);
        } else {
            $output .= $this->edit_question_link($qa, $options);
            $output .= $this->fullscreen_link($qa, $options);
        }

        return $output;
    }

    public function is_qtype_description($qa) {
        return $qa->get_question()->qtype instanceof qtype_description;
    }

    /**
     * Render the question flag, assuming $flagsoption allows it.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $flagsoption the option that says whether flags should be displayed.
     */
    protected function question_flag(question_attempt $qa, $flagsoption) {
        global $CFG;

        $divattributes = array('class' => 'customquestionflag');

        switch ($flagsoption) {
            case question_display_options::VISIBLE:
                $flagcontent = $this->get_flag_html($qa->is_flagged());
                break;

            case question_display_options::EDITABLE:
                $id = $qa->get_flag_field_name();
                // The checkbox id must be different from any element name, because
                // of a stupid IE bug:
                // http://www.456bereastreet.com/archive/200802/beware_of_id_and_name_attribute_mixups_when_using_getelementbyid_in_internet_explorer/
                $checkboxattributes = array(
                    'type'  => 'checkbox',
                    'id'    => $id . 'checkbox',
                    'name'  => $id,
                    'value' => 1,
                );
                if ($qa->is_flagged()) {
                    $checkboxattributes['checked'] = 'checked';
                }
                $postdata = question_flags::get_postdata($qa);

                $customtitle =  html_writer::tag('div',
                    html_writer::tag('span', '', array('class'=>'flag')), array('class' => 'petel-custom-tooltip px-2 position-absolute', 'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));

                $flagcontent = html_writer::empty_tag('input',
                    array('type' => 'hidden', 'name' => $id, 'value' => 0)) .
                html_writer::empty_tag('input', $checkboxattributes) .
                html_writer::empty_tag('input',
                    array('type' => 'hidden', 'value' => $postdata, 'class' => 'customquestionflagpostdata')) .
                    html_writer::tag('label', $this->get_flag_html($qa->is_flagged(), $id . 'img'),
                    array('id' => $id . 'label', 'for' => $id . 'checkbox'));


                $divattributes = array(
                    'class'         => ' quiz-btn customquestionflag editable',
                    'aria-atomic'   => 'true',
                    'aria-relevant' => 'text',
                    'aria-live'     => 'assertive',
                );

                break;

            default:
                $flagcontent = '';
        }

        question_flags::initialise_js();

        return html_writer::nonempty_tag('div', $flagcontent . $customtitle, $divattributes);
    }

    /**
     * Work out the actual img tag needed for the flag
     *
     * @param bool $flagged whether the question is currently flagged.
     * @param string $id an id to be added as an attribute to the img (optional).
     * @return string the img tag.
     */
    protected function get_flag_html($flagged, $id = '') {

        // <i class="fal fa-bookmark regular-state"></i>
        // <i class="far fa-bookmark hover-state"></i>
        // <i class="fas fa-bookmark active-state"></i>

        $attributes = [];

        if ($flagged) {

            $attributes['class'] = 'customquestionflagimage far fa-bookmark active-state';

            // $icon = 'i/flagged';
            // $alt = get_string('flagged', 'question');
            // $label = get_string('clickunflag', 'question');
        } else {
            $attributes['class'] = 'customquestionflagimage fal fa-bookmark regular-state';

            // $icon = 'i/unflagged';
            // $alt = get_string('notflagged', 'theme_petel');
            // $label = get_string('clickflag', 'question');
        }
        // $attributes = array(
        //     'src' => $this->image_url($icon),
        //     'alt' => $alt,
        //     'class' => 'customquestionflagimage',
        // );

        if ($id) {
            $attributes['id'] = $id;
        }

        $img = html_writer::tag('i', '', $attributes);

        // $img = html_writer::empty_tag('img', $attributes);
        // $img .= html_writer::span($label);

        return $img;
    }

    protected function edit_question_link(question_attempt $qa,
        question_display_options $options) {
        global $CFG;

        if (empty($options->editquestionparams)) {
            return '';
        }

        $params = $options->editquestionparams;
        if ($params['returnurl'] instanceof moodle_url) {
            $params['returnurl'] = $params['returnurl']->out_as_local_url(false);
        }
        $params['id'] = $qa->get_question_id();
        $editurl      = new moodle_url('/question/bank/editquestion/question.php', $params);
        $icon = '<i class="fal fa-edit regular-state"></i>
                 <i class="far fa-edit hover-state"></i>
                 <i class="fas fa-edit active-state"></i>';

        $tooltip =  html_writer::tag('div',
        html_writer::tag('span', get_string('editquestion', 'core_question'), array()),
        array('class' => 'petel-custom-tooltip px-2 position-absolute',
            'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));
        return html_writer::tag('div', html_writer::link(
            // $editurl, $this->pix_icon('i/pen-solid', get_string('edit'), '', array('class' => 'iconsmall')) .
            $editurl, $icon . $tooltip, array()),
            array('class' => 'quiz-btn editquestion'));

    }

    protected function fullscreen_link(question_attempt $qa,
        question_display_options $options) {
        global $CFG, $PAGE;

        // $params = $options->editquestionparams;
        // if ($params['returnurl'] instanceof moodle_url) {
        //     $params['returnurl'] = $params['returnurl']->out_as_local_url(false);
        // }
        $params['id'] = $qa->get_question_id();
        $editurl      = new moodle_url('#', $params);

        // TODO: Fullscreen JS.
        //     $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', array('#'.$attr['id']));

        $icon = '<i class="fal fa-expand regular-state"></i>
                 <i class="far fa-expand hover-state"></i>
                 <i class="fas fa-expand active-state"></i>
                 <i class="fal fa-compress-wide"></i>
                 <i class="far fa-compress-wide"></i>
                 <i class="fas fa-compress-wide"></i>';
        $tooltip =  html_writer::tag('div', html_writer::tag('span', get_string('fullscreen', 'theme_petel'), array()),
                 array('class' => 'petel-custom-tooltip px-2 position-absolute', 'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));

        return html_writer::tag('div', html_writer::link('#', $icon ) .  $tooltip,
            array('class' => 'quiz-btn fullscreen-btn d-none d-md-flex align-items-center'));
    }

    protected function send_message_teacher(question_attempt $qa, question_display_options $options, $number) {
        global $CFG, $PAGE, $DB, $USER;

        list($enable, $user) = \theme_petel\funcs::custom_messages();

        if (!$enable) {
            return '';
        }

        $attr = \core_message\helper::messageuser_link_params($user->id);
        $attr['id'] .= '-'.$qa->get_question_id();
        $attr['class'] = 'qsendmessage'.$qa->get_question_id();

        $coursename = '';
        $cmname = '';
        $text = '';
        if($attempt = $DB->get_record('quiz_attempts', array('uniqueid' => $qa->get_usage_id()))){
            $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz));

            $course = get_course($quiz->course);
            $coursename = $course->fullname;
            $cmname = $quiz->name;

            $attemptid = $attempt->id;
            $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course);
            $cmid = $cm->id;

            $a = new \StdClass();
            $a->number = $number;
            $a->cmname = $cmname;
            $a->coursename = $coursename;
            $a->question = get_string('question', 'theme_petel');

            $qlink = new \moodle_url('/mod/quiz/review.php', [
                'attempt' => $attemptid,
                'cmid' => $cmid,
            ]);

            $a->qlink = '<a href="' . $qlink->out(false) . '" target="_blank" class="text-link">' . $a->question . ' ' . $a->number . '</a>';

            $text = get_string('qmessageforteacher', 'theme_petel', $a);
            // Remove " that can break JS.
            $text = str_replace('"', '', $text);
        }

        $PAGE->requires->js_amd_inline('
            require(["jquery", "core/ajax", "core/notification"], function($, Ajax, Notification) {
                $(".qsendmessage'.$qa->get_question_id().'").on("click", function(e) {
                    $("*[data-region='."'content-messages-footer-container'".']").find("textarea").val(`'.$text.'`);
                    $("*[data-region='."'content-messages-footer-container'".']").find("textarea").text(`'.$text.'`);

                    let obj = $("*[data-region='."'message-drawer'".']").parent();

                    if(obj.hasClass("hidden")){
                        Ajax.call([{
                            methodname: "theme_petel_quiz_student_question_message",
                            args: {
                                fromuserid: '.$USER->id.',
                                touserid: '.$user->id.',
                                questionid: '.$qa->get_question_id().',
                            },
                            done: function (response) {
                            },
                            fail: Notification.exception
                        }]);
                    }

                    setTimeout(function() {
                        $(".showrouteback").hide();
                        $("#conversation-actions-menu-button").hide();
                    }, 1000);
                })
            });
        ');

        $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', array('#'.$attr['id']));

        $icon =  '<i class="fal fa-comment-dots regular-state" aria-hidden="true"></i>
                  <i class="far fa-comment-dots hover-state" aria-hidden="true"></i>
                  <i class="fas fa-comment-dots active-state" aria-hidden="true"></i>';

        $tooltip =  html_writer::tag('div', html_writer::tag('span', get_string('message', 'theme_petel'), array()),
                  array('class' => 'petel-custom-tooltip px-2 position-absolute', 'data-position' => 'top-left', 'data-carret-position' => 'bottom-left'));
        return html_writer::tag('div', html_writer::link( $CFG->wwwroot. '/message/index.php?id=' . $user->id, $icon, $attr) . $tooltip, array('class' => 'quiz-btn message'));
    }

}