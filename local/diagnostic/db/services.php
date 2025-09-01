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
 * external functions and service definitions.
 *
 * @package    local
 * @subpackage diagnostic
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'local_diagnostic_get_quizzes' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'get_quizzes',
        'description' => 'Get quizzes',
        'type'        => 'read',
        'enabled'     => 1,
        'ajax'          => true,
        'readonlysession' => true,
    ),
    'local_diagnostic_get_all_course_quizzes' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'get_all_course_quizzes',
        'description' => 'Get all quizzes for course with sections',
        'type'        => 'read',
        'enabled'     => 1,
        'ajax'          => true,
        'readonlysession' => true,
    ),
    'local_diagnostic_get_clusters' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'get_clusters',
        'description' => 'Get clusters',
        'type'        => 'read',
        'enabled'     => 1,
        'ajax'          => true,
        'readonlysession' => true,
    ),
    'local_diagnostic_set_local_clusters' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'set_local_clusters',
        'description' => 'Set clusters revisions for availability',
        'type'        => 'write',
        'enabled'     => 1,
        'ajax'          => true
    ),
    'local_diagnostic_set_sharewith_clusters' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'set_sharewith_clusters',
        'description' => 'Set sharewith clusters revisions for availability',
        'type'        => 'write',
        'enabled'     => 1,
        'ajax'          => true
    ),
    'local_diagnostic_user_dragdrop' => array(
        'classname'   => 'local_diagnostic_external',
        'methodname'  => 'user_dragdrop',
        'description' => 'Trigger event for user dragdrop',
        'type'        => 'write',
        'enabled'     => 1,
        'ajax'          => true
    ),
    'local_diagnostic_user_table' => array(
        'classname' => 'local_diagnostic_external',
        'methodname' => 'user_table',
        'description' => 'Trigger event for user when open table',
        'type' => 'write',
        'enabled' => 1,
        'ajax' => true
    )
);
