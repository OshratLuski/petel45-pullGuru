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
 * Question type class for the diagnostic ADV question type.
 *
 * @package    qtype
 * @subpackage diagnosticadv
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/diagnosticadv/question.php');

class qtype_diagnosticadv_answer extends question_answer {
    public $custom;
    public $answerformat;
    /**
     * Constructor.
     * @param int $id the answer.
     * @param string $answer the answer.
     * @param int $answerformat the format of the answer.
     * @param number $fraction the fraction this answer is worth.
     * @param string $feedback the feedback for this answer.
     * @param int $feedbackformat the format of the feedback.
     * @param integer $blankindex
     */
    public function __construct($id, $answer, $fraction, $feedback, $feedbackformat, $custom, $answerformat = FORMAT_HTML) {
        parent::__construct($id, $answer, $fraction, $feedback, $feedbackformat);
        $this->custom = $custom;
        $this->answerformat = $answerformat;
    }
}


/**
 * The diagnostic ADV question type.
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadv extends question_type {

    public function extra_question_fields() {
        return array('qtype_diagnosticadv_options', 'security', 'hidemark', 'usecase', 'required', 'teacherdesc', 'anonymous', 'aianalytics', 'promt', 'temperature');
    }

    public function extra_answer_fields() {
        return array('qtype_diagnosticadv_answers', 'custom');
    }

    protected function is_extra_answer_fields_empty($questiondata, $key) {

        return !isset($questiondata->custom[$key]) || empty($questiondata->custom[$key]);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    /**
     * Fills the standard answer fields including answer text and format.
     *
     * @param stdClass $answer Answer record to update.
     * @param object $question Question object from the editing form.
     * @param int $key Index of this answer in the arrays.
     * @param context $context The question context.
     * @return stdClass The updated answer record.
     */
    protected function fill_answer_fields($answer, $questiondata, $key, $context) {
        // Extract text and format from the answer editor.
        $answerdata = $questiondata->answer[$key];
        if (is_array($answerdata)) {
            $answer->answer = $answerdata['text'];
            $answer->answerformat = $answerdata['format'];
        }
        else{
            $answer->answer = $answerdata;
        }

        // Extract text and format from the feedback editor.
        $feedbackdata = $questiondata->feedback[$key];
        $answer->feedback = $feedbackdata['text'];
        $answer->feedbackformat = $feedbackdata['format'];

        $answer->fraction = $questiondata->fraction[$key];

        return $answer;
    }


    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();

        // Perform sanity checks on fractional grades.
        $maxfraction = -1;
        foreach ($question->answer as $key => $answerdata) {
            if ($question->fraction[$key] > $maxfraction) {
                $maxfraction = $question->fraction[$key];
            }
        }

        if ($maxfraction != 1) {
            $result->error = get_string('fractionsnomax', 'question', $maxfraction * 100);
            return $result;
        }

        $extraquestionfields = $this->extra_question_fields();

        if (is_array($extraquestionfields)) {
            $question_extension_table = array_shift($extraquestionfields);

            $function = 'update';
            $questionidcolname = $this->questionid_column_name();
            $options = \qtype_diagnosticadv\options::get_record([$questionidcolname => $question->id]);
            if (!$options) {
                $function = 'create';
                $options = new \qtype_diagnosticadv\options();
                $options->set($questionidcolname, $question->id);
            }
            if (empty($question->security)){
                $question->required = 0;
            }
            foreach ($extraquestionfields as $field) {
                if (property_exists($question, $field)) {
                    if (isset($question->$field['text'])) {
                        $options->set($field, $question->$field['text']);
                    } else {
                        $options->set($field, $question->$field);
                    }
                }
            }

            $options->$function();
        }

        $this->save_question_answers($question);

        $this->save_hints($question);
    }

    protected function is_answer_empty($questiondata, $key) {
        // Support editor field structure for answer.
        if(is_array($questiondata->answer[$key])) {
            return !isset($questiondata->answer[$key]['text']) || trim($questiondata->answer[$key]['text']) === '';
        }
        return !isset($questiondata->answer[$key]) || trim($questiondata->answer[$key]) === '';
    }


    /**
     * Save the answers, with any extra data.
     *
     * Questions that use answers will call it from {@link save_question_options()}.
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     * @return object $result->error or $result->notice
     */
    public function save_question_answers($question) {
        global $DB;

        $context = $question->context;
        $oldanswers = $DB->get_records('question_answers',
            ['question' => $question->id], 'id ASC');

        $extraanswerfields = $this->extra_answer_fields();
        $isextraanswerfields = is_array($extraanswerfields);
        $extraanswertable = '';
        $oldanswerextras = [];

        if ($isextraanswerfields) {
            $extraanswertable = array_shift($extraanswerfields);
            // Get *all* existing extras for this question.
            $oldanswerextras = \qtype_diagnosticadv\answers::get_records_select(
                'answerid IN (SELECT id FROM {question_answers} WHERE question = ?)',
                [$question->id]
            );
        }

        $usedextraids = [];

        foreach ($question->answer as $key => $answerdata) {
            if ($this->is_answer_empty($question, $key)) {
                continue;
            }

            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $answer = $this->fill_answer_fields($answer, $question, $key, $context);
            if (is_array($question->answer[$key]) && isset($question->answer[$key]['itemid'])) {
                $answer->answer = file_save_draft_area_files(
                    $question->answer[$key]['itemid'],
                    $context->id,
                    'question',
                    'answer',
                    $answer->id,
                    null,
                    $question->answer[$key]['text']
                );
            }
            $DB->update_record('question_answers', $answer);

            if ($isextraanswerfields) {
                $answerextradata = new stdClass();
                $answerextradata->answerid = $answer->id;

                // Always set the 'custom' field, even if it's 0.
                $answerextradata->custom = isset($question->custom[$key]) ? $question->custom[$key] : 0;

                $existing = \qtype_diagnosticadv\answers::get_record(['answerid' => $answer->id]);

                if ($existing) {
                    $existing->from_record($answerextradata);
                    $existing->update();
                    $usedextraids[] = $existing->get('id');
                } else {
                    $new = new \qtype_diagnosticadv\answers(0, $answerextradata);
                    $usedextraids[] = $new->create()->get('id');
                }
            }

        }

        // 💡 Only delete extras that were not reused.
        if ($isextraanswerfields) {
            foreach ($oldanswerextras as $oldextra) {
                if (!in_array($oldextra->get('id'), $usedextraids)) {
                    $oldextra->delete();
                }
            }
        }

        // Delete any leftover standard answers.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', ['id' => $oldanswer->id]);
        }
    }



    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
    }

    protected function initialise_question_answers(question_definition $question,
                                                                       $questiondata, $forceplaintextanswers = true) {
        $question->answers = array();

        if (empty($questiondata->options->answers)) {
            return;
        }

        foreach ($questiondata->options->answers as $a) {
            $question->answers[$a->id] = new qtype_diagnosticadv_answer(
                $a->id,
                $a->answer,
                $a->fraction,
                $a->feedback,
                $a->feedbackformat,
                $a->custom ?? 0,
                $a->answerformat ?? FORMAT_HTML
            );
        }
    }



    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return $answer->fraction;
            }
        }
        return 0;
    }

    public function get_possible_responses($questiondata) {
        $responses = array();

        $starfound = false;
        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer,
                    $answer->fraction);
            if ($answer->answer === '*') {
                $starfound = true;
            }
        }

        if (!$starfound) {
            $responses[0] = new question_possible_response(
                    get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }

    // IMPORT/EXPORT FUNCTIONS --------------------------------- .

    /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        $question_type = $data['@']['type'];
        if ($question_type != $this->name()) {
            return false;
        }

        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;

        foreach ($extraquestionfields as $field) {
            $qo->$field = $format->getpath($data, array('#', $field, 0, '#'), '');
        }

        // Run through the answers.
        $answers = $data['#']['answer'];
        $a_count = 0;
        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($answers as $answer) {
            $ans = $format->import_answer($answer);
            $qo->answer[$a_count]['text'] = $ans->answer['text'];
            $qo->answer[$a_count]['format'] = FORMAT_HTML;
            $qo->fraction[$a_count] = $ans->fraction;
            $qo->feedback[$a_count] = $ans->feedback;
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $qo->{$field}[$a_count] =
                            $format->getpath($answer, array('#', $field, 0, '#'), '');
                }
            }
            ++$a_count;
        }
        return $qo;
    }

    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $expout='';
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <{$field}>{$exportedvalue}</{$field}>\n";
        }

        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($question->options->answers as $answer) {
            $answer->answer = format_text($answer->answer);
            $extra = '';
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $exportedvalue = $format->xml_escape($answer->$field);
                    $extra .= "      <{$field}>{$exportedvalue}</{$field}>\n";
                }
            }

            $expout .= $format->write_answer($answer, $extra);
        }
        return $expout;
    }


}
