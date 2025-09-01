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
 * Contains the helper class for the select missing words question type tests.
 *
 * @package   qtype_gapselectmath
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Test helper class for the select missing words question type.
 *
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapselectmath_test_helper extends question_test_helper {

    public function get_test_questions() {
        return array('fox', 'maths', 'currency', 'multilang', 'missingchoiceno');
    }

    /**
     * Get data you would get by loading a typical select missing words question.
     *
     * @return stdClass as returned by question_bank::load_question_data for this qtype.
     */
    public static function get_gapselectmath_question_data_fox() {
        global $USER;

        $gapselectmath = new stdClass();
        $gapselectmath->id = 0;
        $gapselectmath->category = 0;
        $gapselectmath->contextid = 0;
        $gapselectmath->parent = 0;
        $gapselectmath->questiontextformat = FORMAT_HTML;
        $gapselectmath->generalfeedbackformat = FORMAT_HTML;
        $gapselectmath->defaultmark = 1;
        $gapselectmath->penalty = 0.3333333;
        $gapselectmath->length = 1;
        $gapselectmath->stamp = make_unique_id_code();
        $gapselectmath->version = make_unique_id_code();
        $gapselectmath->hidden = 0;
        $gapselectmath->idnumber = null;
        $gapselectmath->timecreated = time();
        $gapselectmath->timemodified = time();
        $gapselectmath->createdby = $USER->id;
        $gapselectmath->modifiedby = $USER->id;

        $gapselectmath->name = 'Selection from drop down list question';
        $gapselectmath->questiontext = 'The [[1]] brown [[2]] jumped over the [[3]] dog.';
        $gapselectmath->generalfeedback = 'This sentence uses each letter of the alphabet.';
        $gapselectmath->qtype = 'gapselectmath';

        $gapselectmath->options = new stdClass();
        $gapselectmath->options->shuffleanswers = true;

        test_question_maker::set_standard_combined_feedback_fields($gapselectmath->options);

        $gapselectmath->options->answers = array(
            (object) array('answer' => 'quick', 'feedback' => '1'),
            (object) array('answer' => 'fox', 'feedback' => '2'),
            (object) array('answer' => 'lazy', 'feedback' => '3'),
            (object) array('answer' => 'assiduous', 'feedback' => '3'),
            (object) array('answer' => 'dog', 'feedback' => '2'),
            (object) array('answer' => 'slow', 'feedback' => '1'),
        );

        return $gapselectmath;
    }

    /**
     * Get data required to save a select missing words question where
     * the author missed out one of the group numbers.
     *
     * @return stdClass data to create a gapselectmath question.
     */
    public function get_gapselectmath_question_form_data_missingchoiceno() {
        $fromform = new stdClass();

        $fromform->name = 'Select missing words question';
        $fromform->questiontext = ['text' => 'The [[1]] sat on the [[3]].', 'format' => FORMAT_HTML];
        $fromform->defaultmark = 1.0;
        $fromform->generalfeedback = ['text' => 'The right answer is: "The cat sat on the mat."', 'format' => FORMAT_HTML];
        $fromform->choices = [
                ['answer' => 'cat', 'choicegroup' => '1'],
                ['answer' => '',    'choicegroup' => '1'],
                ['answer' => 'mat', 'choicegroup' => '1'],
        ];
        test_question_maker::set_standard_combined_feedback_form_data($fromform);
        $fromform->shownumcorrect = 0;
        $fromform->penalty = 0.3333333;

        return $fromform;
    }

    /**
     * Get an example gapselectmath question to use for testing. This examples has one of each item.
     * @return qtype_gapselectmath_question
     */
    public static function make_gapselectmath_question_fox() {
        question_bank::load_question_definition_classes('gapselectmath');
        $gapselectmath = new qtype_gapselectmath_question();

        test_question_maker::initialise_a_question($gapselectmath);

        $gapselectmath->name = 'Selection from drop down list question';
        $gapselectmath->questiontext = 'The [[1]] brown [[2]] jumped over the [[3]] dog.';
        $gapselectmath->generalfeedback = 'This sentence uses each letter of the alphabet.';
        $gapselectmath->qtype = question_bank::get_qtype('gapselectmath');

        $gapselectmath->shufflechoices = true;

        test_question_maker::set_standard_combined_feedback_fields($gapselectmath);

        $gapselectmath->choices = array(
            1 => array(
                1 => new qtype_gapselectmath_choice('quick', 1),
                2 => new qtype_gapselectmath_choice('slow', 1)),
            2 => array(
                1 => new qtype_gapselectmath_choice('fox', 2),
                2 => new qtype_gapselectmath_choice('dog', 2)),
            3 => array(
                1 => new qtype_gapselectmath_choice('lazy', 3),
                2 => new qtype_gapselectmath_choice('assiduous', 3)),
        );

        $gapselectmath->places = array(1 => 1, 2 => 2, 3 => 3);
        $gapselectmath->rightchoices = array(1 => 1, 2 => 1, 3 => 1);
        $gapselectmath->textfragments = array('The ', ' brown ', ' jumped over the ', ' dog.');

        return $gapselectmath;
    }

    /**
     * Get an example gapselectmath question to use for testing. This exmples had unlimited items.
     * @return qtype_gapselectmath_question
     */
    public static function make_gapselectmath_question_maths() {
        question_bank::load_question_definition_classes('gapselectmath');
        $gapselectmath = new qtype_gapselectmath_question();

        test_question_maker::initialise_a_question($gapselectmath);

        $gapselectmath->name = 'Selection from drop down list question';
        $gapselectmath->questiontext = 'Fill in the operators to make this equation work: ' .
                '7 [[1]] 11 [[2]] 13 [[1]] 17 [[2]] 19 = 3';
        $gapselectmath->generalfeedback = 'This sentence uses each letter of the alphabet.';
        $gapselectmath->qtype = question_bank::get_qtype('gapselectmath');

        $gapselectmath->shufflechoices = true;

        test_question_maker::set_standard_combined_feedback_fields($gapselectmath);

        $gapselectmath->choices = array(
            1 => array(
                1 => new qtype_gapselectmath_choice('+', 1),
                2 => new qtype_gapselectmath_choice('-', 1),
                3 => new qtype_gapselectmath_choice('*', 1),
                4 => new qtype_gapselectmath_choice('/', 1),
            ));

        $gapselectmath->places = array(1 => 1, 2 => 1, 3 => 1, 4 => 1);
        $gapselectmath->rightchoices = array(1 => 1, 2 => 2, 3 => 1, 4 => 2);
        $gapselectmath->textfragments = array('7 ', ' 11 ', ' 13 ', ' 17 ', ' 19 = 3');

        return $gapselectmath;
    }

    /**
     * Get an example gapselectmath question with multilang entries to use for testing.
     * @return qtype_gapselectmath_question
     */
    public static function make_gapselectmath_question_multilang() {
        question_bank::load_question_definition_classes('gapselectmath');
        $gapselectmath = new qtype_gapselectmath_question();

        test_question_maker::initialise_a_question($gapselectmath);

        $gapselectmath->name = 'Multilang select missing words question';
        $gapselectmath->questiontext = '<span lang="en" class="multilang">The </span><span lang="ru" class="multilang"></span>[[1]] ' .
            '<span lang="en" class="multilang">sat on the</span><span lang="ru" class="multilang">сидела на</span> [[2]].';
        $gapselectmath->generalfeedback = 'This sentence uses each letter of the alphabet.';
        $gapselectmath->qtype = question_bank::get_qtype('gapselectmath');

        $gapselectmath->shufflechoices = true;

        test_question_maker::set_standard_combined_feedback_fields($gapselectmath);

        $gapselectmath->choices = array(
                1 => array(
                    1 => new qtype_gapselectmath_choice('<span lang="en" class="multilang">cat</span><span lang="ru" ' .
                        'class="multilang">кошка</span>', 1),
                    2 => new qtype_gapselectmath_choice('<span lang="en" class="multilang">dog</span><span lang="ru" ' .
                        'class="multilang">пес</span>', 1)),
                2 => array(
                    1 => new qtype_gapselectmath_choice('<span lang="en" class="multilang">mat</span><span lang="ru" ' .
                        'class="multilang">коврике</span>', 2),
                    2 => new qtype_gapselectmath_choice('<span lang="en" class="multilang">bat</span><span lang="ru" ' .
                        'class="multilang">бита</span>', 2))
                );

        $gapselectmath->places = array(1 => 1, 2 => 2);
        $gapselectmath->rightchoices = array(1 => 1, 2 => 1);
        $gapselectmath->textfragments = array('<span lang="en" class="multilang">The </span><span lang="ru" class="multilang"></span>',
            ' <span lang="en" class="multilang">sat on the</span><span lang="ru" class="multilang">сидела на</span> ', '.');

        return $gapselectmath;
    }

    /**
     * This examples includes choices with currency like options.
     * @return qtype_gapselectmath_question
     */
    public static function make_gapselectmath_question_currency() {
        question_bank::load_question_definition_classes('gapselectmath');
        $gapselectmath = new qtype_gapselectmath_question();

        test_question_maker::initialise_a_question($gapselectmath);

        $gapselectmath->name = 'Selection from currency like choices';
        $gapselectmath->questiontext = 'The price of the ball is [[1]] approx.';
        $gapselectmath->generalfeedback = 'The choice is yours';
        $gapselectmath->qtype = question_bank::get_qtype('gapselectmath');

        $gapselectmath->shufflechoices = true;

        test_question_maker::set_standard_combined_feedback_fields($gapselectmath);

        $gapselectmath->choices = [
                1 => [
                        1 => new qtype_gapselectmath_choice('$2', 1),
                        2 => new qtype_gapselectmath_choice('$3', 1),
                        3 => new qtype_gapselectmath_choice('$4.99', 1),
                        4 => new qtype_gapselectmath_choice('-1', 1)
                ]
        ];

        $gapselectmath->places = array(1 => 1);
        $gapselectmath->rightchoices = array(1 => 1);
        $gapselectmath->textfragments = array('The price of the ball is ', ' approx.');

        return $gapselectmath;
    }

    /**
     * Just for backwards compatibility.
     *
     * @return qtype_gapselectmath_question
     */
    public static function make_a_gapselectmath_question() {
        debugging('qtype_gapselectmath_test_helper::make_a_gapselectmath_question is deprecated. ' .
                "Please use test_question_maker::make_question('gapselectmath') instead.");
        return self::make_gapselectmath_question_fox();
    }

    /**
     * Just for backwards compatibility.
     *
     * @return qtype_gapselectmath_question
     */
    public static function make_a_maths_gapselectmath_question() {
        debugging('qtype_gapselectmath_test_helper::make_a_maths_gapselectmath_question is deprecated. ' .
                "Please use test_question_maker::make_question('gapselectmath', 'maths') instead.");
        return self::make_gapselectmath_question_maths();
    }

    /**
     * Just for backwards compatibility.
     *
     * @return qtype_gapselectmath_question
     */
    public static function make_a_currency_gapselectmath_question() {
        debugging('qtype_gapselectmath_test_helper::make_a_currency_gapselectmath_question is deprecated. ' .
                "Please use test_question_maker::make_question('gapselectmath', 'currency') instead.");
        return self::make_gapselectmath_question_currency();
    }

    /**
     * Just for backwards compatibility.
     *
     * @return qtype_gapselectmath_question
     */
    public static function make_a_multilang_gapselectmath_question() {
        debugging('qtype_gapselectmath_test_helper::make_a_multilang_gapselectmath_question is deprecated. ' .
                "Please use test_question_maker::make_question('gapselectmath', 'multilang') instead.");
        return self::make_gapselectmath_question_multilang();
    }
}
