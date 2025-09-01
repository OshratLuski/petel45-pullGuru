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
 * Lists all the users within a given course.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

if(!isset($CFG->custom_participiants_page_enable) || $CFG->custom_participiants_page_enable != true){
    require_once($CFG->dirroot.'/customscripts/user/index_original.php');
    exit;
}

// Get count of participants.
$sql = "
            SELECT u.id FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {user} u ON ue.userid = u.id    
            JOIN {role_assignments} ra ON (u.id = ra.userid)
            JOIN {context} c ON (ra.contextid = c.id AND c.instanceid = :instanceid)
            GROUP BY u.id                        
        ";

$params['instanceid'] = optional_param('id', 0, PARAM_INT);

$countparticipants = count($DB->get_records_sql($sql, $params));

if(isset($CFG->course_participants_cutoff) && $countparticipants > $CFG->course_participants_cutoff ){
    require_once($CFG->dirroot.'/customscripts/user/index_original.php');
    exit;
}

require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/notes/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot . '/local/petel/locallib.php');

use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

$participantsperpage = intval(get_config('moodlecourse', 'participantsperpage'));
define('DEFAULT_PAGE_SIZE', (!empty($participantsperpage) ? $participantsperpage : 20));

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required.
$newcourse    = optional_param('newcourse', false, PARAM_BOOL);
$roleid       = optional_param('roleid', 0, PARAM_INT);
$urlgroupid   = optional_param('group', 0, PARAM_INT);

$PAGE->set_url('/user/index.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'contextid' => $contextid,
        'id' => $courseid,
        'newcourse' => $newcourse));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new \moodle_exception('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
// Not needed anymore.
unset($contextid);
unset($courseid);

require_login($course);

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    course_require_view_participants($context);
}

// Trigger events.
user_list_view($course, $context);

$PAGE->set_title("$course->fullname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-participants');
$PAGE->set_docs_path('enrol/users');
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

// Expand the users node in the settings navigation when it exists because those pages
// are related to this one.
$node = $PAGE->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
if ($node) {
    $node->force_open();
}

echo $OUTPUT->header();

$participanttable = new \core_user\table\participants("user-index-participants-{$course->id}");

// Manage enrolments.
$manager = new course_enrolment_manager($PAGE, $course);
$enrolbuttons = $manager->get_manual_enrol_buttons();
$enrolrenderer = $PAGE->get_renderer('core_enrol');
$enrolbuttonsout = '';
foreach ($enrolbuttons as $enrolbutton) {
    $enrolbuttonsout .= $enrolrenderer->render($enrolbutton);
}

echo $OUTPUT->heading(get_string('enrolledusers', 'enrol'));

$filterset = new \core_user\table\participants_filterset();
$filterset->add_filter(new integer_filter('courseid', filter::JOINTYPE_DEFAULT, [(int)$course->id]));

$canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
$filtergroupids = $urlgroupid ? [$urlgroupid] : [];

// Force group filtering if user should only see a subset of groups' users.
if ($course->groupmode != NOGROUPS && !$canaccessallgroups) {
    if ($filtergroupids) {
        $filtergroupids = array_intersect(
            $filtergroupids,
            array_keys(groups_get_all_groups($course->id, $USER->id))
        );
    } else {
        $filtergroupids = array_keys(groups_get_all_groups($course->id, $USER->id));
    }

    if (empty($filtergroupids)) {
        if ($course->groupmode == SEPARATEGROUPS) {
            // The user is not in a group so show message and exit.
            echo $OUTPUT->notification(get_string('notingroup'));
            echo $OUTPUT->footer();
            exit();
        } else {
            $filtergroupids = [(int) groups_get_course_group($course, true)];
        }
    }
}

// Apply groups filter if included in URL or forced due to lack of capabilities.
if (!empty($filtergroupids)) {
    $filterset->add_filter(new integer_filter('groups', filter::JOINTYPE_DEFAULT, $filtergroupids));
}

// Display single group information if requested in the URL.
if ($urlgroupid > 0 && ($course->groupmode != SEPARATEGROUPS || $canaccessallgroups)) {
    $grouprenderer = $PAGE->get_renderer('core_group');
    $groupdetailpage = new \core_group\output\group_details($urlgroupid);
    echo $grouprenderer->group_details($groupdetailpage);
}

// Filter by role if passed via URL (used on profile page).
if ($roleid) {
    $viewableroles = get_profile_roles($context);

    // Apply filter if the user can view this role.
    if (array_key_exists($roleid, $viewableroles)) {
        $filterset->add_filter(new integer_filter('roles', filter::JOINTYPE_DEFAULT, [$roleid]));
    }
}

// Render the user filters.
$userrenderer = $PAGE->get_renderer('core_user');

// PTL-6239.
$perpage = 0;
echo '
    <style>
        .pagination.pagination-centered{
            display: none;
        }
        
        .commands, .resettable, .initialbar.firstinitial, .initialbar.lastinitial {
            display: none !important;
        }    
        
        #showall, .filter-group{
            display: none !important;
        }  
        
        .no-overflow .generaltable{
            min-height: 0 !important;
        }
                          
    </style>
';

echo $userrenderer->participants_filter($context, $participanttable->uniqueid);

echo $OUTPUT->render_participants_tertiary_nav($course, html_writer::div($enrolbuttonsout, '', [
        'data-region' => 'wrapper',
        'data-table-uniqueid' => $participanttable->uniqueid,
]));

echo '<div class="userlist">';

// Build filter.
$filter = optional_param('filter', 0, PARAM_INT);

$v = get_user_preferences('participant_filter_'.$course->id, 0);
if(!$filter && $v){
    $filter = $v;
}

// Default.
if(!$filter){
    $default = get_config('local_petel', 'participiant_filter');
    if($default){
        $filter = $default;
    }else{
        $filter = PP_ALL;
    }
}

// Set user preference.
set_user_preferences(['participant_filter_'.$course->id => $filter]);

$selectoptions = [
        PP_ACTIVE_STUDENTS => get_string('ppactivestudents', 'local_petel'),
        PP_ALL_STUDENTS => get_string('ppallstudents', 'local_petel'),
        PP_ACTIVE_STUDENTS_AND_TEACHERS => get_string('ppactivestudentsandteachers', 'local_petel'),
        PP_SUSPENDED_USERS => get_string('ppsuspendedusers', 'local_petel'),
        PP_FELLOW_TEACHERS => get_string('ppfellowteachers', 'local_petel'),
        PP_TEACHERS_PAYOFF => get_string('ppteacherspayoff', 'local_petel'),
        PP_TEACHER_DOES_NOT_EDIT => get_string('ppteacherdoesnotedit', 'local_petel'),
];

foreach(get_admins() as $admin){
    if($admin->id == $USER->id){
        $selectoptions[PP_NO_PERSONAL_CATEGORY] = get_string('ppnopersonalcategory', 'local_petel');
        break;
    }
}

$selectoptions[PP_ALL] = get_string('ppall', 'local_petel');

echo html_writer::start_tag('div', array('class' => 'd-flex justify-content-between align-items-center flex-row-reverse'));
echo html_writer::start_tag('div', array('class' => 'filter-participiants-wrapper d-flex justify-content-end pt-2'));

echo html_writer::start_tag('div', array('class' => 'd-flex align-items-center'));
echo html_writer::start_tag('div', ['class' => 'mr-2']);
echo get_string('filterlabel', 'local_petel');
echo html_writer::end_tag('div');

echo html_writer::start_tag('select', array('class' => 'filter-participiants custom-select', 'id' => 'filter-participiants'));

foreach($selectoptions as $value => $optionname){
    $paramsoption = ['value' => $value];

    if($value == $filter){
        $paramsoption['selected'] = 1;
    }
    echo html_writer::tag('option', $optionname, $paramsoption);
}

echo html_writer::end_tag('select');
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo html_writer::start_tag('div', array('class' => 'd-flex justify-content-between align-items-center position-relative'));
echo html_writer::tag('input', '', [
        'class' => 'form-control main-search-input',
        'id' => 'search-participiants',
        'value' => '',
        'placeholder' => get_string('searchplaceholder', 'local_petel')
]);

echo html_writer::start_tag('div', ['class' => 'position-absolute border-0 petel-search-btn']);
echo html_writer::tag('i', '', ['class' => 'fa-sharp fa-solid fa-magnifying-glass','id' => 'search-participiants',]);
echo html_writer::end_tag('div');


echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
$urlparams = $PAGE->url->params();
unset($urlparams['filter']);

$newurl = new moodle_url($PAGE->url, $urlparams);

$newhead = get_string('actions');

$PAGE->requires->js_amd_inline("
    require(['jquery', 'core/loadingicon'], function($, LoadingIcon) {
    
        function showTable(){
            $('#participiants-table-block').show();
            $('#loading-block').hide();           
        }
        
        function hideTable(){
            $('#participiants-table-block').hide();
            $('#loading-block').show();           
        }        
    
        function searchInTable(value){
            let table = $('#participants').find('tbody'); 
                  
            if(value.length !== 0){
                table.find('tr').each(function() {
                    let tr = $(this);
                    let flag = false;
                    
                    // Show tr.
                    tr.show();
                                        
                    tr.find('.cell').each(function(index) {                    
                        let td = $(this);
                        
                        let string = '';
                        switch(index) {
                            case 1:
                            case 3:
                            case 4:
                                string = td.find('a').text();                                
                            break;
                            
                            case 2:
                            case 5:
                                //string = td.text();                                                                                             
                            break;
                            
                            case 6:
                                string = td.find('div').data('status');                                                             
                            break;                            
                        }
                                                
                        if (string !== undefined && string.toLowerCase().indexOf(value.toLowerCase()) >= 0){
                            flag = true;
                        }                        
                    })
                    
                    if(!flag){
                        tr.hide();
                    }                   
                });
            }else{
                table.find('tr').show();
            }
            
            // Change total string.
            setTimeout(function() {
                let count = changeTotalCount();
                let tag = $('*[data-region=".'"participant-count"'."]');
                let str = tag.text().replace(/[0-9]/g, '');                                                        
                tag.text(count + ' ' + str);
            }, 100);            
        }
        
        function changeTotalCount(){
            let table = $('#participants').find('tbody');
            let count = 0;
            
            table.find('tr').each(function() {
                if($(this).is(':visible')){
                    count++;
                }
            })
        
            return count;
        }
        
        function divideColumnStatusAndShowTable(){
        
            // Add header of new column.
            $('#participants').find('thead tr .head-last').remove();
            
            // Remove show count button.            
            $('*[data-action=showcount]').remove();
            
            let parenthead = $('#participants').find('thead tr');
            let clonehead = $('#participants').find('thead tr th').last().clone().appendTo(parenthead);
            
            clonehead.addClass('head-last');
            clonehead.text('".$newhead."');            
            
            $('#participants').find('tbody tr .cell-last').remove();
                    
            $('#participants').find('tbody tr').each(function( index ) {
                let self = $(this).find('td').last();
                
                // Clone element.
                let parent = $(self).parent();
                let clone = $(self).clone().appendTo(parent);
                clone.addClass('cell-last');
                clone.find('a').show();
                                
                // Change functionality of actions.
                let showdetails = clone.find('*[data-action=".'showdetails'."]');
                showdetails.removeAttr('data-action');
                
                showdetails.click(function() {
                  $(self).find('*[data-action=".'showdetails'."]').find('i').trigger('click');                  
                });
                
                // Remove not needed elements.
                $(self).find('a').hide();                
                clone.find('span').remove();
                
                // Show table.
                showTable();   
            });
        }
        
        // Enable loading block.
        LoadingIcon.addIconToContainerWithPromise($('#loading-block'));
        
        if($('#participants').length !== 0){        
            showTable();
        }else{
            hideTable();      
        }
            
        // Event on change.
        $('#filter-participiants').on('change', function() {
            window.location.href = '".$newurl->out(false)."' + '&filter=' + this.value;          
        });
        
        // Divide colums and show table.
        divideColumnStatusAndShowTable();
        
        // Default for search.
        let searchParams = new URLSearchParams(window.location.search);
        
        if(searchParams.has('search')){
            let search = searchParams.get('search');
            $('#search-participiants').val(search);
            searchInTable(search);
        }else{
            $('#search-participiants').val('');
        }
        
        // Event on search.
        $('#search-participiants').on('input', function() {        
            let search = $(this).val();
            searchInTable(search);                        
        });
        
        // Listen to AJAX calls. Hide table.
        $(document).ajaxSend(function(event,request, settings){
            if(settings.type === 'POST' && settings.data !== undefined && settings.data.length !== 0){
                let data = JSON.parse(settings.data);                
                if(data[0] !== undefined){                
                    if(data[0].methodname === 'core_table_get_dynamic_table_content' || data[0].methodname === 'core_update_inplace_editable'){
                                         
                        hideTable();
                    }                    
                }
            }            
        });
        
        
        // Listen to AJAX calls.
        $(document).ajaxComplete(function(event,request, settings){
            if(settings.type === 'POST' && settings.data !== undefined && settings.data.length !== 0){
                
                setTimeout(function() {
                    if($('#participants').length === 0){
                        showTable();
                    }
                }, 200);
                
                let data = JSON.parse(settings.data);                
                if(data[0] !== undefined){                
                    if(data[0].methodname === 'core_table_get_dynamic_table_content' || data[0].methodname === 'core_update_inplace_editable'){
                                         
                        setTimeout(function() {
                            let search = $('#search-participiants').val();                            
                            searchInTable(search);
                            
                            divideColumnStatusAndShowTable();                                                      
                        }, 0);                        
                    }                    
                }               
            }            
        });
        
    });
");

// Do this so we can get the total number of rows.
ob_start();
$participanttable->set_filterset($filterset);

echo '<div id="loading-block"></div>';

echo '<div id="participiants-table-block" style="display: none;">';

$participanttable->out($perpage, true);

echo '</div>';

$participanttablehtml = ob_get_contents();
ob_end_clean();

echo html_writer::start_tag('form', [
    'action' => 'action_redir.php',
    'method' => 'post',
    'id' => 'participantsform',
    'data-course-id' => $course->id,
    'data-table-unique-id' => $participanttable->uniqueid,
]);
echo '<div>';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
echo '<input type="hidden" name="returnto" value="'.s($PAGE->url->out(false)).'" />';

echo html_writer::tag(
    'p',
    get_string('countparticipantsfound', 'core_user', $participanttable->totalrows),
    [
        'data-region' => 'participant-count',
    ]
);

echo $participanttablehtml;

$bulkoptions = (object) [
    'uniqueid' => $participanttable->uniqueid,
];

echo '<br /><div class="buttons"><div class="d-flex flex-wrap align-items-center">';

echo html_writer::start_tag('div', array('class' => 'btn-group'));

//if ($participanttable->get_page_size() < $participanttable->totalrows) {
//    // Select all users, refresh table showing all users and mark them all selected.
//    $label = get_string('selectalluserswithcount', 'moodle', $participanttable->totalrows);
//    echo html_writer::empty_tag('input', [
//        'type' => 'button',
//        'id' => 'checkall',
//        'class' => 'btn btn-secondary',
//        'value' => $label,
//        'data-target-page-size' => TABLE_SHOW_ALL_PAGE_SIZE,
//    ]);
//}
echo html_writer::end_tag('div');
$displaylist = array();
if (!empty($CFG->messaging) && has_all_capabilities(['moodle/site:sendmessage', 'moodle/course:bulkmessaging'], $context)) {
    $displaylist['#messageselect'] = get_string('messageselectadd');
}
if (!empty($CFG->enablenotes) && has_capability('moodle/notes:manage', $context) && $context->id != $frontpagectx->id) {
    $displaylist['#addgroupnote'] = get_string('addnewnote', 'notes');
}

$params = ['operation' => 'download_participants'];

$downloadoptions = [];
$formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
foreach ($formats as $format) {
    if ($format->is_enabled()) {
        $params = ['operation' => 'download_participants', 'dataformat' => $format->name];
        $url = new moodle_url('bulkchange.php', $params);
        $downloadoptions[$url->out(false)] = get_string('dataformat', $format->component);
    }
}

if (!empty($downloadoptions)) {
    $displaylist[] = [get_string('downloadas', 'table') => $downloadoptions];
}

if ($context->id != $frontpagectx->id) {
    $instances = $manager->get_enrolment_instances();
    $plugins = $manager->get_enrolment_plugins(false);
    foreach ($instances as $key => $instance) {
        if (!isset($plugins[$instance->enrol])) {
            // Weird, some broken stuff in plugin.
            continue;
        }
        $plugin = $plugins[$instance->enrol];
        $bulkoperations = $plugin->get_bulk_operations($manager);

        $pluginoptions = [];
        foreach ($bulkoperations as $key => $bulkoperation) {
            $params = ['plugin' => $plugin->get_name(), 'operation' => $key];
            $url = new moodle_url('bulkchange.php', $params);
            $pluginoptions[$url->out(false)] = $bulkoperation->get_title();
        }
        if (!empty($pluginoptions)) {
            $name = get_string('pluginname', 'enrol_' . $plugin->get_name());
            $displaylist[] = [$name => $pluginoptions];
        }
    }
}

$selectactionparams = array(
    'id' => 'formactionid',
    'class' => 'ms-2',
    'data-action' => 'toggle',
    'data-togglegroup' => 'participants-table',
    'data-toggle' => 'action',
    'disabled' => 'disabled'
);
$label = html_writer::tag('label', get_string("withselectedusers"),
        ['for' => 'formactionid', 'class' => 'col-form-label d-inline']);
$select = html_writer::select($displaylist, 'formaction', '', ['' => 'choosedots'], $selectactionparams);
echo html_writer::tag('div', $label . $select);

echo '<input type="hidden" name="id" value="' . $course->id . '" />';
echo '<div class="d-none" data-region="state-help-icon">' . $OUTPUT->help_icon('publishstate', 'notes') . '</div>';
echo '</div></div></div>';

$bulkoptions->noteStateNames = note_get_state_names();

echo '</form>';

$defaultrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
$defaultrole->name = !empty($defaultrole->name) ? $defaultrole->name : get_string('defaultcourseteacher');

$defaults = [
        'categories_ac' => ['name' => '', 'value' => ''],
        'courses_ac' => ['name' => '', 'value' => ''],
        'roles_ac' => [
                'name' => $defaultrole->name,
                'value' => $defaultrole->id
        ],
];

$bulkoptions->currentcourseid = $PAGE->course->id;
$bulkoptions->currentuserid = $USER->id;
$bulkoptions->defaults = $defaults;

$PAGE->requires->js_call_amd('local_petel/action_participants_custom', 'init', [$bulkoptions]);

echo '</div>';  // Userlist.

$enrolrenderer = $PAGE->get_renderer('core_enrol');
// Need to re-generate the buttons to avoid having elements with duplicate ids on the page.
$enrolbuttons = $manager->get_manual_enrol_buttons();
$enrolbuttonsout = '';
foreach ($enrolbuttons as $enrolbutton) {
    $enrolbuttonsout .= $enrolrenderer->render($enrolbutton);
}
echo html_writer::div($enrolbuttonsout, 'd-flex justify-content-end', [
    'data-region' => 'wrapper',
    'data-table-uniqueid' => $participanttable->uniqueid,
]);

echo $OUTPUT->footer();
exit;
