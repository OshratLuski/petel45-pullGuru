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
 * Combined question embedded sub question renderer class.
 *
 * @package   qtype_shortanswer
 * @copyright  2019 Jean-Michel Vedrinr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/renderer.php');

class qtype_essay_embedded_renderer extends qtype_combined_text_entry_renderer_base {

    public function subquestion(question_attempt $qa,
                                question_display_options $options,
                                qtype_combined_combinable_base $subq,
                                $placeno) {
        global $PAGE, $DB;

        $question = $subq->question;
        $responseoutput = $question->get_format_renderer($PAGE);
        $responseoutput->set_displayoptions($options);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var($subq->step_data_name('answer'));

        if (!$step->has_qt_var($subq->step_data_name('answer')) && empty($options->readonly)) {
            // Question has never been answered, fill it with response template.
            $step = new question_attempt_step(array($subq->step_data_name('answer')=>$question->responsetemplate));
        }

        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input_combinable($subq->step_data_name('answer'), $qa,
                $step, $question->responsefieldlines, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only($subq->step_data_name('answer'), $qa,
                $step, $question->responsefieldlines, $options->context);
        }

        $files = '';
        // if ($question->attachments) {
        //     if (empty($options->readonly)) {
        //         $files = $this->files_input($qa, $question->attachments, $options, $subq);

        //     } else {
        //         $files = $this->files_read_only($qa, $options, $subq);
        //     }
        // }

        //$currentanswer = $qa->get_last_qt_var($subq->step_data_name('answer'));
        //$inputname       = $qa->get_qt_field_name($subq->step_data_name('answer'));
        //echo $inputname;exit;

        $result = '';

        // TODO: plain, mono
        $result .= <<<STYLE

<style>

.que.combined textarea.qtype_essay_response {
    width: 100%;
}

.que.combined textarea.qtype_essay_response.qtype_essay_plain {
    white-space: pre-wrap;
    font: inherit;
}

.que.combined .qtype_essay_response.qtype_essay_monospaced {
    white-space: pre;
    font-family: Andale Mono, Monaco, Courier New, DejaVu Sans Mono, monospace;
}

.que.combined .qtype_essay_response {
    min-height: 3em;
}

.que.combined .qtype_essay_response.readonly {
    background-color: white;
}

.que.combined div.qtype_essay_response textarea {
    width: 100%;
}
</style>
STYLE;

        $result .= html_writer::tag('div', $question->format_questiontext($qa),
            array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        // $result .= html_writer::tag('div', $files, array('class' => 'attachments'));
        $result .= html_writer::end_tag('div');

        // Feedback if not graded.
        $feedbackenable = false;
        $qaobj = $DB->get_record('quiz_attempts', ['uniqueid' => $qa->get_usage_id()]);
        if($qaobj && $qaobj->state == mod_quiz\quiz_attempt::FINISHED){
            $feedbackenable = true;

            if($gi = $DB->get_record('grade_items', ['itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $qaobj->quiz])){
                if($gg = $DB->get_record('grade_grades', ['itemid' => $gi->id, 'userid' => $qaobj->userid])){
                    if(!empty($gg->finalgrade)){
                        $feedbackenable = false;
                    }
                }
            }
        }

        if($feedbackenable){
            $result .= html_writer::tag('div', get_string('waitteacherreview', 'qtype_essay'),
                    array('class' => 'alert quizfeedback'));
        }

        // Comment graderinfo.
        $result .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('commentcustom', 'qtype_essay'),
                        $this->manual_comment_custom($qa, $subq, $options)),
                array('class' => 'comment clearfix'));

        $result = preg_replace('/brokenfile.php#/', 'question/type/essay/imagefile.php', $result);

        return $result;
    }

    public function files_read_only(question_attempt $qa, question_display_options $options, $subq) {
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $output = array();

        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                    $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                    'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    public function files_input(question_attempt $qa, $numallowed,
            question_display_options $options, $subq) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $question = $subq->question;


        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $numallowed;
        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        $pickeroptions->context = $options->context;
        $pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;

        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        $pickeroptions->accepted_types = $question->filetypeslist;

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');

        $text = '';
        if (!empty($question->filetypeslist)) {
            $text = html_writer::tag('p', get_string('acceptedfiletypes', 'qtype_essay'));
            $filetypesutil = new \core_form\filetypes_util();
            $filetypes = $question->filetypeslist;
            $filetypedescriptions = $filetypesutil->describe_file_types($filetypes);
            $text .= $this->render_from_template('core_form/filetypes-descriptions', $filetypedescriptions);
        }
        return $filesrenderer->render($fm). html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $pickeroptions->itemid)) . $text;
    }

    public function manual_comment_custom(question_attempt $qa, qtype_combined_combinable_base $subq, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $subq->question;

        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfoformat, $qa, 'qtype_essay',
                'graderinfo', $question->id), array('class' => 'graderinfo'));
    }

    private function add_part_heading($heading, $content) {
        if ($content) {
            $content = html_writer::tag('h4', $heading, array('class' => 'accesshidee')) . $content;
        }
        return $content;
    }

}
