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

require_once(__DIR__.'/../../config.php');

require_login();

$context = context_course::instance(1);
$PAGE->set_context($context);

$PAGE->set_url('/local/redmine/search_issues.php');
$PAGE->set_title('Search redmine issues');
$PAGE->set_heading(get_string('myissues', 'local_redmine'));

echo $OUTPUT->header();

$fullusername = optional_param('fullusername', '', PARAM_TEXT);
if (empty($fullusername)) {
    $searchfor = $USER->firstname.' '.$USER->lastname;
} else {
    $searchfor = $fullusername;
}

echo html_writer::start_div('results');

require_once(__DIR__ . '/vendor/autoload.php');

$client = new \Redmine\Client\NativeCurlClient(
    get_config('local_redmine', 'redmineurl'),
    get_config('local_redmine', 'redmineusername'),
    get_config('local_redmine', 'redminepassword'));

if (!empty($CFG->proxyhost)) {
    $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
}
// We use Redmine v2 API
// https://github.com/kbsali/php-redmine-api/blob/v2.x/docs/usage.md
$search_results = $client->getApi('search')->search($searchfor, ['limit' => 50]);
//print_object($search_results['results']);die;

echo html_writer::start_div('accordion', ['id' => 'accordion_rm_search_results']);
    foreach ($search_results['results'] as $issue) {
        echo html_writer::start_div('card');
            echo html_writer::start_div('card-header', ['id' =>'heading'.$issue['id'], 'data-target'=>'#collapsed'.$issue['id']]);
                $button = html_writer::tag('button', $issue['title'],
                    ['type'=>'button', 'data-toggle'=>'collapsed', 'data-target'=>'#collapsed'.$issue['id'],
                    'aria-expanded'=> 'true', 'aria-controls'=>'collapsed'.$issue['id']]);
                echo html_writer::tag('span', $button, ['class'=>'mb-0']);
            echo html_writer::end_div();
            echo html_writer::start_div('collapsed hide', ['id' =>'collapsed'.$issue['id'],
                'aria-labelledby'=>'heading'.$issue['id'], 'data-parent'=>'accordion']);
                $desc = preg_replace('/\*(.*)\*/U', '<br><b>$1</b>', $issue['description']);
                $desc = str_replace('Mentor', '<br>Mentor', $desc);
                $desc_array = str_split($desc, stripos($desc, 'IP'));
                $desc = array_shift($desc_array);
                echo html_writer::div($desc, 'card-body');
            echo html_writer::end_div();
        echo html_writer::end_div();
    }
echo html_writer::end_div();

$PAGE->requires->js_amd_inline("require(['jquery'], function($) {
    //console.log( 'Loading BS accordion...' );

    $('#accordion_rm_search_results .card-header').on('click', function(e){
        $($(e.currentTarget).data('target')).toggleClass('hide show');
    });
   
});");

echo $OUTPUT->footer();