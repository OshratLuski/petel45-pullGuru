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
 * @package    qtype
 * @subpackage diagnostic
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Multichoice question type conversion handler
 */
class moodle1_qtype_diagnostic_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'MULTICHOICE',
        );
    }

    /**
     * Appends the diagnostic specific information to the question
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the diagnostic.
        if (!isset($data['diagnostic'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data.
            $data['diagnostic'] = array(array(
                'single'                         => 1,
                'shuffleanswers'                 => 1,
                'otheranswer'                 	 => 1,
                'answerreason'                 	 => 1,
                'addcbm'                 		 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'partiallycorrectfeedback'       => '',
                'partiallycorrectfeedbackformat' => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc',
            ));
        }
        $this->write_diagnostic($data['diagnostic'], $data['oldquestiontextformat'], $data['id']);
    }

    /**
     * Converts the diagnostic info and writes it into the question.xml
     *
     * @param array $diagnostics the grouped structure
     * @param int $oldquestiontextformat - {@see moodle1_question_bank_handler::process_question()}
     * @param int $questionid question id
     */
    protected function write_diagnostic(array $diagnostics, $oldquestiontextformat, $questionid) {
        global $CFG;

        // The grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything.
        foreach ($diagnostics as $diagnostic) {
            // Append an artificial 'id' attribute (is not included in moodle.xml).
            $diagnostic['id'] = $this->converter->get_nextid();

            // Replay the upgrade step 2009021801.
            $diagnostic['correctfeedbackformat']               = 0;
            $diagnostic['partiallycorrectfeedbackformat']      = 0;
            $diagnostic['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldquestiontextformat == FORMAT_MOODLE) {
                $diagnostic['correctfeedback']                 = text_to_html($diagnostic['correctfeedback'], false, false, true);
                $diagnostic['correctfeedbackformat']           = FORMAT_HTML;
                $diagnostic['partiallycorrectfeedback']        = text_to_html($diagnostic['partiallycorrectfeedback'], false, false, true);
                $diagnostic['partiallycorrectfeedbackformat']  = FORMAT_HTML;
                $diagnostic['incorrectfeedback']               = text_to_html($diagnostic['incorrectfeedback'], false, false, true);
                $diagnostic['incorrectfeedbackformat']         = FORMAT_HTML;
            } else {
                $diagnostic['correctfeedbackformat']           = $oldquestiontextformat;
                $diagnostic['partiallycorrectfeedbackformat']  = $oldquestiontextformat;
                $diagnostic['incorrectfeedbackformat']         = $oldquestiontextformat;
            }

            $diagnostic['correctfeedback'] = $this->migrate_files(
                    $diagnostic['correctfeedback'], 'question', 'correctfeedback', $questionid);
            $diagnostic['partiallycorrectfeedback'] = $this->migrate_files(
                    $diagnostic['partiallycorrectfeedback'], 'question', 'partiallycorrectfeedback', $questionid);
            $diagnostic['incorrectfeedback'] = $this->migrate_files(
                    $diagnostic['incorrectfeedback'], 'question', 'incorrectfeedback', $questionid);

            $this->write_xml('diagnostic', $diagnostic, array('/diagnostic/id'));
        }
    }
}
