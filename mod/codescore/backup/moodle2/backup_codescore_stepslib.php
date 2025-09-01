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
 * Define all the backup steps that will be used by the backup_codescore_activity_task
 */
class backup_codescore_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $codescore = new backup_nested_element('codescore', array('id'), array(
            'course', 'name', 'intro', 'task',
            'programminglang', 'gradingspec', 'syntaxgrading', 'outputgrading',
            'problemsolutiongrading', 'allcasesgrading', 'commentsgrading', 'reduntatcodegrading',
            'algorithmgrading', 'resourcecompgrading', 'complexitygrading', 'autograde',
            'multiattempts', 'duedate', 'timecreated', 'timemodified',
            'timeopened', 'timeclosed',
        ));
        $attempts = new backup_nested_element('attempts');

        $attempt = new backup_nested_element('attempt', array('id'), array(
            'codescore', 'userid', 'attempt',
            'state', 'timestart', 'timefinish', 'timemodified',
            'timemodifiedoffline', 'timecheckstate', 'code', 'studentnotes',
            'grade', 'aigrade', 'syntaxgrading', 'outputgrading',
            'problemsolutiongrading', 'allcasesgrading', 'correctedcode', 'output',
            'gradednotificationsenttime',
        ));

        $codescore->add_child($attempts);
        $attempts->add_child($attempt);

        $codescore->set_source_table('codescore', array('id' => backup::VAR_ACTIVITYID));
        $attempt->set_source_sql('
            SELECT *
              FROM {codescore_attempts}
             WHERE codescore = ?',
            array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $attempt->set_source_table('codescore_attempts', array('id' => '../../id'));
        }

        return $this->prepare_activity_structure($codescore);
    }
}
