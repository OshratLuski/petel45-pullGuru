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
 * Upgrade library code for the diagnosticadvdesc question type.
 *
 * @package    qtype_diagnosticadvdesc
 * @subpackage diagnosticadvdesc
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class for converting attempt data for diagnosticadvdesc questions when upgrading
 * attempts to the new question engine.
 *
 * This class is used by the code in question/engine/upgrade/upgradelib.php.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_diagnosticadvdesc_qe2_attempt_updater extends question_qtype_attempt_updater {
    /**
     * Returns the correct answer for the question.
     *
     * @return string The correct answer
     */
    public function right_answer() {
        return '';
    }

    /**
     * Checks if the question was answered.
     *
     * @param object $state The question state
     * @return bool True if answered, false otherwise
     */
    public function was_answered($state) {
        return false;
    }

    /**
     * Provides a summary of the response.
     *
     * @param object $state The question state
     * @return string The response summary
     */
    public function response_summary($state) {
        return '';
    }

    /**
     * Sets data elements for the first step of the question attempt.
     *
     * @param object $state The question state
     * @param array &$data The data array to populate
     */
    public function set_first_step_data_elements($state, &$data) {
    }

    /**
     * Supplies missing data for the first step if needed.
     *
     * @param array &$data The data array to populate
     */
    public function supply_missing_first_step_data(&$data) {
    }

    /**
     * Sets data elements for a specific step of the question attempt.
     *
     * @param object $state The question state
     * @param array &$data The data array to populate
     */
    public function set_data_elements_for_step($state, &$data) {
    }
}
