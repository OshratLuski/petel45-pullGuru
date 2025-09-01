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
 * @package    block_enrol_key
 * @copyright  Matan berkovitch <matan.berkovitch@weizmann.ac.il>
 */
class block_enrol_key extends block_base {

    /**
     * block initializations
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_enrol_key');
    }

    public function get_content() {
        global $CFG;
        if ($this->content !== null) {
            return $this->content;
        }

        $text = '
            <div id="enrolkeyform" class="d-block">
                <form class="form-group d-flex align-items-center mb-0" action="' . $CFG->wwwroot . '/enrol/self/enrolwithkey.php" method="post">
                
                    <input class="enrolkey-input mr-2" type="text" id="enrolkey" name="enrolkey" size="10" maxlength="15"
                            placeholder="' . get_string('enrol_key', 'block_enrol_key') . '">
                            
                    <input type="hidden" name="sesskey" value="'.sesskey().'">
                            
                    <input class="btn btn-primary enrolkey-submit-btn" type="submit" 
                            value="' . get_string('enrol_me', 'block_enrol_key') . '">
                </form>
            </div>';

        $this->content = new stdClass;
        $this->content->text = $text;
        return $this->content;

    }

    public function applicable_formats() {
        return array('all' => false, 'mod' => false, 'my' => true);
    }
}
