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
 * Plugin administration pages are defined here.
 *
 * @package     local_redmine
 * @category    support
 * @copyright   2021 <nadav.kavalerchik@weizmann.ac.il>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/redmine/vendor/autoload.php');
include_once($CFG->dirroot . '/local/redmine/locallib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$navdraweropen = get_user_preferences('drawer-open-nav') == 'true' ? "true" : "false";
set_user_preference('drawer-open-nav', "false");

$PAGE->set_url('/local/redmine/index.php', []);

$strname = get_string('plugintitle', 'local_redmine');
$PAGE->navbar->add($strname);
$PAGE->set_title($strname);

if($issueid = optional_param('id', 0, PARAM_INT)){
    $params = ['issueid' => $issueid];
}else{
    $params = [];
}

// Check permission.
if (!get_config('local_redmine', 'redminestatus') || !\local_petel\funcs::user_admin_or_teacher()) {
    throw new \moodle_exception('Permission denied');
}

// Check permission.
if($issueid) {
    $isauthor= false;

    $client = new \Redmine\Client\NativeCurlClient(
            get_config('local_redmine', 'redmineurl'),
            get_config('local_redmine', 'redmineusername'),
            get_config('local_redmine', 'redminepassword'));

    if (!empty($CFG->proxyhost)) {
        $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost . ':' . $CFG->proxyport);
    }

    // Get from opened issues.
    $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'opened']);

    // Or get from closed issues.
    if (empty($data['issues'])) {
        $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'closed']);
    }

    if (empty($data['issues'])) {
        throw new \moodle_exception('No matching id in Redmine');
    }

    if (isset($data['issues'][0])) {
        $issue = $data['issues'][0];

        if (strpos($issue['description'], \local_redmine::userQuery()) !== false) {
            $isauthor = true;
        }
    }

    if(!$isauthor && !is_siteadmin() && !user_has_role_assignment($USER->id, 1 /* manager */, $PAGE->context->id)){
        throw new \moodle_exception('Permission denied');
    }
}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_redmine/issues/main', $params);

echo $OUTPUT->footer();