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
 * @package local_diagnostic
 * @copyright 2021 Devlion.co
 * @author Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin->version   = 2024082001;
$plugin->release   = '1.0';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->requires  = 2020082200;
$plugin->dependencies = [
    'local_clusters' => 2022071600,
    'local_community' => ANY_VERSION,
];
$plugin->component = 'local_diagnostic';
