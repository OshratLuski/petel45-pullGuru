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
 * diagnosticadvai 'question' renderer class.
 *
 * @package    qtype_diagnosticadvai
 * @subpackage diagnosticadvai
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generates the output for diagnosticadvai 'question's.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadvai_renderer extends qtype_renderer {
    /**
     * Generates the formulation and controls for the question.
     *
     * @param question_attempt $qa The question attempt object
     * @param question_display_options $options Display options for the question
     * @return string HTML output
     * @throws moodle_exception
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $USER;
        $this->page->requires->js_call_amd('qtype_diagnosticadvai/message_sender', 'init', [$options]);


        $slot = $qa->get_slot();
        $attemptstate = $qa->get_state()->is_finished();

        if ($USER->id != $qa->get_last_step()->get_user_id()){
            $attemptstate = true;
        }

        return $this->render_from_template('qtype_diagnosticadvai/chat', [
            'slot' => $slot,
            'attemptstate' => $attemptstate,
        ]);
    }

    /**
     * Returns the heading for the question formulation.
     *
     * @return string The heading text
     */
    public function formulation_heading() {
        return get_string('informationtext', 'qtype_diagnosticadvai');
    }
}
