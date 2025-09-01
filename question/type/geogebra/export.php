<?php

/**
 * Defines the editing form for the geogebra question type.
 *
 * @package        qtype
 * @subpackage     geogebra
 * @author         Devlion <info@devlion.co>
 * @copyright  (c) International GeoGebra Institute 2014
 * @license        http://www.geogebra.org/license
 */
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

$id = required_param('id', PARAM_INT); // question id
$cmid = required_param('cmid', PARAM_INT);

list($module, $cm) = get_module_from_cmid($cmid);
require_login($cm->course, true, $cm);
$context = context_module::instance($cmid);

if (!has_capability('moodle/question:add', $context)) {
    send_file_not_found();
}
if ($record = $DB->get_record('qtype_geogebra_options', ['questionid' => $id])) {
    $filename = 'geogebra-export_' . $id . '_' . time() . '.ggb';
    $ggbparameters = json_decode($record->ggbparameters);
    if (!empty($ggbparameters->ggbBase64)) {
        send_file(base64_decode(str_replace('data:application/octet-stream;base64,', '', $ggbparameters->ggbBase64)), $filename, 0, 0, true, true);
        die();
    }
}
send_file_not_found();


