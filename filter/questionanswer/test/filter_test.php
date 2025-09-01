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
 * Unit tests.
 *
 * @package filter_questionanswer
 * @category test
 * @copyright   2023 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @auther      Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @auther      Initial code by assistant author: GPT4 <gpt4@openai.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/questionanswer/filter.php'); // Include the code to test

/**
 * Tests for filter_questionanswer.
 *
 * @copyright 2023 Weizmann institute of science
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class filter_questionanswer_filter_testcase extends advanced_testcase {
    private $filter;

    protected function setUp() : void {
        parent::setUp();

        $this->resetAfterTest(true);
        // Set filter class to be tested
        $this->filter = new filter_questionanswer(context_system::instance(), array());

        // Enable glossary filter at top level.
        filter_set_global_state('questionanswer', TEXTFILTER_ON);
    }
    public function testFilter() {
        $text = "This is a {questionanswer:30,correct,Correct Answer} and this is a {questionanswer:40,incorrect,Wrong Answer}";
        $expected_result = "This is a Correct Answer and this is a ";
        $options = [];
        $result = $this->filter->filter($text, $options);
        $this->assertEquals($expected_result, $result);
        $text = "This is a {questionanswer:50,incorrect,Wrong Answer} and this is a {questionanswer:60,correct,Correct Answer}";
        $expected_result = "This is a  and this is a Correct Answer";
        $options = [];
        $result = $this->filter->filter($text, $options);
        $this->assertEquals($expected_result, $result);
        $text = "This is a {questionanswer:70,correct,Correct Answer} and this is a {questionanswer:80,incorrect,Wrong Answer}";
        $expected_result = "This is a Correct Answer and this is a ";
        $options = [];
        $result = $this->filter->filter($text, $options);
        $this->assertEquals($expected_result, $result);
    }
}