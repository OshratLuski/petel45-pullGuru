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
 * The questiontype class for the multiple choice question type.
 *
 * @package    qtype
 * @subpackage diagnostic
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');


/**
 * The multiple choice question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnostic extends question_type {
    public function get_question_options($question) {
        global $DB, $OUTPUT;
        $question->options = $DB->get_record('qtype_diagnostic_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();
        //$add_other = false;

        $oldanswers = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');
        $options = $DB->get_record('qtype_diagnostic_options', array('questionid' => $question->id));

        // Following hack to check at least two answers exist.
        $answercount = 0;
        foreach ($question->answer as $key => $answer) {
            if ($answer != '') {
                $answercount++;
            }
        }

        if ($answercount < 2) { // Check there are at lest 2 answers for multiple choice.
            $result->notice = get_string('notenoughanswers', 'qtype_diagnostic', '2');
            return $result;
        }

        // Insert all the new answers.

        foreach ($question->answer as $key => $answerdata) {
            if (trim($answerdata['text']) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
            // Doing an import.
            $answer->answer = $this->import_or_save_files($answerdata,
                    $context, 'question', 'answer', $answer->id);
            $answer->answerformat = $answerdata['format'];
            //$answer->fraction = 0;
            $answer->feedback = $this->import_or_save_files($question->feedback[$key],
                    $context, 'question', 'answerfeedback', $answer->id);
            $answer->feedbackformat = $question->feedback[$key]['format'];

            $DB->update_record('question_answers', $answer);

            // if ($question->fraction[$key] > 0) {
                // $totalfraction += $question->fraction[$key];
            // }
            // if ($question->fraction[$key] > $maxfraction) {
                // $maxfraction = $question->fraction[$key];
            // }
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_diagnostic_options', $options);
        }

        $options->single = $question->single;
        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->otheranswer = $question->otheranswer;
        $options->otheranswertext = $question->otheranswertext;
        $options->answerreason = $question->answerreason;
        $options->addcbm = $question->addcbm;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_diagnostic_options', $options);

        $this->save_hints($question, true);

        // Perform sanity checks on fractional grades.
        // if ($options->single) {
            // if ($maxfraction != 1) {
                // $result->noticeyesno = get_string('fractionsnomax', 'qtype_diagnostic',
                        // $maxfraction * 100);
                // return $result;
            // }
        // } else {
            // $totalfraction = round($totalfraction, 2);
            // if ($totalfraction != 1) {
                // $result->noticeyesno = get_string('fractionsaddwrong', 'qtype_diagnostic',
                        // $totalfraction * 100);
                // return $result;
            // }
        // }
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        if ($questiondata->options->single) {
            $class = 'qtype_diagnostic_single_question';
        } else {
            $class = 'qtype_diagnostic_multi_question';
        }
        return new $class();
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->answernumbering = $questiondata->options->answernumbering;
        $question->otheranswer = $questiondata->options->otheranswer;
        $question->otheranswertext = $questiondata->options->otheranswertext;
        $question->answerreason = $questiondata->options->answerreason;
        $question->addcbm = $questiondata->options->addcbm;
        if (!empty($questiondata->options->layout)) {
            $question->layout = $questiondata->options->layout;
        } else {
            $question->layout = qtype_diagnostic_single_question::LAYOUT_VERTICAL;
        }
        $this->initialise_combined_feedback($question, $questiondata, true);

        $this->initialise_question_answers($question, $questiondata, false);
    }

    public function make_answer($answer) {
        // Overridden just so we can make it public for use by question.php.
        return parent::make_answer($answer);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_diagnostic_options', array('questionid' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        if (!$questiondata->options->single) {
            // Pretty much impossible to compute for _multi questions. Don't try.
            return null;
        }

        // Single choice questions - average choice fraction.
        $totalfraction = 0;
        foreach ($questiondata->options->answers as $answer) {
            $totalfraction += $answer->fraction;
        }
        return $totalfraction / count($questiondata->options->answers);
    }

    public function get_possible_responses($questiondata) {
        if ($questiondata->options->single) {
            $responses = array();

            foreach ($questiondata->options->answers as $aid => $answer) {
                $responses[$aid] = new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction);
            }

            $responses[null] = question_possible_response::no_response();
            return array($questiondata->id => $responses);
        } else {
            $parts = array();

            foreach ($questiondata->options->answers as $aid => $answer) {
                $parts[$aid] = array($aid => new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction));
            }

            return $parts;
        }
    }

    /**
     * @return array of the numbering styles supported. For each one, there
     *      should be a lang string answernumberingxxx in teh qtype_diagnostic
     *      language file, and a case in the switch statement in number_in_style,
     *      and it should be listed in the definition of this column in install.xml.
     */
    public static function get_numbering_styles() {
        $styles = array();
        foreach (array('abc', 'ABCD', '123', 'iii', 'IIII', 'none') as $numberingoption) {
            $styles[$numberingoption] =
                    get_string('answernumbering' . $numberingoption, 'qtype_diagnostic');
        }
        return $styles;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    /**
     * Exports question to XML format
     *
     * @param object $question
     * @param qformat_xml $format
     * @param string $extra (optional, default=null)
     * @return string XML representation of question
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {

        $output = '';
        $output .= "    <single>" . $format->get_single($question->options->single) . "</single>\n";
        $output .= "    <shuffleanswers>" .
            $format->get_single($question->options->shuffleanswers) . "</shuffleanswers>\n";
        $output .= "    <answernumbering>" . $question->options->answernumbering . "</answernumbering>\n";
        $output .= "    <showstandardinstruction>" . $question->options->showstandardinstruction .
            "</showstandardinstruction>\n";
        $output .= "    <otheranswer>" . $question->options->otheranswer . "</otheranswer>\n";
        $output .= "    <otheranswertext>" . $question->options->otheranswertext . "</otheranswertext>\n";
        $output .= "    <addcbm>" . $question->options->addcbm . "</addcbm>\n";
        $output .= "    <answerreason>" . $question->options->answerreason . "</answerreason>\n";

        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);

        foreach ($question->options->answers as $answer) {
            $output .= '    <answer fraction="'.$answer->fraction.'" '.$format->format($answer->answerformat).">\n";
            $output .= $format->writetext($answer->answer, 3);
            $output .= $format->write_files($answer->answerfiles);
            if ($feedback = trim($answer->feedback)) { // Usually there is no feedback.
                $output .= '      <feedback '.$format->format($answer->feedbackformat).">\n";
                $output .= $format->writetext($answer->feedback, 4);
                $output .= $format->write_files($answer->feedbackfiles);
                $output .= "      </feedback>\n";
            }
            $output .= "    </answer>\n";
        }

        return $output;
    }

    /**
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     *
     * @param array $data
     * @param qtype_ordering $question (or null)
     * @param qformat_xml $format
     * @param string $extra (optional, default=null)
     * @return object New question object
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {

        $questiontype = $format->getpath($data, array('@', 'type'), '');

        if ($questiontype !== 'diagnostic') {
            return false;
        }

        $newquestion = $format->import_headers($data);
        $newquestion->qtype = $questiontype;

        $single = $format->getpath($question, array('#', 'single', 0, '#'), 'true');
        $newquestion->single = $format->trans_single($single);

        $newquestion->answernumbering = $format->getpath($question,
            array('#', 'answernumbering', 0, '#'), 'abc');

        $shuffleanswers = $format->getpath($question,
            array('#', 'shuffleanswers', 0, '#'), 'false');
        $newquestion->shuffleanswers = $format->trans_single($shuffleanswers);

        $newquestion->showstandardinstruction = $format->getpath($question,
            array('#', 'showstandardinstruction', 0, '#'), '1');

        // TODO: fix me - following attributes are not read correctly. (nadavkav)
        $newquestion->otheranswer = $format->getpath($question,
            array('#', 'otheranswer', 0, '#'), '0');
        $newquestion->otheranswertext = $format->getpath($question,
            array('#', 'otheranswertext', 0, '#'), '');
        $newquestion->addcbm = $format->getpath($question,
            array('#', 'addcbm', 0, '#'), '0');
        $newquestion->answerreason = $format->getpath($question,
            array('#', 'answerreason', 0, '#'), '0');

        // There was a time on the 1.8 branch when it could output an empty
        // answernumbering tag, so fix up any found.
        if (empty($newquestion->answernumbering)) {
            $newquestion->answernumbering = 'abc';
        }


        // Fix empty or long question name.
        $newquestion->name = $this->fix_questionname($newquestion->name, $newquestion->questiontext);

        $newquestion->answer = array();
        $newquestion->answerformat = array();
        $newquestion->fraction = array();
        $newquestion->feedback = array();
        $newquestion->feedbackformat = array();

        $i = 0;
        while ($answer = $format->getpath($data, array('#', 'answer', $i), '')) {
            $ans = $format->import_answer($answer, true, $format->get_format($newquestion->questiontextformat));
            $newquestion->answer[$i] = $ans->answer;
            $newquestion->fraction[$i] = 1; // Will be reset later in save_question_options().
            $newquestion->feedback[$i] = $ans->feedback;
            $i++;
        }

        $format->import_combined_feedback($newquestion, $data, false);

        $format->import_hints($newquestion, $data, false);

        return $newquestion;
    }

    /**
     * Fix empty or long question name
     *
     * @param string $name
     * @param string $defaultname (optional, default='')
     * @param integer $maxnamelength (optional, default=42)
     * @return string Fixed name
     */
    public function fix_questionname($name, $defaultname='', $maxnamelength = 42) {
        if (trim($name) === '') {
            if ($defaultname) {
                $name = $defaultname;
            } else {
                $name = get_string('defaultquestionname', 'qtype_diagnostic');
            }
        }
        if (strlen($name) > $maxnamelength) {
            $name = substr($name, 0, $maxnamelength);
            if ($pos = strrpos($name, ' ')) {
                $name = substr($name, 0, $pos);
            }
            $name .= ' ...';
        }
        return $name;
    }
}
