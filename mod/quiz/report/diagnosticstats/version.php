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
 * Quiz diagnostic statistics report for diagnostic question.
 *
 * @package   quiz_diagnosticstats
 * @copyright 2024 Oshrat Luski <oshrat.luski@weizmann.ac.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024072301; 
$plugin->requires  = 2021051700;
$plugin->component = 'quiz_diagnosticstats';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1';