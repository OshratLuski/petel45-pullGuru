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
 * Question behaviour for deferred feedback with penalty for late work
 *
 * @package    qbehaviour
 * @subpackage deferredpenalty
 * @copyright  2016 Daniel Thies <dthies@ccal.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../deferredfeedback/behaviour.php');

class qbehaviour_deferredpenalty extends qbehaviour_deferredfeedback {

    public function process_finish(question_attempt_pending_step $pendingstep) {
        global $DB, $PAGE;
        $quizpenalty = $DB->get_record('quizaccess_changebehaviour', ['quizid'=> $PAGE->cm->instance]);
        $keep = parent::process_finish($pendingstep);
        $fraction = $pendingstep->get_fraction();
        if ($quizpenalty->penalty && $quizpenalty->penalty != 0) { // Use quizaccess late submission penalty, if exits.
            $penalty = $quizpenalty->penalty / 100;
        } else {
            $penalty = 0;
            //$penalty = $this->question->penalty;
        }
        // To reduce the penalty slightly uncomment following.
        // $penalty = $penalty / (1.0 + $penalty);
        $adjustment = 1.0 - $penalty;
        if ($keep == question_attempt::KEEP &&
                $fraction != null) {
            $pendingstep->set_fraction($fraction * $adjustment);
        }
        $this->qa->set_metadata('penalty', $penalty);
        return $keep;
    }

}

