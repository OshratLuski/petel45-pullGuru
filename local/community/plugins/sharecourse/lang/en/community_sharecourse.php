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
 * Plugin strings are defined here.
 *
 * @package     community_sharecourse
 * @category    string
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Sharing courses';
$string['taskscopycourse'] = 'Task copy course';

// Access.
$string['sharecourse:coursecopy'] = 'Course Copy';

// Settings.
$string['availabletocohort'] = 'Share course to OER catalog';
$string['availabletocohortdesc'] = 'Share course to OER catalog available to cohort members';
$string['settingsshownonrequieredfields'] = 'Button for more options';
$string['settingsshownonrequieredfieldsdesc'] = 'Enable/disable button for more options';
$string['oercoursecohortrole'] = 'Select role for enrol course';
$string['oercoursecohortroledesc'] = 'Select role for enrol course';
$string['oercoursecohort'] = 'Select cohort for enrol course';
$string['oercoursecohortdesc'] = 'Select cohort for enrol course';
$string['oercoursesharevisible'] = 'Course share visible?';
$string['oercoursesharevisibledesc'] = 'Will a course be shared to the shared database in visible or hidden mode.';

// Events.
$string['course_share'] = 'Share course to catalog';
$string['course_unshare'] = 'Unshare course to catalog';
$string['eventcoursecopy'] = 'Copy course';

// Main popup.
$string['menupopuptitle'] = 'Select the desired action';
$string['cancel'] = 'Cancel';
$string['buttonshare'] = 'Share course';
$string['menucoursenode'] = 'Copy course';
$string['courseuploadtocatalog'] = 'Upload course to catalog';
$string['sharecoursepombim'] = 'Environment of colleagues';
$string['coursereuploadtocatalog'] = 'Copy course';
$string['copycoursetoteacher'] = 'Share to teacher';
$string['sharecommunityteachers'] = 'Sharing with community teachers';
$string['sharecoursecommunity'] = 'Share to community';
$string['copycoursetocategory'] = 'To my environment';
$string['couse_copied_from_catalog'] =
        'Please note, this course is a database partner. Do you want to share the new course created, instead of the current old course?';

// Disable popup.
$string['buttonsharedcourse'] = 'Course shared';
$string['disablepopuptitle'] = 'Unshare course from catalog';
$string['disablepopupbody'] = 'Are you sure unshare course from catalog?';
$string['disablepopupsubmit'] = 'Yes';

// Upload catalog.
$string['share_course_catalog_title'] = 'Share course to catalog';
$string['share'] = 'Share';
$string['sharing_content_materials_repository'] =
        'Please pay attention! Once approved anyone who fits the target audience will be able to copy the course in full!';
$string['advanced_catalog_options'] = 'Advanced catalog options';
$string['coursenameinput'] = "Name of course";
$string['coursedescriptioninput'] = "Description of course";
$string['coursedescriptionlabel'] =
        "Please tell other teachers a few words about the teaching sequence, insights from running with students and your recommendations for successful running. The information will appear in the description of the sequence in the shared database";
$string['course_upload_to_mr'] = 'Course has been sent to the shared repository and will be available to all teachers as soon as possible
thanks for sharing!';
$string['eventcourseupload'] = 'Upload course';
$string['theme_of_the_course'] =
        'Theme of the course <span class="font-weight-normal">(select the second level from the drop-down menu)</span>';

// Share social.
$string['titlesharecoursepombim'] = "Sharing a course with colleagues";
$string['approval'] = "Approval";
$string['sharecoursesdesc'] = "Please confirm the sharing of the course with the colleagues.";

// Share to teacher.
$string['send'] = "Send";
$string['back'] = 'Back';
$string['end'] = 'End';
$string['copycoursesuccess'] = 'Pay attention! Some actions require a number of moments to complete.';
$string['eventdublicatetoteacher'] = "Copy course";
$string['subject_message_for_teacher'] = 'Teacher {$a->teachername} share to you course ';

// Copy course.
$string['selectioncategories'] = 'Select category';
$string['eventcoursecopy'] = 'Copy course';
$string['course_copied_to_category'] = 'Course is copied to the category';
$string['finish'] = 'Ok';
$string['sure_duplicate_course_without_students'] = 'Are you sure you want to duplicate the course without the students?';
$string['close'] = 'Close';
$string['copy'] = 'Copy';
$string['wordcopy'] = 'Copy';

// Messages.
$string['notificationmessage'] = '{$a->user} שיתף קורס למאגר המשותף. קישור לצפיה <a href="{$a->url}">"{$a->coursename}"</a>';
$string['subjectmail'] = '{$a->user} שיתף קורס למאגר המשותף.';
$string['community_sharecourse_copy_course_to_teacher'] = 'Copy course to teacher';

// Enrol.
$string['enrolname'] = 'System group Course catalog';
