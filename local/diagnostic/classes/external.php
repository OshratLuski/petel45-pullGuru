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
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021 Devlion.co
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

use local_diagnostic\KMeans;
use local_diagnostic\PHPR\RCore;
use local_diagnostic\PHPR\Engine\CommandLineREngine;
use mod_quiz\quiz_settings;

/**
 * @package local_diagnostic
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */
class local_diagnostic_external extends external_api {

    static $source_filename = 'response-matrix.csv';
    static $result_filename = 'students_petel.csv';
    static $Rscript_name = 'Kmeans.R';
    static $optimal_name = 'find_optimal.py';
    static $important_name = 'important_questions.py';
    static $gapestimateRscript_name = 'GapEstimateKmeans.R';

    const DEFAULT_REBUILD_LIMIT = 350;
    const DEFAULT_PATHTOPYTHON = 'python3';
    const CLUSTERNUM_FIXED = 'fixed';
    const CLUSTERNUM_GAPESTIMATE = 'gapestimate';
    const CLUSTERNUM_OPTIMAL = 'optimal';
    const CLUSTERNUM = 5;
    const NMAX = 4;
    const NMIN = 1;
    const DEFAULTCUTOFF = 0.9;

    public static function user_table_parameters()
    {
        return new external_function_parameters(
            [
                'cmid' => new external_value(PARAM_INT, 'course module id'),
                'mid' => new external_value(PARAM_INT, 'Mid of the activity')
            ]
        );
    }

    public static function user_table($cmid, $mid)
    {
        global $USER;

        $return = [
            'result' => true,
            'message' => ''
        ];

        $params = self::validate_parameters(self::user_table_parameters(),
            [
                'cmid' => $cmid,
                'mid' => $mid
            ]
        );

        try {
            // Trigger event, local clusters submitted.
            $context = context_module::instance($params['cmid']);

            $eventparams = [
                'relateduserid' => $USER->id,
                'userid' => $USER->id,
                'context' => $context,
                'other' => [
                    'cmid' => $cmid,
                    'mid' => $mid
                ]
            ];

            $event = \local_diagnostic\event\user_table::create($eventparams);
            $event->trigger();

        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage() . $e->getTraceAsString()
            ];
        }

        return $return;
    }

    public static function user_table_returns()
    {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'Result'),
                'message' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            ]
        );
    }


    public static function user_dragdrop_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'dragged user id'),
                'cmid' => new external_value(PARAM_INT, 'course module id'),
                'clusternumfrom' => new external_value(PARAM_INT, 'drag from cluster number'),
                'clusternumto' => new external_value(PARAM_INT, 'drag to cluster number'),
            ]
        );
    }

    public static function user_dragdrop($userid, $cmid, $clusternumfrom, $clusternumto) {
        global $DB, $USER;

        $return = [
            'result' => true,
            'message' => ''
        ];

        $params = self::validate_parameters(self::user_dragdrop_parameters(),
            [
                'userid' => $userid,
                'cmid' => $cmid,
                'clusternumfrom' => $clusternumfrom,
                'clusternumto' => $clusternumto,
            ]
        );

        try {
            // Trigger event, local clusters submitted.
            $context = context_module::instance($params['cmid']);

            $eventparams = [
                'relateduserid' => $USER->id,
                'context' => $context,
                'other' => [
                    'userid' => $params['userid'],
                    'clusternumfrom' => $params['clusternumfrom'],
                    'clusternumto' => $params['clusternumto'],
                ]
            ];

            $event = \local_diagnostic\event\user_dragdrop::create($eventparams);
            $event->trigger();
        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage() . $e->getTraceAsString()
            ];
        }

        return $return;
    }

    public static function user_dragdrop_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            ]
        );
    }

    public static function set_local_clusters_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'source' => new external_value(PARAM_ALPHA, 'source'),
                'sourcecmid' => new external_value(PARAM_INT, 'source cmid'),
                'mid' => new external_value(PARAM_INT, 'repository id'),
                'attempt' => new external_value(PARAM_INT, 'attempt id'),
                'data' => new external_multiple_structure(
                    new external_single_structure (
                        [
                            'cmids' => new external_multiple_structure (
                                new external_value(PARAM_INT, 'course module id')
                            ),
                            'userids' => new external_multiple_structure (
                                new external_value(PARAM_INT, 'user id')
                            ),
                            'description' => new external_value(PARAM_TEXT, 'cluster description'),
                            'recommend' => new external_value(PARAM_BOOL, 'cluster recommended'),
                            'clusternum' => new external_value(PARAM_INT, 'cluster number'),
                            'type' => new external_value(PARAM_TEXT, 'type of exec'),
                            'question' => new external_value(PARAM_INT, 'question id'),
                        ]
                        , '', VALUE_OPTIONAL)
                )
            ]
        );
    }

    public static function set_local_clusters($courseid, $source, $sourcecmid, $mid, $attempt, $data) {
        global $USER;

        $return = [
            'result' => true,
            'message' => ''
        ];

        $params = self::validate_parameters(self::set_local_clusters_parameters(),
            [
                'courseid' => $courseid,
                'source' => $source,
                'sourcecmid' => $sourcecmid,
                'mid' => $mid,
                'attempt' => $attempt,
                'data' => $data,
            ]
        );

        try {
            $other = ['cmid' => $sourcecmid, 'mid' => $mid, 'source' => $source];
            foreach ($params['data'] as $clusterdata) {
                if (!empty($clusterdata['userids']) && !empty($clusterdata['cmids'])) {
                    $other['coursecmids'] = $clusterdata['cmids'];
                    foreach ($clusterdata['cmids'] as $cmid) {
                        \local_clusters\clusters::add_cluster(
                            $params['courseid'],
                            $source,
                            $params['sourcecmid'],
                            $params['mid'],
                            $cmid,
                            $clusterdata['clusternum'],
                            $params['attempt'],
                            $clusterdata['description'],
                            $clusterdata['recommend'],
                            $clusterdata['userids'],
                            $clusterdata['type'],
                            $clusterdata['question']
                        );
                    }
                }
            }

            // Trigger event, local clusters submitted.
            $context = context_module::instance($sourcecmid);

            $eventparams = [
                'relateduserid' => $USER->id,
                'context' => $context,
                'other' => $other,
            ];

            $event = \local_diagnostic\event\local_clusters_submitted::create($eventparams);
            $event->trigger();
        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage() . $e->getTraceAsString()
            ];
        }

        return $return;
    }

    public static function set_local_clusters_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            ]
        );
    }

    public static function set_sharewith_clusters_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'sourcecmid' => new external_value(PARAM_INT, 'source cmid'),
                'source' => new external_value(PARAM_ALPHA, 'source'),
                'mid' => new external_value(PARAM_INT, 'repository id'),
                'attempt' => new external_value(PARAM_INT, 'attempt id'),
                'data' => new external_multiple_structure(
                    new external_single_structure (
                        [
                            'cmids' => new external_multiple_structure (
                                new external_value(PARAM_INT, 'course module id')
                            ),
                            'userids' => new external_multiple_structure (
                                new external_value(PARAM_INT, 'user id')
                            ),
                            'description' => new external_value(PARAM_TEXT, 'cluster description'),
                            'recommend' => new external_value(PARAM_BOOL, 'cluster recommend'),
                            'clusternum' => new external_value(PARAM_INT, 'cluster number'),
                            'type' => new external_value(PARAM_TEXT, 'type of exec'),
                            'question' => new external_value(PARAM_INT, 'question id'),
                        ]
                        , '', VALUE_OPTIONAL)
                )
            ]
        );
    }

    public static function set_sharewith_clusters($courseid, $sourcecmid, $source, $mid, $attempt, $data) {
        global $CFG, $DB, $USER;

        $return = [
            'result' => true,
            'message' => ''
        ];

        $params = self::validate_parameters(self::set_sharewith_clusters_parameters(),
            [
                'courseid' => $courseid,
                'sourcecmid' => $sourcecmid,
                'source' => $source,
                'mid' => $mid,
                'attempt' => $attempt,
                'data' => $data,
            ]
        );

        try {
            $other = ['cmid' => $sourcecmid, 'mid' => $mid, 'source' => $source];
            foreach ($params['data'] as $clusterdata) {
                if (!empty($clusterdata['userids']) && !empty($clusterdata['cmids'])) {
                    $other['coursecmids'] = $clusterdata['cmids'];
                    $targetsectionid = $DB->get_field('course_modules', 'section', ['id' => $params['sourcecmid']]);

                    foreach ($clusterdata['cmids'] as $cmid) {
                        if (class_exists('\community_sharewith\funcs')) {
                            list($modrec, $cmrec) = get_module_from_cmid($cmid);

                            $metadata = json_encode(
                                [
                                    'clusterdata' => [
                                        'courseid' => $params['courseid'],
                                        'sourcecmid' => $params['sourcecmid'],
                                        'source' => $params['source'],
                                        'clusternum' => $clusterdata['clusternum'],
                                        'attempt' => $params['attempt'],
                                        'mid' => $params['mid'],
                                        'description' => $clusterdata['description'],
                                        'recommend' => $clusterdata['recommend'],
                                        'userids' => $clusterdata['userids'],
                                        'newactivitycompetencies' => '',
                                        'type' => $clusterdata['type'],
                                        'question' => $clusterdata['question'],
                                    ],
                                    'callbackfunc' => 'local_diagnotic_external_sharewith_callback_clusters',
                                    'callbackpath' => '/local/diagnostic/classes/external.php'
                                ]
                            );

                            \community_sharewith\funcs::add_task(
                                    'activitycopy',
                                    $USER->id,
                                    $USER->id,
                                    null,
                                    $params['courseid'],
                                    null,
                                    $targetsectionid,
                                    null,
                                    $cmid,
                                    $metadata
                            );
                        }
                    }
                }
            }

            // Trigger event, local clusters submitted.
            $context = context_module::instance($sourcecmid);

            $eventparams = [
                'relateduserid' => $USER->id,
                'context' => $context,
                'other' => $other,
            ];

            $event = \local_diagnostic\event\local_clusters_submitted::create($eventparams);
            $event->trigger();
        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage() . $e->getTraceAsString()
            ];
        }

        return $return;
    }

    public static function set_sharewith_clusters_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            ]
        );
    }

    public static function get_quizzes_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'daterange' => new external_value(PARAM_RAW, 'date range', VALUE_OPTIONAL),
            ]
        );
    }


    public static function get_quizzes($courseid, $daterange = false) {
        global $PAGE, $USER;
        $config = get_config('local_diagnostic');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_quizzes_parameters(),
            ['courseid' => $courseid, 'daterange' => $daterange]);

        $return = $config->demomode
            ? static::get_all_course_quizzes($params['courseid'], true)
            : static::get_quizzes_for_template($params);

        $context = context_course::instance($courseid);

        // Trigger event, popup loaded.
        $eventparams = [
            'relateduserid' => $USER->id,
            'context' => $context
        ];

        $event = \local_diagnostic\event\popup_loaded::create($eventparams);
        $event->trigger();

        return $return;
    }

    public static function get_quizzes_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
                'templatedata' => new external_single_structure (
                    [
                        'daterange' => new external_value(PARAM_RAW, 'date range submitted', VALUE_OPTIONAL),
                        'daterangesubmitted' => new external_value(PARAM_BOOL, 'date range submitted flag', VALUE_OPTIONAL),
                        'sections' => new external_multiple_structure(
                            new external_single_structure (
                                [
                                    'quizzes' => new external_multiple_structure(
                                        new external_single_structure (
                                            [
                                                'cmid' => new external_value(PARAM_ALPHANUMEXT, 'course module id'),
                                                'name' => new external_value(PARAM_TEXT, 'quiz name'),
                                                'type' => new external_value(PARAM_TEXT, 'quiz type'),
                                            ]
                                        )
                                        , '', VALUE_OPTIONAL),
                                    'name' => new external_value(PARAM_TEXT, 'section name', VALUE_OPTIONAL),
                                ]
                            )
                        )
                    ], '', VALUE_OPTIONAL
                )
            ]
        );
    }

    public static function get_all_course_quizzes_parameters() {
        return new external_function_parameters(
            [
                'courseid' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }


    public static function get_all_course_quizzes($courseid, $demomode = false) {
        global $PAGE;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_all_course_quizzes_parameters(),
            ['courseid' => $courseid]);
        return static::get_all_course_cms_for_template($courseid, $demomode);
    }

    public static function get_all_course_quizzes_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
                'templatedata' => new external_single_structure (
                    [
                        'sections' => new external_multiple_structure(
                            new external_single_structure (
                                [
                                    'quizzes' => new external_multiple_structure(
                                        new external_single_structure (
                                            [
                                                'cmid' => new external_value(PARAM_ALPHANUMEXT, 'course module id'),
                                                'name' => new external_value(PARAM_TEXT, 'quiz name'),
                                            ]
                                        )
                                        ,'', VALUE_OPTIONAL),
                                    'name' => new external_value(PARAM_TEXT, 'section name', VALUE_OPTIONAL),
                                ]
                            )
                        )
                    ], '', VALUE_OPTIONAL
                )
            ]
        );
    }

    public static function get_clusters_parameters() {
        return new external_function_parameters(
            [
                'cmids' => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'mid + cm id + quiz id + attempt num')
                ),
                'courseid' => new external_value(PARAM_INT, 'course id', VALUE_OPTIONAL),
            ]
        );
    }


    public static function get_clusters($cmids, $courseid) {
        global $PAGE, $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_clusters_parameters(),
            ['courseid' => $courseid, 'cmids' => $cmids]);

        list($params['mid'], $params['cmid'], $params['filterqid'], $params['filterattempt'], $params['type']) = explode('-', reset($params['cmids']));
        $params['cmids'] = [$params['cmid']];
        $PAGE->set_context(\context_module::instance($params['cmid']));
        $funcname = get_config('local_diagnostic', 'demomode') ? 'get_clusters_json_demo' : 'get_clusters_json';
        try {
            $json = static::$funcname($params);
            $result = true;
            $message = '';

            // Trigger event, clusters loaded.
            $context = context_module::instance($params['cmid']);

            $eventparams = [
                'relateduserid' => $USER->id,
                'context' => $context,
                'other' => ['cmid' => $params['cmid']]
            ];

            $event = \local_diagnostic\event\clusters_loaded::create($eventparams);
            $event->trigger();

        } catch (\Exception $e) {
            $json = '';
            $result = false;
            $message = $e->getMessage();
        }

        return ['result' => $result, 'message' => $message, 'json' => $json];
    }

    public static function get_clusters_returns() {
        return new external_single_structure(
            [
                'result'    => new external_value(PARAM_BOOL, 'Result'),
                'message'    => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
                'json'    => new external_value(PARAM_RAW, 'clusters json', VALUE_OPTIONAL),
            ]
        );
    }

    public static function get_quizzes_for_template($params) {
        global $DB, $CFG;

        $config = get_config('local_diagnostic');
        $courseid = $params['courseid'];
        $quizcache = \cache::make('local_diagnostic', 'quizzes');
        $quizzesforcourse = $quizcache->get($courseid);
        if (isset($quizzesforcourse['templatedata']['sections']) && !empty($quizzesforcourse['templatedata']['sections'])) {
            return $quizzesforcourse;
        }

        require_once($CFG->dirroot . '/course/lib.php');
        $return = [
            'result' => true,
            'message' => get_string('success', 'local_diagnostic'),
            'templatedata' => [
                'daterange' => $params['daterange'] ?? '0-0',
                'sections' => []
            ]
        ];
        try {
            $where = $join = '';
            $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
            if (!$metadatafieldid) {
                $quizcache->set($courseid, $return);
                return $return;
            }
            $requestparams = ['quiz' => 'quiz', 'fieldid' => $metadatafieldid];

            if (isset($params['daterange']) && !empty($params['daterange'])) {
                list($start, $end) = explode('-', $params['daterange']);
                if (empty($start) || empty($end)) {
                    $return['result'] = false;
                    $return['message'] = get_string('nostartend', 'local_diagnostic');
                    $quizcache->set($courseid, $return);
                    return $return;
                }

                $requestparams['timestart'] = strtotime(trim($start));
                $requestparams['timeend'] = strtotime(trim($end));
                $where = 'AND q.id IN (SELECT DISTINCT quiz FROM {quiz_attempts} WHERE timemodified BETWEEN :timestart AND :timeend)';
            }

            /*
            if ($config->enabletags) {
                if ($config->allowedtags) {
                    $tags = explode(',', $config->allowedtags);
                    $tags = array_map('trim', $tags);
                    list($tagwhere, $tagparams) = $DB->get_in_or_equal($tags, SQL_PARAMS_NAMED, 'tagname');
                    $join = "JOIN {tag_instance} ti ON (ti.itemid = cm.id AND itemtype = 'course_modules') JOIN {tag} t ON (ti.tagid = t.id)";
                    $where .= ' AND t.name ' . $tagwhere;
                    $requestparams = array_merge($requestparams, $tagparams);
                } else {
                    return $return;
                }
            }
            */

            if (isset($courseid) && !empty($courseid)) {
                $requestparams['courseid'] = $courseid;
                $where  .= ' AND q.course = :courseid';
            }

            $quizzes = $DB->get_records_sql("
            SELECT cm.id as cmid, cs.id as csid, cs.name as csname, cs.section, q.name, q.id FROM {quiz} q
            LEFT JOIN {course_modules} cm ON (q.id = cm.instance 
                AND cm.module = (SELECT id FROM {modules} m WHERE m.name = :quiz))
            LEFT JOIN {local_metadata} lm ON (lm.instanceid = cm.id)       
            LEFT JOIN {course_sections} cs ON (cm.section = cs.id) $join
            WHERE lm.fieldid = :fieldid AND EXISTS(SELECT * FROM {quiz_attempts} WHERE quiz = q.id)
                $where ORDER BY csid ASC", $requestparams);
        } catch (\Exception $e) {
            $quizcache->set($courseid, $return);
            return $return;
        }

        $croncustommids = array_map(function($item) {
                return trim($item);
            },
            explode(',', $config->croncustommids)
        );

        if (isset($courseid) && !empty($courseid)) {
            if ($quizzes) {
                $return['templatedata']['daterangesubmitted'] = true;
                $return['templatedata']['sections'] = [];
                foreach ($quizzes as $quiz) {
                    $mids = static::get_mids($metadatafieldid, [$quiz->cmid]);
                    if (empty($mids)) {
                        //Should never happen
                        continue;
                    }
                    $mid = array_shift($mids);
                    if (!$cache = \local_diagnostic\cache::get_record(['mid' => $mid])) {
                        //we have no cache data for this quiz - skip it
                        continue;
                    }

                    if (!$cache->get('readytouse')) {
                        //not ready to show yet
                        //continue;
                    }

                    if (!in_array($mid, $croncustommids)) {
                        //not in available list
                        continue;
                    }

                    $activitydata = json_decode($cache->get('activities'), true);
                    $extradata = json_decode($cache->get('extra'), true);

                    if (isset($activitydata[$courseid][$quiz->id]) && !empty($activitydata[$courseid][$quiz->id])) {
                        //Actually this should always be true
                        if (!isset($return['templatedata']['sections'][$quiz->csid])) {
                            $return['templatedata']['sections'][$quiz->csid] = [
                                'name' => get_section_name($courseid, $quiz->section),
                                'quizzes' => []
                            ];
                        }
                        if ($config->severalattempts) {
                            foreach ($activitydata[$courseid][$quiz->id] as $unused => $attempt) {
                                $return['templatedata']['sections'][$quiz->csid]['quizzes'][] = [
                                    'cmid' => $mid . '-' . $quiz->cmid . '-' .$quiz->id . '-' . $attempt . '-mid',
                                    'name' => $quiz->name . ' ' . get_string('attempt', 'quiz', $attempt),
                                    'type' => ''
                                ];
                            }
                        } else {
                            $return['templatedata']['sections'][$quiz->csid]['quizzes'][] = [
                                'cmid' => $mid . '-' . $quiz->cmid . '-' .$quiz->id . '-0-mid',
                                'name' => $quiz->name,
                                'type' => ''
                            ];
                        }

                        if ($extradata) {
                            foreach ($extradata as $questionid => $questiondata) {
                                if (isset($questiondata['clusters'][$quiz->id])) {
                                    $quizclusters = $questiondata['clusters'][$quiz->id];
                                    $questionname = (reset($quizclusters))['name'];

                                    $a = (object) [
                                        'quizname' => $quiz->name,
                                        'questionname' => $questionname
                                    ];
                                    $return['templatedata']['sections'][$quiz->csid]['quizzes'][] = [
                                        'cmid' => $mid . '-' . $quiz->cmid . '-' .$quiz->id . '-' . $questionid . '-extra',
                                        'name' => get_string('mlnpmenutext', 'local_diagnostic', $a),
                                        'type' => 'mlnp'
                                    ];
                                }
                            }
                        }
                    }
                }

                $return['templatedata']['sections'] = array_values($return['templatedata']['sections']);
            }
        } else {
            $return = $quizzes;
        }

        $quizcache->set($courseid, $return);
        return $return;
    }

    public static function get_all_course_cms_for_template($courseid, $demomode = false) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/datalib.php');
        require_once($CFG->dirroot . '/course/lib.php');

        $return = [
            'result' => true,
            'message' => get_string('success', 'local_diagnostic'),
            'templatedata' => [
                'sections' => []
            ]
        ];
        try {
            $cms = get_course_mods($courseid);
            foreach ($cms as $cm) {
                if ($cm->modname <> 'label') {
                    $moduleinfo = get_coursemodule_from_instance($cm->modname, $cm->instance, $courseid, true);
                    if (!isset($return['templatedata']['sections'][$cm->section])) {
                        $return['templatedata']['sections'][$cm->section] = [
                            'name' => get_section_name($courseid, $moduleinfo->sectionnum),
                            'quizzes' => []
                        ];
                    }

                    $return['templatedata']['sections'][$cm->section]['quizzes'][] = [
                        'cmid' => $demomode ? $cm->id . '-' . $cm->id . '-' . $cm->instance . '-1' : $cm->id,
                        'name' => $moduleinfo->name
                    ];
                }
            }

            $return['templatedata']['sections'] = array_values($return['templatedata']['sections']);
        } catch (\Exception $e) {
            $return = [
                'result' => false,
                'message' => $e->getMessage(),
                'templatedata' => [
                    'sections' => []
                ]
            ];
        }

        return $return;
    }

    public static function get_clusters_json($params) {
        global $DB, $PAGE;

        $heb_group_name = explode(',', get_string('alphabet', 'langconfig'));
        $params['metadatafieldid'] = $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
        $config = get_config('local_diagnostic');
        $resultobject = new \stdClass();

        $params['question'] = $params['filterattempt'];

        if ($params['cmids']) {
            $clusters = static::get_cached_clusters_array($params);
            $results = $settings = $excludefromtable = [];

            $params['metadatafieldid'] = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
            $mid = $params['mid'];
            $cmid = $params['cmid'];
            $repocmid = static::get_repo_cmid($mid);
            if (static::has_custom_settings($mid)) {
                $settings = static::get_custom_settings($mid);
                $midurl = $midfilename = '';
                if (!empty($settings['customsettings']['midurl'])) {
                    $midurl = $settings['customsettings']['midurl'];
                } elseif (!empty($settings['customsettings']['midfile'])) {
                    $midfilename = $settings['customsettings']['midfile'];
                }
            }

            $allclusterprcsum = $allclusterprc = $allclusterprccount = 0;
            $params['questionid'] = $params['filterattempt'];
            if (!$config->severalattempts) {
                $params['filterattempt'] = 1;
            }

            foreach ($clusters['clusters'] as $i => $clusterdata) {
                $hasusers = false;
                $arrcluster = [];
                $arrcluster['id'] = $i;


                $lcparams = [
                        'sourcecmid' => $cmid,
                        'clusternum' => $i,
                        'attempt' => $params['filterattempt']
                ];

                if ($params['type'] == 'extra') {
                    $lcparams['type'] = $params['type'];
                    $lcparams['question'] = $params['question'];
                }

                $arrcluster['lcchosen'] = $arrcluster['lcdescription'] = '';


                if ($localclusters = \local_clusters\clusters::get_records($lcparams)) {
                    $arrcluster['lcchosen'] = count($localclusters);
                    $localcluster = array_pop($localclusters);
                    $arrcluster['lcdescription'] = $localcluster->get('description');
                }

                if ($params['type'] == 'mid') {

                    // Recommended.
                    $rcclusters = \local_clusters\clusters::get_records(
                        [
                            'clusternum' => $i,
                            'attempt' => $params['filterattempt'],
                            'mid' => $mid,
                            'recommend' => 1,
                        ],
                        'timemodified',
                        'DESC'
                    );

                    if ($rcclusters) {
                        $arrcluster['recommend'] = [];
                        foreach ($rcclusters as $rccluster) {
                            $user = core_user::get_user($rccluster->get('usermodified'));
                            $userpicture = new \user_picture($user);
                            $userpicture->size = 1; // Size f1.
                            $profileimageurl = $userpicture->get_url($PAGE)->out(false);

                            if ($rcmidcourseid = $DB->get_field('course_modules', 'course', ['id' => $rccluster->get('cmid')])) {
                                $rcmidcm = get_fast_modinfo($rcmidcourseid)->get_cm($rccluster->get('cmid'));

                                $arrcluster['recommend'][] = [
                                    'cmid' => $rccluster->get('cmid'),
                                    'fullname' => fullname($user),
                                    'userid' => $user->id,
                                    'activity' => $rcmidcm->__get('name'),
                                    'profileimageurl' => $profileimageurl,
                                    'date' => date('d/m/Y', $rccluster->get('timemodified')),
                                    'description' => $rccluster->get('description'),
                                    'source' => get_string('source_' . $rccluster->get('source'), 'local_clusters')
                                ];
                            }
                        }

                        //sort users by parameters
                        $sort = get_config('local_diagnostic', 'recommendedbyusers');
                        if (!empty($arrcluster['recommend']) && !empty($sort)) {
                            $newresult = [];
                            $userfirst = explode(',', $sort);
                            foreach ($userfirst as $userid) {
                                foreach ($arrcluster['recommend'] as $rkey => $recomendeduser) {
                                    if ($recomendeduser['userid'] == $userid) {
                                        $newresult[] = $recomendeduser;
                                        unset($arrcluster['recommend'][$rkey]);
                                    }
                                }
                            }

                            if ($newresult) {
                                $newresult = array_merge($newresult, $arrcluster['recommend']);
                                $arrcluster['recommend'] = $newresult;
                            }
                        }
                    }
                }

                $arrcluster['clustername'] = isset($heb_group_name[$i - 1]) ? get_string('clustername', 'local_diagnostic', $heb_group_name[$i - 1]) : get_string('clustername', 'local_diagnostic', $i);
                $arrcluster['clusternum'] = $i;

                $arrcluster['users'] = [];
                $clusterprcsum = $clusterprccount = 0;
                foreach ($clusterdata['users'] as $userdatakey => $userdata) {
                    if ($params['type'] == 'mid') {
                        list($userid, $qid, $attempt) = explode('-', $userdatakey);
                    } else {
                        $userid = $userdatakey;
                    }

                    if ($params['type'] <> 'mid' || ($qid == $params['filterqid'] && $attempt == $params['filterattempt'])) {
                        $hasusers = true;
                        $arrusers = [
                            'id' => get_string('uid', 'local_diagnostic', $userid),
                            'fullname' => $userdata['user']['fullname'],
                            'garde' => $userdata['prc']
                        ];
                        $arrcluster['users'][] = $arrusers;

                        //we only count non-zero clusters
                        if ($i > 0) {
                            $clusterprccount++;
                            $clusterprcsum += $userdata['prc'];

                            $allclusterprccount++;
                            $allclusterprcsum += $userdata['prc'];
                        }
                    }
                }

                $viewempty = $config->viewemptyclusters;

                if ($hasusers || $viewempty) {
                    $clusterprc = $clusterprccount > 0 ? round($clusterprcsum / $clusterprccount, 2) : 0;

                    $clusterinfo = isset($settings['customsettings']["cluster_" . $i . "_desc"]) &&
                    !empty($settings['customsettings']["cluster_" . $i . "_desc"])
                        ? '<div>' . get_string('avggrade', 'local_diagnostic', $clusterprc) . '</div>' .
                        '<p><strong>' . str_replace('"', "''", $settings['customsettings']["cluster_" . $i . "_desc"]) .
                        '</strong></p>'
                        : get_string('avggrade', 'local_diagnostic', $clusterprc);

                    $arrcluster['info'] = $arrcluster['text'] = $clusterinfo;
                    $results[] = $arrcluster;
                } else if (!$viewempty && $i == 0) {
                    //we fake zero cluster if we don't have one
                    $results[] = [
                        'id' => 0,
                        'users' => [],
                    ];
                } else if (!$viewempty) {
                    //PTL-7371 exclude corrsponding table cluster columns
                    // if there are no users for this CMID in corresponding clusters
                    $excludefromtable[] = $i;
                }
            }

            if ($allclusterprcsum) {
                $allclusterprc = round($allclusterprcsum / $allclusterprccount, 2);
            }

            $arrtable = [];

            foreach ($clusters['table'] as $mid => $clusterstabledata) {
                $arrtable[$mid] = [
                    'data' => [],
                    'name' => $clusterstabledata['name'] ?? '',
                    'repocmid' => $repocmid,
                    //TODO Maybe we should put more complicated logic here
                    'hasimportant' => $config->importantquestions
                ];

                foreach (array_values($clusterstabledata['data']) as $i => $tabledata) {
                    $tablekey = $i + 1;
                    if (isset($tabledata['tabledata']) && !in_array($tablekey, $excludefromtable)) {
                        if ($config->importantquestions) {
                            $importancearray = array_map(function($questiontabledata){
                                return $questiontabledata['importance'];
                            }, $tabledata['tabledata']);
                            arsort($importancearray);
                            $importancearray = array_slice($importancearray, 0, $config->importantnum);
                            foreach ($tabledata['tabledata'] as $questiontablekey => $questiontabledata) {
                                if ($questiontabledata['importance']) {
                                    $tabledata['tabledata'][$questiontablekey]['important'] = in_array($questiontabledata['importance'], $importancearray);
                                }
                            }
                        }

                        $arrtable[$mid]['data'][$tablekey] = [
                            'table' => $tabledata['tabledata'],
                            'avg' => $tabledata['prc'],
                            'clustername' => isset($heb_group_name[$i]) ? get_string('clustername', 'local_diagnostic', $heb_group_name[$i]) : get_string('clustername', 'local_diagnostic', $i)
                        ];
                    }
                }
            }

            $resultobject = new \stdClass();
            $resultobject->mid = $params['mid'];
            $resultobject->midurl = $midurl ?? '';
            $resultobject->midfilename = $midfilename ?? '';
            $resultobject->cmid = $params['cmid'];
            $resultobject->repocmid = $repocmid;
            $resultobject->attempt = $params['filterattempt'];
            $resultobject->questionid = $params['questionid'];
            $resultobject->type = $params['type'];
            $resultobject->clusters = $results;
            $resultobject->table = $arrtable;

            $resultobject->yellow = isset($settings['customsettings']['yellow']) && !empty($settings['customsettings']['yellow']) ?
                $settings['customsettings']['yellow'] : $config->yellow;
            $resultobject->green = isset($settings['customsettings']['green']) && !empty($settings['customsettings']['green']) ?
                $settings['customsettings']['green'] : $config->green;
            $resultobject->cmids = implode(',', $params['cmids']);
            $a = ['total' => $allclusterprc, 'totalall' => $clusters['total']['allprc']];
            $resultobject->total = ['info' => get_string('avggradetotal', 'local_diagnostic', $a)];
        }

        return json_encode($resultobject);
    }

    public static function get_clusters_json_demo($params) {
        global $DB, $PAGE;

        $results = [];
        $config = get_config('local_diagnostic');
        $randomcluster = static::get_clusternum();
        $heb_group_name = explode(',', get_string('alphabet', 'langconfig'));

        $usersnum = 10;
        $questions = rand(5, 20);
        $arrtable = [];
        for ($i=0; $i<$randomcluster; $i++){

            $arrcluster = [];
            $arrcluster['id'] = $i;

            $lcparams = [
                'sourcecmid' => $params['cmid'],
                'clusternum' => $i,
                'attempt' => $params['filterattempt']
            ];

            $arrcluster['lcchosen'] = $arrcluster['lcdescription'] = '';

            if ($localclusters = \local_clusters\clusters::get_records($lcparams)) {
                $arrcluster['lcchosen'] = count($localclusters);
                $localcluster = array_pop($localclusters);
                $arrcluster['lcdescription'] = $localcluster->get('description');
                $arrcluster['recommend'] = $localcluster->get('recommend');
            }

            // Recommended.
            $rcclusters = \local_clusters\clusters::get_records(
                [
                    'clusternum' => $i ,
                    'attempt' => $params['filterattempt'],
                    'mid' => $params['mid'],
                    'recommend' => 1,
                ],
                'timemodified',
                'DESC'
            );

            if ($rcclusters) {
                $arrcluster['recommend'] = [];
                foreach ($rcclusters as $rccluster) {
                    $user  = core_user::get_user($rccluster->get('usermodified'));
                    $userpicture = new \user_picture($user);
                    $userpicture->size = 1; // Size f1.
                    $profileimageurl = $userpicture->get_url($PAGE)->out(false);

                    if ($rcmidcourseid = $DB->get_field('course_modules', 'course', ['id' => $rccluster->get('mid')])) {
                        $rcmidcm = get_fast_modinfo($rcmidcourseid)->get_cm($rccluster->get('mid'));

                        $arrcluster['recommend'][] = [
                                'fullname' => fullname($user),
                                'activity' => $rcmidcm->__get('name'),
                                'cmid' => $rccluster->get('mid'),
                                'profileimageurl' => $profileimageurl,
                                'date' => date('d/m/Y', $rccluster->get('timemodified')),
                                'description' => $rccluster->get('description'),
                                'source' => get_string('source_' . $rccluster->get('source'), 'local_clusters')
                        ];
                    }
                }
            }

            $arrcluster['clustername'] = isset($heb_group_name[$i-1]) ? get_string('clustername', 'local_diagnostic', $heb_group_name[$i-1]) : get_string('clustername', 'local_diagnostic', $i-1);
            $arrcluster['clusternum'] = $i+1;
            $arrcluster['info'] = get_string('clusterinfo' . $i, 'local_diagnostic', rand(0,100));
            $arrcluster['text'] = get_string('clusterinfo' . $i, 'local_diagnostic', rand(0,100));
            $arrcluster['users'] = [];

            $users = rand(5, $usersnum);

            for ($u=1; $u<$users; $u++){
                $arrusers = [];
                $arrusers['id'] = get_string('uid', 'local_diagnostic', $u);
                $arrusers['fullname'] = "User" . $u . "  LastName" . $u;
                $arrusers['garde'] = 10;
                $arrcluster['users'][] = $arrusers;
            }



            for($mid=1;$mid<=2;$mid++) {
                if (!isset($arrtable[$mid])) {
                    $arrtable[$mid] = [
                        'name' => 'MID TITLE ' . $mid,
                        'data' => []
                    ];
                }

                $arrtable[$mid]['data'][$i] = [
                    'table' => [],
                    'avg' => 0,
                    'sum' => 0,
                    'count' => 0,
                    'clustername' => isset($heb_group_name[$i-1]) ? get_string('clustername', 'local_diagnostic', $heb_group_name[$i-1]) : get_string('clustername', 'local_diagnostic', $i-1)
                ];

                for ($question=1; $question<=$questions; $question++) {
                    $prc = rand(0, 100);
                    if ($prc < $config->yellow) {
                        $color = 'red';
                    } elseif ($prc >= $config->green) {
                        $color = 'green';
                    } else {
                        $color = 'yellow';
                    }
                    $key = $question . '-' . $question;
                    $arrtable[$mid]['data'][$i]['sum'] += $prc;
                    $arrtable[$mid]['data'][$i]['count']++;
                    $arrtable[$mid]['data'][$i]['avg'] = round($arrtable[$mid]['data'][$i]['sum'] / $arrtable[$mid]['data'][$i]['count'], 2);
                    $arrtable[$mid]['data'][$i]['table'][$key] = [
                        'color' => $color,
                        'prc' => $prc,
                        'questionnum' => $question,
                        'courseid' => $params['courseid'],
                        'qname' => get_string('questionname', 'local_diagnostic', $question)
                    ];
                }
            }

            $results[] = $arrcluster;
        }

        $resultobject = new \stdClass();
        $resultobject->mid = $params['mid'];
        $resultobject->cmid = $params['cmid'];
        $resultobject->attempt = $params['filterattempt'];
        $resultobject->clusters = $results;
        $resultobject->table = $arrtable;
        $resultobject->cmids = '';
        $a = ['total' => rand(0,100), 'totalall' => rand(0,100)];
        $resultobject->total = ['info' => get_string('avggradetotal', 'local_diagnostic', $a)];
        return json_encode($resultobject);
    }

    public static function get_cached_clusters_array($params) {
        global $DB;

        $return = [];

        $params['metadatafieldid'] = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
        $mids = static::get_mids($params['metadatafieldid'], $params['cmids']);

        $mid = array_shift($mids);

        if ($record = \local_diagnostic\cache::get_record(['mid' => $mid])) {
            switch ($params['type']) {
                case 'extra':
                    $extra = json_decode($record->get('extra'), true);
                    $quizid = $DB->get_field('course_modules', 'instance', ['id' => $params['cmid']]);
                    $return = [
                        'clusters' => $extra[$params['filterattempt']]['clusters'][$quizid],
                        'table' => $extra[$params['filterattempt']]['table'],
                        'total' => $extra[$params['filterattempt']]['total']
                    ];
                    break;
                default:
                    $return = json_decode($record->get('data'), true);
                    break;
            }
        }

        return $return;
    }

    public static function get_clusters_array($params) {
        global $DB, $CFG;

        $readytouse = 0;
        $return = [[], [], [], [], $readytouse, [], []];

        $onlyfromcourse = isset($params['onlyfromcourse']) && !empty($params['onlyfromcourse']) ? $params['onlyfromcourse'] : 0;
        $params['metadatafieldid'] = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

        if (!empty($params['cmids']) && !empty($params['metadatafieldid'])) {

            if (static::has_custom_settings($params['mid'])) {
                $params = array_merge($params, static::get_custom_settings($params['mid']));
            }

            $customsettings = $params['customsettings'] ?? [];

            list ($allkeys, $params['qids']) = static::get_qids($params);

            $qids = array_keys($params['qids']);

            if (empty($qids)){
                return $return;
            }

            //We check if there are new attempts
            $lasthours = $CFG->local_diagnostic_recache_time ?? 24;
            list ($qinsql, $qinparams) = $DB->get_in_or_equal($qids, SQL_PARAMS_NAMED, 'qids');
            $qinparams['timestart'] = time() - $lasthours*3600;
            $qinparams['timeend'] = time();

            $exclwhere = '';
            if (isset($customsettings['startdate']) && !empty($customsettings['startdate'])) {
                $qinparams['excltimestart'] = $customsettings['startdate'];
                if (isset($customsettings['enddate']) && !empty($customsettings['enddate'])) {
                    $qinparams['excltimeend'] = $customsettings['enddate'];
                    $exclwhere = 'AND NOT (qa.timefinish  BETWEEN :excltimestart AND :excltimeend)';
                } else {
                    $exclwhere = 'AND qa.timefinish < :excltimestart';
                }
            } elseif (isset($customsettings['enddate']) && !empty($customsettings['enddate'])) {
                $qinparams['excltimeend'] = $customsettings['enddate'];
                $exclwhere = 'AND qa.timefinish > :excltimeend';
            }

            $newattemptssql = "SELECT id FROM {quiz_attempts} qa WHERE qa.quiz $qinsql AND qa.timefinish BETWEEN :timestart AND :timeend $exclwhere";
            $newattempts = $DB->get_records_sql($newattemptssql, $qinparams);

            $params['hasnewattempts'] = !empty($newattempts);

            $currentcount = static::get_attempts_count($params['cache']);
            $config = get_config('local_diagnostic');

            $cachebuildtimestamp = static::get_cachebuild_timestamp($params);
            $rebuild = static::get_rebuild($currentcount, $params);

            list($userdata, $enrolled, $activitydata) = static::get_userdata($params, $allkeys);

            $centroids = [];

            $yellow = isset($customsettings['yellow']) && !empty($customsettings['yellow']) ? $customsettings['yellow'] : $config->yellow;
            $green = isset($customsettings['green']) && !empty($customsettings['green']) ? $customsettings['green'] : $config->green;
            $extra = $extracentroids = [];

            if (!empty($userdata)) {

                //$kmeans = new local_diagnostic\Clustering\KMeans\KMeans($clustercount, local_diagnostic\Clustering\KMeans\KMeans::INIT_KMEANS_MEDIAN);

                $readytouse = count($userdata) >= (count($allkeys) * 10) ? 1 : 0;

                $points = $avgcalcdata = $perqavg = $timestamps = $tocentroids = [];
                mtrace ('MID: ' . $params['mid'] . ' QUESTIONS: ' . count($allkeys) . ' USERDATA SIZE: ' . count($userdata));

                foreach ($userdata as $userdatakey => $data) {
                    $userdata[$userdatakey]['sum'] = $userdata[$userdatakey]['max'] = 0;
                    foreach ($allkeys as $uniquekey => $allkeysdata) {
                        if (!empty($allkeysdata['mid']) && !isset($perqavg[$allkeysdata['mid']])) {

                            $namecmid = $params['repocmid'] ?? $allkeysdata['mid'];
                            try {
                                list($modrec, $cmrec) = get_module_from_cmid($namecmid);
                            } catch (Exception $e) {
                                mtrace('CMID DOES NOT EXIST: ' . $namecmid);
                            }

                            $perqavg[$allkeysdata['mid']] = [
                                'data' => [],
                                'name' => $modrec->name
                            ];
                        }

                        $point = (isset($data['keys'][$uniquekey]['fraction']) && !empty($data['keys'][$uniquekey]['fraction'])) ? $data['keys'][$uniquekey]['fraction'] : 0;

                        if (isset($data['toclusters']) && !empty($data['toclusters'])) {
                            if (!isset($points[$userdatakey])) {
                                $points[$userdatakey] = [];
                            }
                            $points[$userdatakey][$uniquekey] = $point;
                        } else {
                            if (!isset($tocentroids[$userdatakey])) {
                                $tocentroids[$userdatakey] = [];
                            }
                            $tocentroids[$userdatakey][$uniquekey] = $point;
                        }

                        $timestamps[$userdatakey][$uniquekey] = isset($data['keys'][$uniquekey]['timecreated']) ? $data['keys'][$uniquekey]['timecreated'] : null;
                        $userdata[$userdatakey]['keys'][$uniquekey] = $point;
                        $userdata[$userdatakey]['sum'] += $point;
                    }

                    $userdata[$userdatakey]['prc'] = count($allkeys) ? round($userdata[$userdatakey]['sum'] / count($allkeys) * 100, 2) : 0;
                }

                $pluginmanager = core_plugin_manager::instance();
                $enabled = $pluginmanager->get_enabled_plugins('qtype');
                if ($enabled['mlnlpessay']) {
                    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
                    require_once($CFG->dirroot . '/question/type/mlnlpessay/lib.php');

                    if ($mquizid = $DB->get_field('course_modules', 'instance', ['id' => $params['mid']])) {
                        $quizobj = quiz_settings::create($mquizid);
                        $quizobj->preload_questions();
                        $quizobj->load_questions();
                        $questions = $quizobj->get_questions();
                        foreach ($questions as $question) {

                            if ($question->qtype == 'mlnlpessay') {
                                list($mlnpclusterdata, $mlnlpcentroids) = static::get_mlnlp_data($question, $params);
                                if ($mlnpclusterdata) {
                                    $extra[$question->id] = $mlnpclusterdata;
                                }

                                if ($mlnlpcentroids) {
                                    $extracentroids[$question->id] = $mlnlpcentroids;
                                }
                            }
                        }
                    }
                }

                if ($rebuild) {
                    //$clusters = $kmeans->cluster($points);
                    //echo ' POINTS: ' . count($points);
                    //echo ' TOCENTROIDS: ' . count($tocentroids);

                    list($clusters, $centroids, $importancedata) = static::Rcluster($points, $tocentroids, $params);
                } else {
                    static::add_new_attempts_to_clusters($userdata, $activitydata, $params, $readytouse);
                    return [[], [] ,[] ,[], $readytouse, [], []];
                }

                $aggregatedclusters = $avgorder = $order = [];
                $totalprcsum = $totalallprcsum = $totalcount = $totalallcount = $kmeanscount = $clusterkmeanscount = 0;

                foreach ($clusters as $key => $cluster) {
                    $num = $key;
                    $clusteravgcount = 0;

                    $clusterkmeanscount += count($cluster);

                    foreach ($cluster as $userdatakey => $point) {
                        if (!isset($aggregatedclusters[$num])) {
                            $aggregatedclusters[$num] = ['sum' => 0, 'users' => [], 'usercourses' => []];
                        }

                        $aggregatedclusters[$num]['users'][$userdatakey] = $userdata[$userdatakey];

                        if (isset($tocentroids[$userdatakey])) {
                            continue;
                        }

                        $kmeanscount++;
                        $keycounts = [];
                        foreach ($allkeys as $uniquekey => $allkeysdata) {
                            if (!$rebuild && (($cachebuildtimestamp && ($timestamps[$userdatakey][$uniquekey] > $cachebuildtimestamp)) || is_null($timestamps[$userdatakey][$uniquekey])) ) {
                                continue;
                            }

                            $mid = $allkeysdata['mid'];

                            if (!isset($perqavg[$mid]['data'][$num])) {
                                $perqavg[$mid]['data'][$num] = [
                                    'tabledata' => [],
                                    'sum' => 0,
                                    'count' => 0,
                                    'avg' => 0,
                                ];
                            }

                            if (!isset($keycounts[$mid])) {
                                $keycounts[$mid] = 0;
                            }

                            $keycounts[$mid]++;

                            $countkey = get_string('question', 'local_diagnostic', $keycounts[$mid]);
                            $qsortnumber = [];
                            $qnumber = 1;
                            foreach ($questions as $qitem) {
                                if ($qitem->qtype != 'description') {
                                    if (!isset($qsortnumber[$qitem->slot])) {
                                        $qsortnumber[$qitem->slot] = $qnumber;
                                    }
                                    $qnumber++;
                                }
                            }
                            if (!isset($perqavg[$mid]['data'][$num]['tabledata'][$countkey])) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey] = [
                                    'sum' => 0,
                                    'count' => 0,
                                    'qname' => $allkeysdata['qname'],
                                    'qid' => $allkeysdata['qid'],
                                    'slot' => $allkeysdata['slot'],
                                    'number' => $qsortnumber[$allkeysdata['slot']],
                                ];
                                //We have importance count starting from 0
                                $importanceindex = $keycounts[$mid] - 1;
                                if (isset($importancedata[$importanceindex])) {
                                    $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['importance'] = $importancedata[$importanceindex];
                                }
                            }

                            $perqavg[$mid]['data'][$num]['sum'] += $userdata[$userdatakey]['keys'][$uniquekey];
                            $perqavg[$mid]['data'][$num]['count']++;


                            if (isset($perqavg[$mid]['data'][$num]) && isset($perqavg[$mid]['data'][$num]['count']) && $perqavg[$mid]['data'][$num]['count']) {
                                $perqavg[$mid]['data'][$num]['prc'] =
                                        round($perqavg[$mid]['data'][$num]['sum'] / $perqavg[$mid]['data'][$num]['count'] * 100, 2);
                            } else {
                                $perqavg[$mid]['data'][$num]['prc'] = 0;
                            }

                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['sum'] += $userdata[$userdatakey]['keys'][$uniquekey];
                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['count']++;
                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] = round($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['sum'] / $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['count'] * 100, 2);
                            if ($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] < $yellow) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'red';
                            } elseif ($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] >= $green) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'green';
                            } else {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'yellow';
                            }

                            if (!isset($avgorder[$num])) {
                                $avgorder[$num] = ['sum' => 0, 'count' => 0];
                            }
                            $avgorder[$num]['sum'] += $userdata[$userdatakey]['keys'][$uniquekey];
                            $avgorder[$num]['count']++;
                        }

                        $clusteravgcount++;

                        $aggregatedclusters[$num]['sum'] += $userdata[$userdatakey]['prc'];
                        $aggregatedclusters[$num]['prc'] =
                                $clusteravgcount ? round($aggregatedclusters[$num]['sum'] / $clusteravgcount, 2) : 0;

                        $totalallprcsum += $userdata[$userdatakey]['prc'];
                        $totalallcount++;
                    }

                    if (isset($avgorder[$num]) && isset($avgorder[$num]['count']) && $avgorder[$num]['count']) {
                        $avgorder[$num] = round($avgorder[$num]['sum'] / $avgorder[$num]['count'] * 100, 2);
                    } else {
                        $avgorder[$num] = 0;
                    }

                    if (isset($aggregatedclusters[$num])) {
                        $order[$num] = $avgorder[$num];
                        $totalprcsum += $aggregatedclusters[$num]['sum'];
                        $totalcount += $clusteravgcount;
                    }
                }

                //echo ' $kmeanscount: ' . $kmeanscount;
                //echo ' $clusterkmeanscount: ' . $clusterkmeanscount;

                unset($userdata);

                $totalprc = $totalcount ? round($totalprcsum / $totalcount, 2) : 0;
                $totalallprc = $totalallcount ? round($totalallprcsum / $totalallcount, 2) : 0;

                //these two may be of different sizes so we sort them separately
                arsort($order, SORT_NUMERIC);
                arsort($avgorder, SORT_NUMERIC);

                $returnclusters = array_replace($order, $aggregatedclusters);

                $allkeys = array_keys($allkeys);

                foreach ($perqavg as $mid => $perqavgdata) {
                    $perqavg[$mid]['data'] = array_replace($avgorder, $perqavgdata['data']);
                }

                $clusterdata = ['clusters' => $returnclusters, 'table' => $perqavg, 'total' => ['prc' => $totalprc, 'allprc' => $totalallprc]];

                $zerocluster = [
                    'users' => [],
                    'sum' => 0,
                    'avg' => 0,
                    'prc' => 0
                ];

                foreach ($enrolled as $quizid => $quizdata) {
                    foreach ($quizdata as $attempt => $attemptdata) {
                        foreach ($attemptdata as $userid => $unused) {
                            $zerouserdatakey = $userid . '-' . $quizid . '-' . $attempt;
                            if (!isset($zerocluster['users'][$zerouserdatakey])) {
                                $zeroclusteruser = \core_user::get_user($userid);
                                $zerocluster['users'][$zerouserdatakey] = [
                                    'user' => (object) ['id' => $userid, 'fullname' => fullname($zeroclusteruser)],
                                    'sum' => 0,
                                    'avg' => 0,
                                    'prc' => 0
                                ];
                            }
                        }
                    }
                }

                array_unshift($clusterdata['clusters'], $zerocluster);
            } else {
                if ($userdata === false) {
                    $clusterdata = false;
                } else {
                    $clusterdata = [];
                }
            }

            $return = [$allkeys, $clusterdata, $centroids, $activitydata, $readytouse, $extra, $extracentroids];
        }

        return $return;
    }

    public static function get_mlnlp_results($midquestion, $params) {
        global $DB;

        $midcategoriesids = array_keys(get_enabled_categories($midquestion->id));
        $results = [];

        $timefinish = 0;

        if (isset($params['buildtime']) && !empty($params['buildtime'])) {
            $date = \DateTime::createFromFormat('d-m-Y', $params['buildtime']);
            $timefinish = $date->getTimestamp();
        }

        foreach (array_keys($params['qids']) as $qid) {
            $quizobj = quiz_settings::create($qid);
            $quizobj->preload_questions();
            $quizobj->load_questions();
            $questions = $quizobj->get_questions();

            foreach ($questions as $question) {
                if ($question->qtype == 'mlnlpessay' && $question->stamp == $midquestion->stamp) {
                    $categoriesids = array_keys(get_enabled_categories($question->id));
                    if ($categoriesids == $midcategoriesids) {
                        $results[$qid] = [
                            'grades' => qtype_mlnlpessay_get_results($qid, $question->id, $timefinish),
                            'questionname' => $question->name
                        ];
                    } else {
                        mtrace('MLNLP QUESTION MISMATCH, ID = ' . $question->id . ' QUIZ ID = ' . $qid);
                    }
                }
            }
        }

        return $results;
    }

    public static function get_mlnlp_data($question, $params) {
        global $DB;

        $clusterdata = [];

        if ($result = static::get_mlnlp_results($question, $params)) {
            $attemptscount = 0;
            $noanywhereattemptuserids = $noattemptsbyquizid = [];
            $allkeys = $matrix = $userdata = $perqavg = [];

            foreach ($result as $quizid => $data) {
                $courseid = $DB->get_field('quiz', 'course', ['id' => $quizid]);
                $noattemptuserids = static::get_enrolled_userids($courseid);
                $idx = 1;
                foreach ($data['grades']['grades'] as $categoryid => $categorydata) {
                    if (!isset($allkeys[$idx])) {
                        $allkeys[$idx] = $categorydata->name;
                    }
                    $attemptscount += count(array_keys($categorydata->grades));
                    $noattemptuserids = array_diff($noattemptuserids, array_keys($categorydata->grades));
                    foreach ($categorydata->grades as $userid => $grade) {
                        $userkey = $quizid . '-' . $userid;
                        $grade = intval($grade);
                        if (!isset($userdata[$userkey])) {
                            $user = \core_user::get_user($userid);
                            $userdata[$userkey] = [
                                'count' => 0,
                                'sum' => 0,
                                'prc' => 0,
                                'questionname' => $data['questionname'],
                                'timetaken' => $data['grades']['timetaken'][$userid],
                                'user' => [
                                    'id' => $userid,
                                    'fullname' => fullname($user)
                                ],
                                'keys' => []
                            ];
                        }
                        $userdata[$userkey]['keys'][$idx] = $grade;
                        $userdata[$userkey]['count']++;
                        $userdata[$userkey]['sum'] += $grade;
                        $userdata[$userkey]['prc'] = round($userdata[$userkey]['sum'] / $userdata[$userkey]['count'] * 100, 2);
                        $matrix[$userkey][$idx] = $grade;
                    }
                    $idx++;
                }

                foreach ($noattemptuserids as $userid) {
                    $userkey = $quizid . '-' . $userid;
                    if (!isset($userdata[$userkey])) {
                        $user = \core_user::get_user($userid);
                        $userdata[$userkey] = [
                            'count' => 0,
                            'zerocluster' => true,
                            'questionname' => $data['questionname'],
                            'sum' => 0,
                            'prc' => 0,
                            'timetaken' => 0,
                            'user' => [
                                'id' => $userid,
                                'fullname' => fullname($user)
                            ]
                        ];
                    }
                }

                $noattemptsbyquizid[$quizid] = $noattemptuserids;
                $noanywhereattemptuserids = array_merge($noanywhereattemptuserids, $noattemptuserids);
            }

            $mid = $params['mid'];

            mtrace ('$matrixcount: ' . count($matrix));

            if (!empty($matrix)) {

                $rebuild = static::get_rebuild($attemptscount, $params);
                if ($rebuild) {
                    list($clusters, $centroids, $importancedata) = static::Rcluster($matrix, [], $params);
                } else {
                    static::mlnlp_add_new_attempts_to_clusters($userdata, $params, $question->id);
                    return [json_decode($params['cache']->get('extra'), true)[$question->id], json_decode($params['cache']->get('extracentroids'), true)[$question->id]];
                }

                $config = get_config('local_diagnostic');

                $customsettings = $params['customsettings'] ?? [];
                $yellow = isset($customsettings['yellow']) && !empty($customsettings['yellow']) ? $customsettings['yellow'] : $config->yellow;
                $green = isset($customsettings['green']) && !empty($customsettings['green']) ? $customsettings['green'] : $config->green;

                $aggregatedclusters = $avgorder = [];
                $totalallprcsum = $totalcount = $totalallcount = $kmeanscount = $clusterkmeanscount = 0;

                foreach ($clusters as $key => $cluster) {
                    $num = $key;
                    $clusteravgcount = 0;

                    $clusterkmeanscount += count($cluster);

                    foreach ($cluster as $userkey => $point) {
                        list($quizid, $userid) = explode('-', $userkey);
                        if (!isset($aggregatedclusters[$quizid][$num])) {
                            $aggregatedclusters[$quizid][$num] = [
                                'sum' => 0,
                                'users' => [],
                                'name' => $result[$quizid]['questionname']
                            ];
                        }

                        $aggregatedclusters[$quizid][$num]['users'][$userid] =
                            [
                                'count' => $userdata[$userkey]['count'],
                                'sum' => $userdata[$userkey]['sum'],
                                'prc' => $userdata[$userkey]['prc'],
                                'user' => $userdata[$userkey]['user']
                            ];

                        $kmeanscount++;
                        $keycounts = [];
                        foreach ($allkeys as $uniquekey => $catname) {

                            if (!isset($perqavg[$mid]['data'][$num])) {
                                $perqavg[$mid]['data'][$num] = [
                                    'tabledata' => [],
                                    'sum' => 0,
                                    'count' => 0,
                                    'avg' => 0,
                                ];
                            }

                            if (!isset($keycounts[$mid])) {
                                $keycounts[$mid] = 0;
                            }

                            $keycounts[$mid]++;


                            $countkey = mb_strlen($catname) > 30 ? mb_substr($catname, 0, 30) . '...' : $catname;

                            if (!isset($perqavg[$mid]['data'][$num]['tabledata'][$countkey])) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey] = [
                                    'sum' => 0,
                                    'count' => 0,
                                    'qname' => $catname
                                ];
                                $importanceindex = $keycounts[$mid] - 1;
                                if (isset($importancedata[$importanceindex])) {
                                    $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['importance'] = $importancedata[$importanceindex];
                                }
                            }

                            $perqavg[$mid]['data'][$num]['sum'] += $point[$uniquekey];
                            $perqavg[$mid]['data'][$num]['count']++;
                            $perqavg[$mid]['data'][$num]['prc'] = round($perqavg[$mid]['data'][$num]['sum'] / $perqavg[$mid]['data'][$num]['count'] * 100, 2);

                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['sum'] += $point[$uniquekey];
                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['count']++;
                            $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] = round($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['sum'] / $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['count'] * 100, 2);
                            if ($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] < $yellow) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'red';
                            } elseif ($perqavg[$mid]['data'][$num]['tabledata'][$countkey]['prc'] >= $green) {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'green';
                            } else {
                                $perqavg[$mid]['data'][$num]['tabledata'][$countkey]['color'] = 'yellow';
                            }

                            if (!isset($avgorder[$num])) {
                                $avgorder[$num] = ['sum' => 0, 'count' => 0];
                            }
                            $avgorder[$num]['sum'] += $point[$uniquekey];
                            $avgorder[$num]['count']++;
                        }

                        $clusteravgcount++;

                        $aggregatedclusters[$quizid][$num]['sum'] += $userdata[$userkey]['prc'];
                        $aggregatedclusters[$quizid][$num]['prc'] = round($aggregatedclusters[$quizid][$num]['sum'] / $clusteravgcount, 2);

                        $totalallprcsum += $userdata[$userkey]['prc'];
                        $totalallcount++;
                    }

                    if (isset($avgorder[$num])) {
                        $avgorder[$num] = round($avgorder[$num]['sum'] / $avgorder[$num]['count'] * 100, 2);
                    } else {
                        $avgorder[$num] = 0;
                    }
                }

                mtrace(' $kmeanscount: ' . $kmeanscount);
                mtrace(' $clusterkmeanscount: ' . $clusterkmeanscount);

                $totalallprc = $totalallcount ? round($totalallprcsum / $totalallcount, 2) : 0;

                arsort($avgorder, SORT_NUMERIC);

                $returnclusters = [];
                foreach ($aggregatedclusters as $quizid => $aggregatedcluster) {
                    foreach ($avgorder as $clusternum => $percentage) {
                        if (isset($aggregatedcluster[$clusternum])) {
                            $returnclusters[$quizid][$clusternum] = $aggregatedcluster[$clusternum];
                        }
                    }

                    $zerocluster = [
                        'users' => [],
                        'name' => $result[$quizid]['questionname'],
                        'sum' => 0,
                        'avg' => 0,
                        'prc' => 0
                    ];

                    if (isset($noattemptsbyquizid[$quizid])) {
                        foreach ($noattemptsbyquizid[$quizid] as $userid) {
                            if (!isset($zerocluster['users'][$userid])) {
                                $zeroclusteruser = \core_user::get_user($userid);
                                $zerocluster['users'][$userid] = [
                                    'user' => (object) ['id' => $userid, 'fullname' => fullname($zeroclusteruser)],
                                    'sum' => 0,
                                    'avg' => 0,
                                    'prc' => 0
                                ];
                            }
                        }
                    }

                    array_unshift($returnclusters[$quizid], $zerocluster);
                }

                foreach ($perqavg as $mid => $perqavgdata) {
                    $perqavg[$mid]['data'] = array_replace($avgorder, $perqavgdata['data']);
                }

                $clusterdata = ['clusters' => $returnclusters, 'table' => $perqavg, 'total' => ['allprc' => $totalallprc], 'categorynames' => $allkeys];
            }
        }

        if ($attemptscount < static::get_rebuild_limit()) {
            $centroids = [];
        }

        return [$clusterdata, $centroids];
    }

    public static function get_enrolled_userids($courseid) {
        global $DB, $CFG;

        $context = \context_course::instance($courseid);

        list($gradebookroles_sql, $params) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
        $params['contextid'] = $context->id;
        $sql = "SELECT DISTINCT ra.userid FROM {role_assignments} ra WHERE ra.roleid $gradebookroles_sql AND contextid = :contextid";

        $userids = $DB->get_fieldset_sql($sql, $params);

        return array_combine(array_values($userids), array_values($userids));
    }

    static function get_userdata($params, $allkeys) {
        global $DB, $CFG;

        $userdata = $enrolled = $users = $activitydata = $invaliduserids = [];

        if ($params['hasnewattempts'] || $params['recache']) {
            list($qwhere, $qparams) = $DB->get_in_or_equal(array_keys($params['qids']), SQL_PARAMS_NAMED, 'quizid');

            list($grwhere, $grparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');

            $timewhere = '';
            $timeparams = [];
            if (isset($params['buildtime']) && !empty($params['buildtime'])) {
                $timewhere = "AND qa.timefinish < :timefinish";

                $date = \DateTime::createFromFormat('d-m-Y', $params['buildtime']);
                $timeparams = [
                    'timefinish' => $date->getTimestamp()
                ];
            }

            $exclwhere = '';
            $exclparams = [];

            if (isset($customsettings['startdate']) && !empty($customsettings['startdate'])) {
                $exclparams['excltimestart'] = $customsettings['startdate'];
                if (isset($customsettings['enddate']) && !empty($customsettings['enddate'])) {
                    $exclparams['excltimeend'] = $customsettings['enddate'];
                    $exclwhere = 'AND qa.timefinish BETWEEN :excltimestart AND :excltimeend';
                } else {
                    $exclwhere = 'AND qa.timefinish > :excltimestart';
                }
            } elseif (isset($customsettings['enddate']) && !empty($customsettings['enddate'])) {
                $exclparams['excltimeend'] = $customsettings['enddate'];
                $exclwhere = 'AND qa.timefinish < :excltimeend';
            }

            $cutoff = isset($customsettings['cutoff']) && !empty($customsettings['cutoff']) ? $customsettings['cutoff'] : static::DEFAULTCUTOFF;

            $sql = "SELECT DISTINCT qas.id, qas.questionattemptid, qas.sequencenumber, qas.state,
                       qas.fraction, qas.timecreated, att.questionid, att.slot, qa.userid as userid,
                       qa.attempt as attempt, q.id as qid, que.stamp as idnumber, c.instanceid as courseid, 
                       qa.timestart, qa.timefinish
                    FROM {question_attempt_steps} qas
                    JOIN {question_attempts} att ON qas.questionattemptid = att.id
                    JOIN {question} que ON att.questionid = que.id
                    JOIN {quiz_attempts} qa ON (qa.uniqueid = att.questionusageid)
                    JOIN {quiz} q ON (qa.quiz = q.id)
                    JOIN {role_assignments} ra ON (qa.userid = ra.userid)
                    JOIN {context} c ON (ra.contextid = c.id AND q.course = c.instanceid)
                        WHERE q.id $qwhere
                        AND ra.roleid $grwhere
                        AND qas.fraction IS NOT NULL
                        AND que.stamp IS NOT NULL
                        AND ra.id IS NOT NULL
                        AND c.id IS NOT NULL
                        AND que.length > 0
                        AND qa.state = 'finished'
                        AND qa.preview = 0
                        $timewhere
                        $exclwhere
                        ORDER BY qas.questionattemptid, qas.timecreated ASC";

            $questionsteps = $DB->get_records_sql($sql, $qparams + $grparams + $timeparams + $exclparams);

            $firstattempts = [];

            foreach ($questionsteps as $questionstep) {

                if (!isset($enrolled[$questionstep->qid][$questionstep->attempt])) {
                    $enrolled[$questionstep->qid][$questionstep->attempt] = static::get_enrolled_userids($questionstep->courseid);
                }

                if (isset($firstattempts[$questionstep->questionattemptid])) {
                    continue;
                } else {
                    $firstattempts[$questionstep->questionattemptid] = $questionstep->timecreated;
                }

                unset($enrolled[$questionstep->qid][$questionstep->attempt][$questionstep->userid]);

                if (!isset($users[$questionstep->userid])) {
                    $users[$questionstep->userid] = \core_user::get_user($questionstep->userid);
                }

                $userid = $questionstep->userid . '-' . $questionstep->qid . '-' . $questionstep->attempt;

                if (!isset($userdata[$userid])) {
                    $userdata[$userid] = [
                        'keys' =>[],
                        'courses' => [],
                        'activities' => [],
                        'user' => (object) [
                            'id' => $users[$questionstep->userid]->id,
                            'fullname' => fullname($users[$questionstep->userid])
                        ]
                    ];

                    if (!isset($firstattemptsbyuserid[$questionstep->userid])) {
                        $firstattemptsbyuserid[$questionstep->userid] = $questionstep->userid;
                        $userdata[$userid]['toclusters'] = true;
                    }
                }

                if (!isset($userdata[$userid]['courses'][$questionstep->courseid])) {
                    $userdata[$userid]['courses'][$questionstep->courseid] = $questionstep->courseid;
                }
                if (!isset($userdata[$userid]['activities'][$questionstep->qid])) {
                    if (empty($userdata[$userid]['activities'])) {
                        $userdata[$userid]['activities'][$questionstep->qid]['first'] = true;
                    } else {
                        $userdata[$userid]['activities'][$questionstep->qid] = [];
                    }

                    $userdata[$userid]['activities'][$questionstep->qid] = array_merge(
                        $userdata[$userid]['activities'][$questionstep->qid],
                        [
                            'cmid' => $params['qids'][$questionstep->qid]->instanceid,
                            'timetaken' => $questionstep->timefinish - $questionstep->timestart
                        ]
                    );
                }

                $uniquekey = $params['qids'][$questionstep->qid]->data . '-' . $questionstep->idnumber;
                if (!isset($allkeys[$uniquekey])) {
                    $invaliduserids[$userid][] = $uniquekey;
                }
                if (!isset($userdata[$userid]['keys'][$uniquekey]) || $userdata[$userid]['keys'][$uniquekey]['timecreated'] > $questionstep->timecreated) {
                    $userdata[$userid]['keys'][$uniquekey] = [
                        'fraction' => $questionstep->fraction >= $cutoff ? 1 : 0,
                        'timecreated' => $questionstep->timecreated,
                    ];
                }

                if (!isset($activitydata[$questionstep->courseid])) {
                    $activitydata[$questionstep->courseid] = [];
                }

                if (!isset($activitydata[$questionstep->courseid][$questionstep->qid])) {
                    $activitydata[$questionstep->courseid][$questionstep->qid] = [];
                }

                if (!isset($activitydata[$questionstep->courseid][$questionstep->qid][$questionstep->attempt])) {
                    $activitydata[$questionstep->courseid][$questionstep->qid][$questionstep->attempt] = $questionstep->attempt;
                }
            }
        } else {
            $userdata = false;
        }

        //PTL-12828 throw away all attempts in which at least one question has invalid stamp
        foreach ($invaliduserids as $invaliduserid => $questionarray) {
            unset($userdata[$invaliduserid]);
        }

        return [$userdata, $enrolled, $activitydata];
    }

    static function truncate($string, $words = 10, $etc = ' ...')
    {
        $exploded = explode(' ', $string);
        $text  = implode(' ', array_slice($exploded, 0, $words));
        return $text . (count($exploded) > $words ? $etc : '');
    }

    static function get_mids($metadatafieldid, $cmids) {
        global $DB;
        $mids = [];
        if ($cmids) {
            list ($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cmids');
            $inparams['fieldid'] = $metadatafieldid;

            $result = $DB->get_records_sql("SELECT DISTINCT lm1.data FROM {local_metadata} lm1 WHERE lm1.fieldid = :fieldid AND lm1.instanceid $insql", $inparams);

            foreach ($result as $cm) {
                $mids[$cm->data] = $cm->data;
            }
        }

        return $mids;
    }

    public static function Rcluster($points, $tocentroids, $params, $forcebrad = false) {
        global $CFG;

        $config = get_config('local_diagnostic');
        $clustercount = isset($params['clusters']) && !empty($params['clusters']) ? $params['clusters'] : static::get_clusternum();
        if (isset($params['rdebug']) && !empty($params['rdebug'])) {
            $dir = make_writable_directory($CFG->dataroot . '/rscripts');
        } else {
            $dir = make_temp_directory(random_string());
        }

        $pathtopython = $CFG->pathtopython ?: static::DEFAULT_PATHTOPYTHON;
        $filepath = $dir . '/' . static::$source_filename;

        $fp = fopen($filepath, 'w');
        $mapper = [];
        foreach ($points as $userdatakey => $point) {
            $mapper[] = $userdatakey;
            fputcsv($fp, $point);
        }

        fclose($fp);

        $clusternummethod = $forcebrad ? static::CLUSTERNUM_OPTIMAL : $config->clusternummethod;
        switch ($clusternummethod) {
            case static::CLUSTERNUM_GAPESTIMATE:
                $scriptname = $config->gapestimate ?: static::$gapestimateRscript_name;
                break;
            case static::CLUSTERNUM_OPTIMAL:
                $nmax = $config->nmax ?: static::NMAX;
                $nmin = $config->nmin ?: static::NMIN;
                $optimal_script_path = $CFG->dirroot . '/local/diagnostic/scripts/' . static::$optimal_name;

                $time = time();
                exec("$pathtopython $optimal_script_path $filepath $nmin $nmax", $optimal_output);
                if (is_array($optimal_output) && !empty($optimal_output)) {
                    $firstline = array_shift($optimal_output);
                    $optimal_outputs = json_decode($firstline);
                    mtrace('python result ' . $firstline . ", RUN time " . (time() - $time) . " sec");
                    $clustercount = min($optimal_outputs);
                    if ($forcebrad) {
                        return $optimal_outputs;
                    }
                } else {
                    mtrace('python result empty ar not an array, RUN time:' . (time() - $time) . " sec");
                }
                if ($forcebrad) {
                    return [];
                }
                break;
            case static::CLUSTERNUM_FIXED:
                $scriptname = static::$Rscript_name;
                break;
            default:
                throw new \moodle_exception('unknownclustermethod', 'local_diagnostic');
        }

        $outputfilepath = $dir . '/' . static::$result_filename;
        $script_path = $CFG->dirroot . '/local/diagnostic/Rscripts/' . $scriptname;

        exec("Rscript $script_path $filepath $outputfilepath $dir $clustercount");
        if (!file_exists($outputfilepath)) {
            return [[],[],[]];
        }

        $fp = fopen($outputfilepath, 'r');
        $rowcount = -1;
        $clusters = $centroids = $sums = $pointcounts = [];
        while($row = fgetcsv($fp)) {
            //skip first row with headers
            if ($rowcount > -1) {
                $clusternum = array_pop($row);
                if (!isset($clusters[$clusternum])) {
                    $clusters[$clusternum] = [];
                }
                if (!isset($pointcounts[$clusternum])) {
                    $pointcounts[$clusternum] = 0;
                }

                $pointcounts[$clusternum]++;

                if (!isset($sums[$clusternum])) {
                    $sums[$clusternum] = [];
                }

                if (!isset($centroids[$clusternum])) {
                    $centroids[$clusternum] = [];
                }

                $userdatakey = $mapper[$rowcount];
                $clusters[$clusternum][$userdatakey] = $points[$userdatakey];

                foreach (array_keys($points[$userdatakey]) as $uniquekey) {
                    if (!isset($sums[$clusternum][$uniquekey])) {
                        $sums[$clusternum][$uniquekey] = 0;
                    }
                    $sums[$clusternum][$uniquekey] += $points[$userdatakey][$uniquekey];
                }
            }

            $rowcount++;
        }

        fclose($fp);

        foreach ($sums as $clusternum => $sumsbyquestion) {
            foreach ($sumsbyquestion as $uniquekey => $sum) {
                $centroids[$clusternum][$uniquekey] = number_format($sum / $pointcounts[$clusternum], 4);
            }
        }

        foreach ($tocentroids as $userdatakey => $tocentroiddata) {
            if ($assignedcluster = static::get_clusternum_for_point_by_centroid($centroids, $tocentroiddata)) {
                $clusters[$assignedcluster][$userdatakey] = $tocentroiddata;
            }
        }

        $importancedata = [];

        if ($config->importantquestions) {
            $important_script_path = $CFG->dirroot . '/local/diagnostic/scripts/' . static::$important_name;

            $time = time();
            exec("$pathtopython $important_script_path $filepath $clustercount", $important_output);
            if (is_array($important_output) && !empty($important_output)) {
                foreach ($important_output as $responseline) {
                    $values = explode('    ', $responseline);
                    if (count($values) !== 2) {
                        //if it least one line has no expected output - trace and break
                        mtrace('Importance calculation error, unexpected output line: ' . $responseline);
                        $importancedata = [];
                        break;
                    } else {
                        $questionposition = trim($values[0]);
                        $importancedata[$questionposition] = trim($values[1]);
                    }
                }
            } else {
                mtrace('Importance calculation error, empty or faulty response');
            }
        }

        return [$clusters, $centroids, $importancedata];
    }

    public static function get_attempts_count($cache) :int {
        $count = 0;

        if (!empty($cache) && is_subclass_of($cache, '\core\persistent') ) {
            $jsondata = json_decode($cache->get('data'), true);

            if (is_array($jsondata['clusters'])) {
                //zero cluster should not count
                array_shift($jsondata['clusters']);

                foreach ($jsondata['clusters'] as $cluster) {
                    $count+= count($cluster['users']);
                }
            }
        }

        return $count;
    }

    static function add_new_attempts_to_clusters($userdata, $activitydata, $params, $readytouse) :void
    {
        global $DB;

        if (!empty($params['cache']) && is_subclass_of($params['cache'], '\core\persistent')) {
            $jsondata = json_decode($params['cache']->get('data'), true);
            $centroids = json_decode($params['cache']->get('centroids'), true);
            $jsonactivitydata = json_decode($params['cache']->get('activities'), true);

            if (is_array($jsondata['clusters'])) {
                foreach ($jsondata['clusters'] as $clusternum => $cluster) {
                    if ($clusternum == 0) {
                        continue;
                    }
                    foreach ($userdata as $userdatakey => $pointdata) {
                        if (isset($cluster['users'][$userdatakey])) {
                            unset($userdata[$userdatakey]);
                        }
                    }
                }

                $newattempts = 0;

                foreach ($userdata as $userdatakey => $pointdata) {

                    $point = $pointdata['keys'];

                    if (!empty($centroids)) {
                        $assignedcluster = static::get_clusternum_for_point_by_centroid($centroids, $point);
                    } else {
                        $mindistance = -1;
                        $assignedcluster = null;

                        foreach ($jsondata['clusters'] as $clusternum => $cluster) {
                            if ($clusternum == 0) {
                                continue;
                            }
                            foreach ($cluster['users'] as $olduserdatakey => $oldpointdata) {
                                $oldpoint = $oldpointdata['keys'];
                                $distance = static::distance($oldpoint, $point);
                                if ($distance < $mindistance || $mindistance < 0) {
                                    $mindistance = $distance;
                                    $assignedcluster = $clusternum;
                                }
                            }
                        }
                    }

                    if ($assignedcluster !== null) {
                        $newattempts++;
                        unset($userdata[$userdatakey]['toclusters']);
                        $jsondata['clusters'][$assignedcluster]['users'][$userdatakey] = $userdata[$userdatakey];

                        if (isset($jsondata['clusters'][0]['users'][$userdatakey])) {
                            unset($jsondata['clusters'][0]['users'][$userdatakey]);
                        }

                        list($userid, $qid, $attempt) = explode('-', $userdatakey);

                        $event = \local_diagnostic\event\added_by_centroid::create([
                            'relateduserid' => $userid,
                            'context' => \context_module::instance($params['cmid']),
                            'other' => [
                                'attempt' => $attempt,
                                'sourcecmid' => $params['cmid'],
                                'clusternum' => $assignedcluster,
                            ]
                        ]);

                        $event->trigger();
                    }
                }

                $clustercount = count($jsondata['clusters']) - 1;
                mtrace('CLUSTERS: ' . $clustercount . ', NO REBUILD, NEW ATTEMPTS: ' . $newattempts);

                $params['cache']->set('data', json_encode($jsondata));
                $params['cache']->set('activities', json_encode($activitydata + $jsonactivitydata));
                $params['cache']->set('readytouse', $readytouse);
                $params['cache']->update();
            }
        }
    }

    static function mlnlp_add_new_attempts_to_clusters($userdata, $params, $questionid, $readytouse = true) :void
    {
        global $DB;

        if (!empty($params['cache']) && is_subclass_of($params['cache'], '\core\persistent')) {
            $extradata = json_decode($params['cache']->get('extra'), true);
            $extracentroids = json_decode($params['cache']->get('extracentroids'), true);

            if (!empty($extradata[$questionid]) && !empty($extracentroids[$questionid])) {
                $jsondata = $extradata[$questionid];
                $centroids = $extracentroids[$questionid];

                if (is_array($jsondata['clusters'])) {
                    foreach ($jsondata['clusters'] as $quizid => $quizcluster) {
                        foreach ($quizcluster as $clusternum => $cluster) {
                            if ($clusternum == 0) {
                                continue;
                            }
                            foreach ($cluster['users'] as $userid => $user) {
                                $userdatakey = $quizid . '-' . $userid;
                                if (isset($userdata[$userdatakey])) {
                                    unset($userdata[$userdatakey]);
                                }
                            }
                        }
                    }

                    $newattempts = 0;

                    foreach ($userdata as $userdatakey => $pointdata) {

                        list($quizid, $userid) = explode('-', $userdatakey);
                        if (!isset($jsondata['clusters'][$quizid][0])) {
                            $jsondata['clusters'][$quizid][0] = [
                                'users' => [],
                                'name' => $pointdata['questionname'],
                                'sum' => 0,
                                'avg' => 0,
                                'prc' => 0
                            ];
                        }

                        if (isset($pointdata['zerocluster']) && !empty($pointdata['zerocluster'])) {
                            $jsondata['clusters'][$quizid][0][$userid] = [
                                'count' => $pointdata['count'],
                                'sum' => $pointdata['sum'],
                                'prc' => $pointdata['prc'],
                                'user' => $pointdata['user']
                            ];

                            continue;
                        }

                        $point = $pointdata['keys'];
                        $assignedcluster = static::get_clusternum_for_point_by_centroid($centroids, $point);

                        if ($assignedcluster !== null) {
                            $newattempts++;
                            $jsondata['clusters'][$quizid][$assignedcluster]['users'][$userid] = [
                                'count' => $pointdata['count'],
                                'sum' => $pointdata['sum'],
                                'prc' => $pointdata['prc'],
                                'user' => $pointdata['user']
                            ];

                            if (isset($jsondata['clusters'][$quizid][0]['users'][$userid])) {
                                unset($jsondata['clusters'][$quizid][0]['users'][$userid]);
                            }

                            $event = \local_diagnostic\event\added_by_centroid::create([
                                'relateduserid' => $userid,
                                'context' => \context_module::instance($params['cmid']),
                                'other' => [
                                    'type' => 'mlnlp',
                                    'questionid' => $questionid,
                                    'sourcecmid' => $params['cmid'],
                                    'clusternum' => $assignedcluster,
                                ]
                            ]);

                            $event->trigger();
                        }

                        ksort($jsondata['clusters'][$quizid]);
                    }

                    $clustercount = count($jsondata['clusters']) - 1;
                    mtrace('MLNLP QUESTION = ' . $questionid . ', NO REBUILD, NEW ATTEMPTS ADDED BY CENTROIDS: ' . $newattempts);
                    $extradata[$questionid] = $jsondata;
                    $params['cache']->set('extra', json_encode($extradata));
                    $params['cache']->update();
                }
            }
        }
    }

    static function get_clusternum_for_point_by_centroid($centroids, $point) {
        $mindistance = -1;
        $assignedcluster = null;

        foreach ($centroids as $clusternum => $centroid) {
            $distance = static::distance($centroid, $point);
            if ($distance < $mindistance || $mindistance < 0) {
                $mindistance = $distance;
                $assignedcluster = $clusternum;
            }
        }

        return $assignedcluster;
    }

    static function distance($point1, $point2): float {
        $sum = 0;
        foreach ($point1 as $key => $value) {
            if (isset($point2[$key])) {
                $sum += pow($value - $point2[$key], 2);
            }
        }

        return sqrt($sum);
    }

    static function process($params) {
        global $DB;

        list($allkeys, $clusters, $centroids, $activitydata, $readytouse, $extra, $extracentroids) = \local_diagnostic_external::get_clusters_array($params);

        if (isset($clusters) && !empty($clusters)) {

            $clustercount = count($clusters['clusters']) - 1;
            $clusters = json_encode($clusters, JSON_THROW_ON_ERROR);
            $extra = !empty($extra) ? json_encode($extra, JSON_THROW_ON_ERROR) : '';

            if ($params['cache']) {
                $params['cache']->set('data', $clusters);
                $params['cache']->set('extra', $extra);
                $params['cache']->set('activities', $activitydata);
                $params['cache']->set('rebuild', 0);
                $params['cache']->set('readytouse', $readytouse);
                if ($centroids) {
                    $params['cache']->set('centroids', json_encode($centroids));
                }
                if ($extracentroids) {
                    $params['cache']->set('extracentroids', json_encode($extracentroids));
                }
                if ($activitydata) {
                    $params['cache']->set('activities', json_encode($activitydata));
                }

                if (isset($params['buildtime'])) {
                    $buildtime = !empty($params['buildtime']) && empty($params['rebuild']) ? $params['buildtime'] : null;
                    $params['cache']->set('buildtime', $buildtime);
                }

                $params['cache']->update();

                //unset($cachestodelete[$cache->get('id')]);
            } else {
                $recorddata = [
                    'mid' => $params['mid'],
                    'data' => $clusters,
                    'extra' => $extra,
                    'centroids' => $centroids ? json_encode($centroids) : '',
                    'extracentroids' => $extracentroids ? json_encode($extracentroids) : '',
                    'activities' => $activitydata ? json_encode($activitydata) : '',
                    'rebuild' => 0,
                    'readytouse' => $readytouse,
                    'buildtime' => $params['buildtime'] ?? null
                ];

                $cache = new \local_diagnostic\cache(0, (object)$recorddata);
                $cache->create();

                unset($recorddata);
            }

            unset($clusters);
            mtrace('MID: ' . $params['mid'] . ' CLUSTERS: ' . $clustercount);
        }
    }

    public static function has_custom_settings($mid) {

        $custommids = get_config('local_diagnostic', 'custommids');

        foreach (explode(',', $custommids) as $custommid) {
            if ($mid == trim($custommid)) {
                return true;
            }
        }

        return false;
    }

    public static function is_enabled($mid) {

        $custommids = get_config('local_diagnostic', 'croncustommids');

        foreach (explode(',', $custommids) as $custommid) {
            if ($mid == trim($custommid)) {
                return true;
            }
        }
        return false;
    }


    public static function get_custom_settings($mid) {

        $return_settings = [
            'yellow' => intval(get_config('local_diagnostic', 'activity' . $mid . 'yellow')),
            'green' => intval(get_config('local_diagnostic', 'activity' . $mid . 'green')),
            'excludedcmids' => get_config('local_diagnostic', 'activity' . $mid . 'exludedcmids') ? array_map('trim', explode(',', get_config('local_diagnostic', 'activity' . $mid . 'exludedcmids'))) : [],
            'repoquestionsonly' => get_config('local_diagnostic', 'repoquestionsonly' . $mid) ? get_config('local_diagnostic', 'repoquestionsonly' . $mid) : false,
            'excludeopenquestions' => get_config('local_diagnostic', 'excludeopenquestions' . $mid) ? get_config('local_diagnostic', 'excludeopenquestions' . $mid) : false,
            'cutoff' => get_config('local_diagnostic', 'activity' . $mid . 'cutoff') ? floatval(get_config('local_diagnostic', 'activity' . $mid . 'cutoff')) : static::DEFAULTCUTOFF,
            'startdate' => get_config('local_diagnostic', 'activity' . $mid . 'startdate') ? \DateTimeImmutable::createFromFormat('d-m-Y', get_config('local_diagnostic', 'activity' . $mid . 'startdate'))->getTimestamp() : 0,
            'enddate' => get_config('local_diagnostic', 'activity' . $mid . 'enddate') ? \DateTimeImmutable::createFromFormat('d-m-Y', get_config('local_diagnostic', 'activity' . $mid . 'enddate'))->getTimestamp() : 0,
            'excludedquestionids' => get_config('local_diagnostic', 'activity' . $mid . 'exludedquestionids') ? array_map('trim', explode(',', get_config('local_diagnostic', 'activity' . $mid . 'exludedquestionids'))) : [],
            'midfile' => get_config('local_diagnostic', 'midfile' . $mid),
            'midurl' => get_config('local_diagnostic', 'midurl' . $mid),
        ];

        $midclusters = get_config('local_diagnostic', 'activityclusternum_' . $mid);
        $clusternum = !empty($midclusters) ? $midclusters : static::get_clusternum();

        if (!empty($clusternum)) {
            for ($j = 1; $j <= $clusternum; ++$j) {
                $return_settings["cluster_" . $j . "_desc"] = get_config('local_diagnostic',
                    'cluster' . $j . 'descriptionactivity' . $mid);
            }
        }

        return ['customsettings' => $return_settings, 'clusters' => $clusternum];
    }

    public static function get_clusternum() {
        $clusters = get_config('local_diagnostic', 'clusternum');
        return intval($clusters) > 0 ? $clusters : static::CLUSTERNUM;
    }

    public static function get_qids($params) {
        global $DB, $CFG;

        $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
        $repoquestionsonly = isset($params['customsettings']['repoquestionsonly']) ? $params['customsettings']['repoquestionsonly'] : null;

        $inparams = [
            'mid' => $params['mid'],
            'fieldid' => $params['metadatafieldid']
        ];

        $excludedsql = $excludedcmidsql = $mexcludedsql = $stampexcludedsql = $excludeopenquestions = '';
        $allkeys = $excludedparams = $mexcludedparams = $stampexcludedparams = [];

        if (isset($params['customsettings']['excludedcmids']) && !empty($params['customsettings']['excludedcmids'])) {
            list ($excludedsql, $excludedparams) = $DB->get_in_or_equal($params['customsettings']['excludedcmids'], SQL_PARAMS_NAMED, 'exclcmid', false);
            $excludedsql = ' AND lm.instanceid ' . $excludedsql;
            $excludedcmidsql = ' AND cm.id ' . $excludedsql;
        }

        $qids = $DB->get_records_sql("
            SELECT cm.instance as quizid, lm.instanceid, lm.data, cm.course
            FROM {local_metadata} lm
            JOIN {course_modules} cm ON (lm.instanceid = cm.id)
            WHERE lm.fieldid = :fieldid AND lm.data = :mid" . $excludedsql, $inparams + $excludedparams);


        if (isset($params['customsettings']['excludedquestionids']) && !empty($params['customsettings']['excludedquestionids'])) {
            list ($mexcludedsql, $mexcludedparams) = $DB->get_in_or_equal($params['customsettings']['excludedquestionids'], SQL_PARAMS_NAMED, 'exclqid', false);
            list ($qexcludedsql, $qexcludedparams) = $DB->get_in_or_equal($params['customsettings']['excludedquestionids'], SQL_PARAMS_NAMED, 'exclqid');
            $stamps = $DB->get_fieldset_select('question', 'stamp', 'id ' . $qexcludedsql, $qexcludedparams);
            list ($stampexcludedsql, $stampexcludedparams) = $DB->get_in_or_equal($stamps, SQL_PARAMS_NAMED, 'exclstamp', false);
            $mexcludedsql = ' AND q.id ' . $mexcludedsql;
            $stampexcludedsql = ' AND q.stamp ' . $stampexcludedsql;
        }

        if (isset($params['customsettings']['excludeopenquestions']) && !empty($params['customsettings']['excludeopenquestions'])) {
            $excludeopenquestions = " AND q.qtype not in ('essay', 'essayrubric', 'poodllrecording', 'opensheet') ";
        }

        $msql = "SELECT qv.id, qv.version, q.stamp as idnumber, q.questiontext, q.name, q.id as questionid, q.qtype, qs.slot, cm.id as cmid
                                FROM {quiz_slots} qs
                                JOIN {question_references} qr ON qr.itemid = qs.id AND qr.component = :modquiz AND qr.questionarea = :slot
                                JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                                JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                                JOIN {question} q ON q.id = qv.questionid
                                JOIN {course_modules} cm ON (cm.instance = qs.quizid)
                                JOIN {local_metadata} lm ON (lm.instanceid = cm.id AND fieldid = :fieldid)
                                JOIN {modules} m ON (cm.module = m.id)
                                JOIN {course} c ON (cm.course = c.id)
                                JOIN {course_categories} AS cc ON c.category = cc.id
                                WHERE m.name = :quiz
                                AND q.length > 0
                                AND cc.id IN (SELECT id FROM {course_categories} WHERE parent = :maagar)
                                AND lm.data = :mid $excludeopenquestions
                                AND q.stamp IS NOT NULL $mexcludedsql $excludedcmidsql
                                ORDER BY qs.slot";

        if (!$parentcategoryid = \community_oer\main_oer::get_oer_category()) {
            $parentcategoryid = 0;
        }

        $minparams = [
            'mid' => $params['mid'],
            'quiz' => 'quiz',
            'modquiz' => 'mod_quiz',
            'fieldid' => $metadatafieldid,
            'slot' => 'slot',
            'maagar' => $parentcategoryid,
        ];

        $allmidquestionsidnums = [];
        $allmidquestionswithqv = $DB->get_records_sql($msql, $minparams + $mexcludedparams + $excludedparams);


        if (isset($params['customsettings']['excludeopenquestions']) && !empty($params['customsettings']['excludeopenquestions'])) {
            foreach ($allmidquestionswithqv as $qvid => $questiondata) {
                if ($questiondata->qtype == 'combined') {
                    $sql = "SELECT count(id)
                            from {question} 
                            where  parent = ? and qtype in ('essay')
                            ";
                    if ($DB->count_records_sql($sql, [$questiondata->questionid])){
                        unset($allmidquestionswithqv[$qvid]);
                        $excludeopenquestions.= ' AND q.id != '.$questiondata->questionid;
                    }
                 }
            }
        }

        $questionids = [];

        $midsnumbers = [];
        foreach ($allmidquestionswithqv as $row) {
            if ($row->cmid == $params['mid']) {
                $midsnumbers[] = $row;
            }
        }
        list($allmidquestionsidnums, $qvids) = get_stamps_by_latest_version($midsnumbers);

        foreach ($allmidquestionsidnums as $slot => $idnumber) {
            $uniquekey = $params['mid'] . '-' . $idnumber;
            if (!isset($allkeys[$uniquekey])) {
                $allkeys[$uniquekey] = [
                    'uniquekey' => $uniquekey,
                    'mid' => $params['mid'],
                    'qname' => static::truncate($allmidquestionswithqv[$qvids[$slot]]->name . ' ' . strip_tags($allmidquestionswithqv[$qvids[$slot]]->questiontext)),
                    'qid' => $allmidquestionswithqv[$qvids[$slot]]->questionid,
                    'slot' => $slot,
                    'version' => $allmidquestionswithqv[$qvids[$slot]]->version,
                ];
            }
        }

        $cmsql = "SELECT qv.id, q.stamp as idnumber, qv.version, qs.slot
                                FROM {quiz_slots} qs
                                JOIN {question_references} qr ON qr.itemid = qs.id AND qr.usingcontextid = :modulecontextid AND qr.component = :modquiz AND qr.questionarea = :slot
                                JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                                JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                                JOIN {question} q ON q.id = qv.questionid
                                WHERE qs.quizid = :quizid
                                AND q.length > 0 $excludeopenquestions
                                AND q.stamp IS NOT NULL $stampexcludedsql
                                ORDER BY qs.slot";

        $stampexcludedparams['modquiz'] = 'mod_quiz';
        $stampexcludedparams['slot'] = 'slot';

        foreach ($qids as $qid => $qiddata) {

            $stampexcludedparams['quizid'] = $qid;
            $stampexcludedparams['modulecontextid'] = (\context_module::instance($qiddata->instanceid))->id;

            $allquizquestionswithqv = $DB->get_records_sql($cmsql, $stampexcludedparams);
            list($allquizquestions, $qvids) = get_stamps_by_latest_version($allquizquestionswithqv);

            $matchcount = count(array_intersect($allmidquestionsidnums, $allquizquestions));

            if ($repoquestionsonly) {
                if ($matchcount < count($allmidquestionsidnums)) {
                    unset($qids[$qid]);
                    $sql = 'SELECT COUNT(id) FROM {quiz_attempts} where quiz = ? AND preview=0 AND state = ?';
                    $countattempt = $DB->count_records_sql($sql, [$qid, 'finished']);
                    mtrace('COURSE ' . $qiddata->course . ' CMID ' . $qiddata->instanceid . ' - Question not matched (' .
                        $countattempt . ')');
                }
            } elseif ($matchcount < count($allquizquestions)) {
                unset($qids[$qid]);
                $sql = 'SELECT COUNT(id) FROM {quiz_attempts} where quiz = ? AND preview=0 AND state = ?';
                $countattempt = $DB->count_records_sql($sql, [$qid, 'finished']);
                mtrace('COURSE ' . $qiddata->course . ' CMID ' . $qiddata->instanceid . ' - Question not matched (' .
                    $countattempt . ')');
            }
        }

        return [$allkeys, $qids];
    }

    static function get_rebuild($currentcount, $params) {
        $cachedbuildtimestamp = static::get_cachebuild_timestamp($params);

        $rebuildlimit = static::get_rebuild_limit();

        return ($currentcount < $rebuildlimit && !$cachedbuildtimestamp) || (isset($params['buildtime']) && !empty($params['buildtime'])) || $params['rebuild'];
    }

    static function get_cachebuild_timestamp($params) {
        $cachedbuildtimestamp = null;

        if (!empty($params['cache'])) {
            $cachedbuildtime = $params['cache']->get('buildtime');
            if ($cachedbuildtime) {
                $date = \DateTime::createFromFormat('d-m-Y', $cachedbuildtime);
                $cachedbuildtimestamp = $date->getTimestamp();
            }
        }

        return $cachedbuildtimestamp;
    }

    static function get_rebuild_limit() {
        $config = get_config('local_diagnostic');

        return $config->rebuildlimit ?: static::DEFAULT_REBUILD_LIMIT;
    }

    static function get_repo_cmid($mid) {
        global $CFG, $DB;

        if (!$parentcategoryid = \community_oer\main_oer::get_oer_category()) {
            $parentcategoryid = 0;
        }

        $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);

        return $DB->get_field_sql('SELECT lm.instanceid FROM {local_metadata} lm
                                                JOIN {course_modules} cm ON (cm.id = lm.instanceid)
                                                JOIN {course} c ON (cm.course = c.id)
                                                JOIN {course_categories} AS cc ON c.category = cc.id
                                                JOIN {modules} AS m ON m.id = cm.module
                                                WHERE cc.id IN (SELECT id FROM {course_categories} WHERE parent = ?) AND m.name = "quiz"
                                                AND lm.fieldid = ? AND lm.data = ? AND cm.id IS NOT NULL LIMIT 1',
            [$parentcategoryid, $metadatafieldid, $mid]);
    }
}

function local_diagnotic_external_sharewith_callback_clusters($metadataobj, $newcmid) {
    \local_clusters\clusters::add_cluster(
        $metadataobj->clusterdata->courseid,
        $metadataobj->clusterdata->source,
        $metadataobj->clusterdata->sourcecmid,
        $metadataobj->clusterdata->mid,
        $newcmid,
        $metadataobj->clusterdata->clusternum,
        $metadataobj->clusterdata->attempt,
        $metadataobj->clusterdata->description,
        $metadataobj->clusterdata->recommend,
        $metadataobj->clusterdata->userids,
        $metadataobj->clusterdata->type,
        $metadataobj->clusterdata->question
    );
}

function local_diagnotic_rebuild($cmids, $rebuild = false) {
    global $CFG, $DB;
    $metadatafieldid = $DB->get_field('local_metadata_field', 'id', ['shortname' => 'ID']);
    raise_memory_limit(MEMORY_UNLIMITED);

    foreach ($cmids as $cmid) {
        $mid = $DB->get_field('local_metadata', 'data', ['instanceid' => $cmid, 'fieldid' => $metadatafieldid]);
        if (empty($mid)) {
            mtrace('MID of the ' . $cmid . " is not exists");
        }
        $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);
        $params = \local_diagnostic_external::get_custom_settings($mid);
        $rebuildwithtime = 0;
        if ($cache && empty($cache->get('buildtime')) &&
                (!empty($params['customsettings']['startdate']) || !empty($params['customsettings']['enddate']))) {
            $cache->delete();
            $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);
            $rebuildwithtime = 1;
        }

        $params['recache'] = true;
        $params['rdebug'] = false;
        $params['cache'] = $cache;
        $params['rebuild'] = !empty($cache) ? $cache->get('rebuild') : 1;
        if ($rebuild) {
            $params['rebuild'] = true;
        }
        $params['mid'] = $mid;
        $params['cmids'] = [$cmid];
        $params['cmid'] = $cmid;

        $params['repocmid'] = \local_diagnostic_external::get_repo_cmid($mid);

        if ($rebuildwithtime || (empty($cache) && !empty($params['customsettings']['enddate']))) {
            $params['buildtime'] = date("d-m-Y", $params['customsettings']['enddate']);
            \local_diagnostic_external::process($params);
            unset($params['buildtime']);
            $cache = \local_diagnostic\cache::get_record(['mid' => $mid]);
            $params['cache'] = $cache;
        }

        \local_diagnostic_external::process($params);
    }
}

function get_stamps_by_latest_version($questionswithversion) {
    $questionversions = $idnumbers = $qvids = [];
    foreach ($questionswithversion as $questiondata) {
        if (!isset($questionversions[$questiondata->slot]) || $questionversions[$questiondata->slot] < $questiondata->version) {
            $questionversions[$questiondata->slot] = $questiondata->version;
            $idnumbers[$questiondata->slot] = $questiondata->idnumber;
            $qvids[$questiondata->slot] = $questiondata->id;
        }
    }

    return array($idnumbers, $qvids);
}
