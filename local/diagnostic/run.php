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
define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
global $CFG, $DB;

require_once($CFG->libdir.'/clilib.php');      // cli only functions

$usage = "Runs the cluster cache script with selected cmids.

Usage:
    # php run.php [--cmids=<cmids>][-r]
    # php run.php [--help|-h]

Options:
    -h --help                   Print this help.
    <cmids> - course module ids, separated with comma

Examples:

    # php run.php
        Does nothing

    # php run.php --cmids=12
        recaches course module id = 12

    # php run.php --cmids=12,13
        recaches two course modules with id = 12 and id = 13

    # php run.php --cmids=12 -r
        recaches course module id = 12 and forces full rebuild on it

    # php run.php --help
    # php run.php -h
        Prints this help
";

list($options, $unrecognised) = cli_get_params(
    [
        'cmids' => '',
        'help' => false,
        'time' => null,
    ], [
        'h' => 'help',
        'r' => 'rebuild'
    ]
);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if (isset($options['help']) && $options['help'] !== false) {
    cli_writeln($usage);
    exit(2);
}

$cmids = explode(',', $options['cmids']) ?: [];
$metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

foreach ($cmids as $cmid) {
    $mid = $DB->get_field('local_metadata', 'data', ['instanceid' => $cmid, 'fieldid' => $metadatafieldid]);
    $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);

    if (isset($options['rebuild'])) {
        $rebuild = true;
    } else {
        $rebuild = $cache ? $cache->get('rebuild') : 1;
    }

    $params['recache'] = true;
    $params['rdebug'] = false;
    $params['cache'] = $cache;
    $params['rebuild'] = $rebuild;
    $params['mid'] = $mid;
    $params['cmids'] = [$cmid];
    $params['cmid'] = $cmid;

    if (!$parentcategoryid = \community_oer\main_oer::get_oer_category()) {
        $parentcategoryid = 0;
    }

    $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

    $params['repocmid'] = $DB->get_field_sql('SELECT lm.instanceid FROM {local_metadata} lm
                                                JOIN {course_modules} cm ON (cm.id = lm.instanceid) 
                                                JOIN {course} c ON (cm.course = c.id) 
                                                JOIN {course_categories} AS cc ON c.category = cc.id
                                                JOIN {modules} AS m ON m.id = cm.module
                                                WHERE cc.id IN (SELECT id FROM {course_categories} WHERE parent = ?) AND m.name = "quiz"
                                                AND lm.fieldid = ? AND lm.data = ? AND cm.id IS NOT NULL LIMIT 1', [$parentcategoryid, $metadatafieldid, $mid]);
    if ($options['time']) {
        $params['buildtime'] = $options['time'];
        \local_diagnostic_external::process($params);
        unset($params['buildtime']);
        $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);
        $params['cache'] = $cache;
    }

    \local_diagnostic_external::process($params);
}