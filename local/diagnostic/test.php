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
 * Run the diagnostic for selected activities.
 *
 * @package    local_diagnostic
 * @copyright  2021 Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$clustercount = 0;
exec("python3 /var/www/m39/moodles/dev2/local/diagnostic/scripts/find_optimal.py /var/www/m39/moodles/dev2/local/diagnostic/scripts/response-matrix-example-clean.csv 4", $optimal_output);
if (is_array($optimal_output) && !empty($optimal_output)) {
    $firstline = array_shift($optimal_output);
    $optimal_output = json_decode($firstline);
    if (is_array($optimal_output) && !empty($optimal_output)) {
        $optimal_output = array_shift($optimal_output);
        if (is_array($optimal_output) && !empty($optimal_output)) {
            $clustercount = array_shift($optimal_output);
        }
    }
}

echo '<pre>';
var_dump($clustercount);
echo '</pre>';
die();
