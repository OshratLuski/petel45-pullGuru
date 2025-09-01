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
 * Multiple choice question renderer classes.
 *
 * @package    qtype
 * @subpackage diagnostic
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Base class for generating the bits of output common to multiple choice
 * single and multiple questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_diagnostic_renderer_base extends qtype_with_combined_feedback_renderer {
    protected abstract function get_input_type();

    protected abstract function get_input_name(question_attempt $qa, $value);

    protected abstract function get_input_value($value);

    protected abstract function get_input_id(question_attempt $qa, $value);

    /**
     * Whether a choice should be considered right, wrong or partially right.
     * @param question_answer $ans representing one of the choices.
     * @return fload 1.0, 0.0 or something in between, respectively.
     */
    protected abstract function is_right(question_answer $ans);

    protected abstract function prompt();

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        $question = $qa->get_question();
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => $this->get_input_type(),
            'name' => $inputname,
        );

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }

        $radiobuttons = array();
        $feedbackimg = array();
        $feedback = array();
        $classes = array();
        $result = '';

        if (!$options->readonly && $this->get_input_type() == 'radio') {
            $result .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => $this->get_input_name($qa, 0),
                    'value' => -1,
            ));
        }

        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $inputattributes['name'] = $this->get_input_name($qa, $value);
            $inputattributes['value'] = $this->get_input_value($value);
            $inputattributes['id'] = $this->get_input_id($qa, $value);
            $isselected = $question->is_choice_selected($response, $value);
            if ($isselected) {
                $inputattributes['checked'] = 'checked';
            } else {
                unset($inputattributes['checked']);
            }
            $hidden = '';
            if (!$options->readonly && $this->get_input_type() == 'checkbox') {
                $hidden .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => $inputattributes['name'],
                    'value' => 0,
                ));
            }
            $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                    html_writer::tag('label',
                        $this->number_in_style($value, $question->answernumbering) .
                        $question->make_html_inline($question->format_text(
                                $ans->answer, $ans->answerformat,
                                $qa, 'question', 'answer', $ansid)),
                    array('for' => $inputattributes['id']));

            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            if ($options->feedback && empty($options->suppresschoicefeedback) &&
                    $isselected && trim($ans->feedback)) {
                $feedback[] = html_writer::tag('div',
                        $question->make_html_inline($question->format_text(
                                $ans->feedback, $ans->feedbackformat,
                                $qa, 'question', 'answerfeedback', $ansid)),
                        array('class' => 'specificfeedback'));
            } else {
                $feedback[] = '';
            }
            $class = 'r' . ($value % 2);
            if ($options->correctness && $isselected) {
                $feedbackimg[] = $this->feedback_image($this->is_right($ans));
                $class .= ' ' . $this->feedback_class($this->is_right($ans));
            } else {
                $feedbackimg[] = '';
            }
            $classes[] = $class;
        }

        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $this->prompt(), array('class' => 'prompt'));

        $result .= html_writer::start_tag('div', array('class' => 'answer'));
        foreach ($radiobuttons as $key => $radio) {
            $result .= html_writer::tag('div', $radio . ' ' . $feedbackimg[$key] . $feedback[$key],
                    array('class' => $classes[$key])) . "\n";
        }

        if($question->otheranswer)
        {
            $result .= html_writer::start_tag('div', array('class' => 'r' . (($value + 1) % 2)));
            $inputattributes['name'] = $qa->get_qt_field_name('other');
            $inputattributes['id'] = $qa->get_qt_field_name('other');
            $inputattributes['value'] = $this->get_input_value($value + 1);
            $isselected = $qa->get_last_qt_var('other');

            if ($isselected) {
                $inputattributes['checked'] = 'checked';
            } else {
                unset($inputattributes['checked']);
            }
            $hidden = '';

            if(!$options->readonly) {
                $hidden = html_writer::empty_tag('input', array(
                        'type' => 'hidden',
                        'name' => $qa->get_qt_field_name('other'),
                        'value' => 0
                ));
            }

			$otheranswertext = $this->number_in_style($value + 1, $question->answernumbering);

			if(isset($question->otheranswertext) && $question->otheranswertext != '')
			{
				$otheranswertext .= $question->otheranswertext;
			} else {
				$otheranswertext .= get_string('other', 'qtype_diagnostic');
			}

            $result .= $hidden . html_writer::empty_tag('input', $inputattributes) . html_writer::tag('label',
                        $otheranswertext,
                        array('for' => $inputattributes['id']));

            $inputname = $qa->get_qt_field_name('other_data');
            $currentanswer = $qa->get_last_qt_var('other_data');
            $inputattributes['name'] = $inputname;
            $inputattributes['id'] = $inputname;
            $inputattributes['rows'] = 3;
            $inputattributes['cols'] = 50;
            unset($inputattributes['checked']);

            if($options->readonly) {
                $inputattributes['readonly'] = true;
            } else {
                unset($inputattributes['readonly']);
            }

            $result .= html_writer::tag('div', html_writer::tag('textarea', $currentanswer, $inputattributes));
            $result .= html_writer::end_tag('div'); // r
        }

        if($question->answerreason)
        {
                $inputname = $qa->get_qt_field_name('reason');
                $currentanswer = $qa->get_last_qt_var('reason');
                $inputattributes['name'] = $inputname;
                $inputattributes['id'] = $inputname;
                $inputattributes['rows'] = 3;
                $inputattributes['cols'] = 50;

                if($options->readonly)
                {
                    $inputattributes['readonly'] = true;
                }

                $result .= html_writer::tag('div', get_string('reasonyouranswer', 'qtype_diagnostic'), array('class' => 'prompt'));
                $result .=  html_writer::tag('div', html_writer::tag('textarea', $currentanswer,
                $inputattributes));
        }

        if($question->addcbm)
        {
                $inputname = $qa->get_qt_field_name('cbm');
                $currentanswer = $qa->get_last_qt_var('cbm');
                $result .= '<br>';
                $result .= html_writer::tag('div', get_string('cbmanswer', 'qtype_diagnostic'), array('class' => 'prompt'));
                $result .= $this->certainty_choices(qtype_diagnostic_base::$certainties, $inputname, $currentanswer, $options->readonly);
				$inputname = $qa->get_qt_field_name('cbmnotsure');
				$currentanswer = $qa->get_last_qt_var('cbmnotsure');
				$inputattributes['name'] = $inputname;
				$inputattributes['id'] = $inputname;
				unset($inputattributes['checked']);

				if($options->readonly) {
					$inputattributes['readonly'] = true;
				} else {
					unset($inputattributes['readonly']);
				}

				$result .= html_writer::tag('div', get_string('cbmnotsure', 'qtype_diagnostic'), array('class' => 'prompt'));
				$result .=  html_writer::tag('div', html_writer::tag('textarea', $currentanswer, $inputattributes));
        }

        $result .= html_writer::end_tag('div'); // Answer.

        $result .= html_writer::end_tag('div'); // Ablock.


        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($qa->get_last_qt_data()),
                    array('class' => 'validationerror'));
        }

       $this->page->requires->jquery_plugin('diagnostic', 'qtype_diagnostic');
        return $result;
    }

    protected function number_html($qnum) {
        return $qnum . '. ';
    }

    /**
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     * options returned by {@link qtype_diagnostic:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    protected function number_in_style($num, $style) {
        switch($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    function certainty_choices($certainties, $controlname, $selected, $readonly) {
        $attributes = array(
            'type' => 'radio',
            'name' => $controlname,
        );
        if ($readonly) {
            $attributes['disabled'] = 'disabled';
        }

        $choices = '';
        foreach ($certainties as $certainty) {
            $id = $controlname . $certainty;
            $attributes['id'] = $id;
            $attributes['value'] = $certainty;
            if ($selected == $certainty) {
                $attributes['checked'] = 'checked';
            } else {
                unset($attributes['checked']);
            }
            $choices .= ' ' .
                    html_writer::tag('label', html_writer::empty_tag('input', $attributes) .
                            get_string('certainty' . $certainty, 'qtype_diagnostic'), array('for' => $id));
        }
        return $choices;
    }
}


/**
 * Subclass for generating the bits of output specific to multiple choice
 * single questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnostic_single_renderer extends qtype_diagnostic_renderer_base {
    protected function get_input_type() {
        return 'radio';
    }

    protected function get_input_name(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('answer');
    }

    protected function get_input_value($value) {
        return $value;
    }

    protected function get_input_id(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('answer' . $value);
    }

    protected function is_right(question_answer $ans) {
        return $ans->fraction;
    }

    protected function prompt() {
        return get_string('selectone', 'qtype_diagnostic');
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        foreach ($question->answers as $ansid => $ans) {
            if (question_state::graded_state_for_fraction($ans->fraction) ==
                    question_state::$gradedright) {
                return get_string('correctansweris', 'qtype_diagnostic',
                        $question->make_html_inline($question->format_text($ans->answer, $ans->answerformat,
                                $qa, 'question', 'answer', $ansid)));
            }
        }

        return '';
    }
}

/**
 * Subclass for generating the bits of output specific to multiple choice
 * multi=select questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnostic_multi_renderer extends qtype_diagnostic_renderer_base {
    protected function get_input_type() {
        return 'checkbox';
    }

    protected function get_input_name(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('choice' . $value);
    }

    protected function get_input_value($value) {
        return 1;
    }

    protected function get_input_id(question_attempt $qa, $value) {
        return $this->get_input_name($qa, $value);
    }

    protected function is_right(question_answer $ans) {
        if ($ans->fraction > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    protected function prompt() {
        return get_string('selectmulti', 'qtype_diagnostic');
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $right = array();
        foreach ($question->answers as $ansid => $ans) {
            if ($ans->fraction > 0) {
                $right[] = $question->make_html_inline($question->format_text($ans->answer, $ans->answerformat,
                        $qa, 'question', 'answer', $ansid));
            }
        }

        if (!empty($right)) {
                return get_string('correctansweris', 'qtype_diagnostic',
                        implode(', ', $right));
        }
        return '';
    }

    protected function num_parts_correct(question_attempt $qa) {
        if ($qa->get_question()->get_num_selected_choices($qa->get_last_qt_data()) >
                $qa->get_question()->get_num_correct_choices()) {
            return get_string('toomanyselected', 'qtype_diagnostic');
        }

        return parent::num_parts_correct($qa);
    }
}
