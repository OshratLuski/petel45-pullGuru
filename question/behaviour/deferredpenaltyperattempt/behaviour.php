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
 * @subpackage deferredpenaltyperattempt
 * @copyright  2016 Daniel Thies <dthies@ccal.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../deferredfeedback/behaviour.php');

class qbehaviour_deferredpenaltyperattempt extends qbehaviour_deferredfeedback {

    public function process_finish(question_attempt_pending_step $pendingstep) {
        global $DB, $USER;

        $keep = parent::process_finish($pendingstep);
        $fraction = $pendingstep->get_fraction();
        $penalty = $this->question->penalty;
        // To reduce the penalty slightly uncomment following.
        // $penalty = $penalty / (1.0 + $penalty);
        // $adjustment = 1.0 - $penalty;

        $current_user_quiz_attempt = $DB->get_record_sql('SELECT * FROM `mdl_quiz_attempts` WHERE `userid` = '.$USER->id.' 
        AND `uniqueid` = '.$this->qa->get_usage_id().' ORDER BY `mdl_quiz_attempts`.`attempt` DESC LIMIT 1');
        // Attempt 1 => Do not use penalty.
        // Attempt 2 => Use penalty once.
        // Attempt 3 => Use penalty twice.
        $attempt_penalty = ($current_user_quiz_attempt->attempt - 1) * $penalty;
        $adjustment = 1.0 - $attempt_penalty;

        if ($keep == question_attempt::KEEP &&
                $fraction != null) {
            $pendingstep->set_fraction($fraction * $adjustment);
        }

        $this->qa->set_metadata('penalty', $attempt_penalty);

        return $keep;
    }

}

