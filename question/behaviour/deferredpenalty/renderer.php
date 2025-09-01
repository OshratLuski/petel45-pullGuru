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
 * Renderer for outputting parts of a question with deferred feedback
 *
 * @package    qbehaviour
 * @subpackage deferredpenalty
 * @copyright  2015 Daniel Thies <dthies@ccal.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../deferredfeedback/renderer.php');

class qbehaviour_deferredpenalty_renderer extends qbehaviour_deferredfeedback_renderer {
    public function feedback(question_attempt $qa, question_display_options $options) {
        global $DB;
        $quizpenalty = $DB->get_record('quizaccess_changebehaviour', ['quizid'=> $this->page->cm->instance]);
        if (empty($qa->get_metadata('penalty')) || $qa->get_state()->is_incorrect()) {
            if ($quizpenalty->behaviourduration > 0 || $quizpenalty->behaviourtime > 0) {
                return parent::feedback($qa, $options) . get_string('feedbackwithoutpenalty', 'qbehaviour_deferredpenalty');
            } else {
                return parent::feedback($qa, $options);
            }
        } else {
            if ($quizpenalty->penalty && $quizpenalty->penalty != 0) { // Use quizaccess late submission penalty, if exits.
                $penalty = $quizpenalty->penalty / 100;

                $penalty = round(100000*$penalty)/1000;
                return parent::feedback($qa, $options) .  get_string('feedback',
                                'qbehaviour_deferredpenalty', array('penalty' => $penalty)) ;
            } else {
                //$penalty = $qa->get_metadata('penalty');

                return parent::feedback($qa, $options) .  get_string('feedbackwithoutpenalty', 'qbehaviour_deferredpenalty') ;
            }
        }
    }

}
