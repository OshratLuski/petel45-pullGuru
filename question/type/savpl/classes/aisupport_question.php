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
 * @package    qtype_savpl
 * @copyright  2024 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_savpl;

defined('MOODLE_INTERNAL') || die();

class aisupport_question
{
    var $question;
    var int $userid;
    var int $courseid;

    public function __construct($questionid) {
        global $CFG;
        require_once($CFG->dirroot . '/question/engine/bank.php');
        $this->question = \question_bank::load_question($questionid);
    }

    public function get_full_prompt($studentprompt) {
        $sysprompt = get_config(SAQVPL, 'systemprompt');
        //$prompt = !empty($this->question->aiteacherprompt) ? str_replace('[[request]]', $studentprompt, $this->question->aiteacherprompt) : $studentprompt;

        return $sysprompt .' '. $this->question->aiteacherprompt . ' ' . $studentprompt;
    }
}


