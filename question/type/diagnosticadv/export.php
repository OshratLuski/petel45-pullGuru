<?php
require_once __DIR__ . '/../../../config.php';
require_once($CFG->libdir . '/pdflib.php');
defined('MOODLE_INTERNAL') || die();

use qtype_diagnosticadv\event\ai_analytics_exportpdf;

$contextid = required_param('context', PARAM_INT);
$objectid = required_param('objectid', PARAM_INT);
require_login();
$context = \context::instance_by_id($contextid);
require_capability('mod/quiz:viewreports', $context);
$sql = "SELECT id, timecreated, other
                  FROM  {logstore_standard_log} 
                  where contextid = :contextid and objectid = :objectid and target = :target and  action = :action
                  order by id DESC limit 1";
$eventparams = [
        'contextid' => $contextid,
        'objectid' => $objectid,
        'target' => 'ai_analytics',
        'action' => 'created'
];

$log = $DB->get_record_sql($sql, $eventparams);

$data = [];
if (!empty($log->other)) {
    $other = json_decode($log->other, true);
    $data['aipromt'] = $other['promt'];
    $data['airesult'] = $other['result'];
    $data['userdata'] = !empty($other['userdata']) ? implode("\n", array_slice(explode("\n", $other['userdata']), 1)) : "";
}
$data['timecreated'] = date("Y-m-d H:i", $log->timecreated);

$questionoptions = \qtype_diagnosticadv\options::get_record(['questionid' => $objectid]);
$questionoptions->get('promt');
$data['teacherpromt'] = str_replace('{{LOG}}', $data['userdata'], $questionoptions->get('promt'));
$data['teacherpromt'] = str_replace("\n", "<br>", $data['teacherpromt']);
$mainContent = $OUTPUT->render_from_template('qtype_diagnosticadv/exportpdf', $data);

$PAGE->set_context($contextid);
$PAGE->set_url(new moodle_url('question/type/diagnosticadv/export.php')); // Adjust the path to where this file is stored.
$PAGE->set_pagelayout('standard');

// Set up document properties.
$pdf = new \pdf();
$pdf->setPrintHeader(false); // Disable header in PDF.
$pdf->setPrintFooter(false); // Disable footer in PDF.
$pdf->SetTitle(get_string('pluginname', 'qtype_diagnosticadv'));

// Set default font and margins.
$pdf->SetMargins(10, 30, 10); // Left, Top, Right margins in mm.
$pdf->SetFont('freesans', '', 10); // Use the Moodle default sans-serif font. You can use 'freeserif' for serif fonts.

$pdf->AddPage('L'); // 'L' sets the orientation to landscape. Use 'P' for portrait.

// Add the main content for the PDF.
$pdf->writeHTML($mainContent); // Use writeHTML for formatted content (HTML + inline CSS).
$pdf->setRTL(true);
$event = ai_analytics_exportpdf::create([
        'objectid' => $objectid,
        'context' => $context
]);
$event->trigger();

// Output the PDF document.
$pdf->Output('example_export_' . time() . '.pdf', 'D'); // 'D' forces the download of the file.
exit; // Ensure no further output is sent after PDF generation.