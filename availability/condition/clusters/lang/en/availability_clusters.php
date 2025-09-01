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
 * Language strings.
 *
 * @package availability
 * @subpackage clusters
 * @copyright  2022 Devlion.co <info@devlion.co>
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['description'] = 'Allow only students who belong to specified clusters';
$string['missing'] = '(Missing clusters)';
$string['pluginname'] = 'Restriction by clusters';
$string['error_selectclusters'] = 'You must select cluster(s).';
$string['requires_clusters'] = 'You belong to <strong>{$a}</strong>';
$string['requires_notanyclusters'] = 'You do not belong to any clusters';
$string['requires_notclusters'] = 'You do not belong to <strong>{$a}</strong>';
$string['clustername'] = 'Activity <strong>{$a->name}</strong> - Attempt <strong>{$a->attempt}</strong> - Cluster <strong>{$a->clusternum}</strong>';
$string['studentclustername'] = '<strong>{$a->name}</strong> Cluster <strong>{$a->clusternum}</strong>';
$string['title'] = 'Cluster';

$string['privacy:metadata'] = 'The Restriction by clusters plugin does not store any personal data.';
