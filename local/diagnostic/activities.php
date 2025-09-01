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
 * Redirect to question preview.
 *
 * @package    local_diagnostic
 * @copyright  2024 Devlion Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/classes/forms/midform.php');

require_login();
if (!is_siteadmin()) {
    $systemcontext = context_system::instance();

    $userroles = array_map(function ($assignment) {
        return $assignment->shortname;
    }, get_user_roles($systemcontext));

    if (!in_array('manager', $userroles)) {
        throw new \moodle_exception('nopermissions');
    }
}

$custommid = required_param('mid', PARAM_TEXT);

$PAGE->set_context($systemcontext);
$contextid = context_system::instance()->id;
$PAGE->set_url('/local/diagnostic/activities.php');

$mform = new midform($CFG->wwwroot . '/local/diagnostic/activities.php?mid=' . $custommid);

if ($fromform = $mform->get_data()) {
    file_save_draft_area_files(
        file_get_submitted_draft_itemid('local_diagnostic/midfile' . $custommid),
        $contextid,
        'local_diagnostic',
        'midfile',
        $custommid,
        [
            'maxfiles' => 1,
        ]
    );

    $clusters = get_config('local_diagnostic', 'activityclusternum_' . $custommid);

    if ($fromform->{'local_diagnostic/modelenabled' . $custommid} === 1) {
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        if (!(in_array($custommid, $croncustommids))) {
            $croncustommids[] = $custommid;
        }
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        if (!(in_array($custommid, $custommids))) {
            $custommids[] = $custommid;
        }
        set_config('custommids', implode(',', $custommids), 'local_diagnostic');
        set_config('activityclusternum_' . $custommid, $clusters, 'local_diagnostic');
    } else {
        $config = get_config('local_diagnostic');
        $croncustommids = explode(',', $config->croncustommids);
        $croncustommids = array_diff($croncustommids, [$custommid]);
        set_config('croncustommids', implode(',', $croncustommids), 'local_diagnostic');
        $custommids = explode(',', $config->custommids);
        $croncustommids = array_diff($custommids, [$custommid]);
        set_config('custommids', implode(',', $croncustommids), 'local_diagnostic');
    }

    foreach ($fromform as $key => $value) {
        $configname = isset(explode('/', $key)[1]) ? explode('/', $key)[1] : false;
        $pluginname = explode('/', $key)[0];
        if ($configname) {
            set_config($configname, $value, $pluginname);
        }
    }

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'local_diagnostic', 'midfile', $custommid, "filename");
    $file = isset(array_values($files)[1]) ? array_values($files)[1] : false;
    $filename = '';
    if ($file) {
        $filename = $file->get_filename();
    }
    set_config('midfile' . $custommid, '/' . $filename, 'local_diagnostic');
    $factory = cache_factory::instance();
    $definition = $factory->create_definition('core', 'config');
    if ($definition->has_required_identifiers()) {
        // We will have to purge the stores used by this definition.
        cache_helper::purge_stores_used_by_definition('core', 'config');
    } else {
        // Alrighty we can purge just the data belonging to this definition.
        cache_helper::purge_by_definition('core', 'config');
    }
    redirect($CFG->wwwroot . '/local/diagnostic/activities.php?mid=' . $custommid);
}

$PAGE->set_title('Mid ' . $custommid . ' customization');
echo $OUTPUT->header();

echo $mform->render();

echo $OUTPUT->footer();