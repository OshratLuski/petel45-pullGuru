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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/codescore/backup/moodle2/backup_codescore_stepslib.php');
class backup_codescore_activity_task extends backup_activity_task {
      /**
       * No specific settings for this activity
       */
    protected function define_my_settings() {
    }

     /**
      * Defines backup steps to store the instance data and required questions
      */
    protected function define_my_steps() {
        $this->add_step(new backup_codescore_activity_structure_step('codescore_structure', 'codescore.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    public static function encode_content_links($content) {
        return $content;
    }

}
