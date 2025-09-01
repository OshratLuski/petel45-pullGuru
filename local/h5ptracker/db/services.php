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
 * @subpackage h5ptracker
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author 2021 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [

        'local_h5ptracker_track_actions' => [
                'classname' => 'local_h5ptracker\external',
                'methodname' => 'track_actions',
                'description' => 'Track actions',
                'type' => 'write',
                'enabled' => 1,
                'ajax' => true
        ],
];
