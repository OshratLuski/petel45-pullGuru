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
 * @package local_diagnostic
 * @copyright 2021 Devlion.co
 * @author Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Diagnosing students according to their results';
$string['analytics'] = 'Analytics';
$string['activityrequired'] = 'Please select at least one activity';
$string['popuptip'] = 'Please set the parameters for creating the student groups (in the drop-down menus at the top of the page)
After completing the settings, the groups will be created on this screen';

$string['activitiesselected'] = 'Selected {$a} activities';
$string['noselection'] = 'No selection';
$string['teachers'] = 'Cohorts name';
$string['nostartend'] = 'No start or end date specified';
$string['success'] = 'Success';
$string['select'] = 'Select';
$string['submittext'] = 'Apply';
$string['submit'] = 'Submit';
$string['required'] = 'This field is required';

$string['daterange'] = 'Range of dates';
$string['events'] = 'Selection of activities';

$string['nan'] = 'NaN';
$string['courseids'] = 'CourseIDs';
$string['activityids'] = 'ActivityIDs';
$string['userid'] = 'UserID';
$string['cluster'] = 'Cluster';
$string['clustername'] = 'Cluster {$a}';
$string['avggrade'] = 'Average grade {$a}%';
$string['avggradetotal'] = 'Average grade {$a->total}% (Global {$a->totalall}%)';
$string['uid'] = 'UID{$a}';

$string['cohorts'] = 'Cohorts';

$string['yellow'] = 'Lowest yellow border, %';
$string['yellowdesc'] = 'Average percentages equal or above this number will be shown as yellow in datasheet table for clusters';
$string['green'] = 'Lowest green border, %';
$string['greendesc'] = 'Average percentages equal or above this number will be shown as green in datasheet table for clusters';
$string['questionname'] = 'Question name {$a}';

$string['slidertitle'] = 'Statistics';
$string['tabletitle'] = 'Statistical description of the groups';
$string['red'] = 'Low level';
$string['yellow'] = 'Medium level';
$string['green'] = 'High level';

$string['demomode'] = 'Demo mode';
$string['commontitle'] = 'Information and assignment of activities to the whole class';
$string['rectangletitle'] = 'Students who do not have enough data in the system to diagnose them';
$string['questionstitle'] = 'title';
$string['question'] = 'question {$a}';
$string['usertooltiptitle'] = 'Tooltip title';
$string['userlisttitle'] = 'Student names';
$string['gapestimate'] = 'Gap estimated number of clusters';

$string['excelcluster'] = 'Cluster {$a}';
$string['clusterinfo1'] = '</div>{$a}% ציון ממוצע<div>
<div>&nbsp;מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון רחב של תרחישים, יותר סטנדרטיים
 (2-11), או פחות סטנדרטיים (14-12). לעתים מחמיצים תרחיש נוסף
  בשאלות בהן יש מספר תרחישים אפשריים (1, 17) 
  או מחמיצים תשובה נוספת בשאלות לא סטנדרטיות בהן יש שתי תשובות נכונות (15, 16).</div>';
$string['clusterinfo2'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון רחב של תרחישים סטנדרטיים
 (2-4, 9-11), לעתים מתבטאת תפישה חלופית של &quot;כוח הנורמל שווה תמיד למשקל&quot; (5-8).
  מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8), לא מצליחים להתמודד בתרחיש לא סטנדרטי
   של החלקה אפשרית בכיוונים שונים (12-13, 15-17), 
  אלא אם ניתן להסתייע באינטואיציה (14).</p>';
$string['clusterinfo3'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים בהצלחה עם יישום עקרונות פיזיקליים במגוון צר של תרחישים סטנדרטיים
 (2-4). לעתים מתבטאת תפישה חלופית של &quot;כוח הנורמל שווה תמיד למשקל&quot;>
  (5-8). <>לא מצליחים להתמודד עם תרחיש של מערכת גופים
   (10-11), או עם תרחיש לא סטנדרטי של החלקה אפשרית בכיוונים שונים (12-17). 
   מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8)</p>';
$string['clusterinfo4'] = '</div>{$a} ציון ממוצע<div>
<p>מתמודדים באופן חלקי בעיקר עם שאלות בהן ניתן להסתייע באינטואיציה
 (9,11) או בידע קודם שאינו נוגע לחיכוך> (10), 
 אינם מצליחים ליישם עקרונות פיזיקליים לניתוח מרבית התרחישים. 
מפגינים כבר בשאלות סטנדרטיות תפישות חלופיות נפוצות
  דוגמת &quot;כוח הנורמל שווה תמיד למשקל&quot; &quot;
  כוח חיכוך סטטי שווה תמיד לערכו המקסימלי&quot; &quot;כוח הנורמל שווה תמיד למשקל&quot; (5-8, 1-3). 
  מחמיצים תרחיש נוסף בשאלות בהן יש מספר תרחישים אפשריים (1, 8).</p>';
$string['clusterinfo5'] = '</div>{$a} ציון ממוצע<div>
<p>אינם מצליחים ליישם עקרונות פיזיקליים לניתוח המצבים השונים.</p>';

$string['cluster_info'] = 'Cluster details';

$string['share'] = 'Share';
$string['shareactivitycourse'] = 'Share activity from my courses';
$string['clusternum'] = 'Number of clusters';
$string['clusternum_desc'] = 'Sets number of clusters, is overriden for certain MIDs if Activity number of clusters is set';
$string['enabletags'] = 'Enable Tag filter';
$string['allowedtags'] = 'Select Tags';
$string['allowedtags_desc'] = 'Activity chooser will only display quizzes with selected tags';
$string['viewemptyclusters'] = 'View empty clusters';

$string['shareactivityrepo'] = 'Share activity from repository';
$string['clusteractivities'] = 'Current clustered attached {$a} activities';

$string['globalview'] = 'Global view';
$string['noclusterdata'] = 'No analytics data for current activities';
$string['cachetask'] = 'Refresh attempts cache';

$string['export_excel'] = 'Export to excel';
$string['firstname_excelhdr'] = 'First name';
$string['lastname_excelhdr'] = 'Last name';
$string['group_excelhdr'] = 'Group';
$string['grade_excelhdr'] = 'Grade';
$string['gradecluster_excelhdr'] = 'Grade (cluster)';
$string['id_excelhdr'] = 'ID';
$string['email_excelhdr'] = 'Email';
$string['status_excelhdr'] = 'Status';
$string['started_excelhdr'] = 'Started at:';
$string['finished_excelhdr'] = 'Finished at:';
$string['duration_excelhdr'] = 'Duration';
$string['activities'] = 'Activities';
$string['average_percent_of_success'] = 'Average percent of success';

$string['custommids'] = 'MIDS to customize';
$string['custommidssesc'] = 'Comma-separated list of mids to customize their settings';
$string['cluster_text_area'] = 'cluster {$a}';
$string['cluster_text_area_desc'] = 'Add description to cluster {$a}';
$string['activityclusternum'] = 'MID {$a} - Number of clusters';
$string['activity_yellow'] = 'MID {$a} - Medium level';
$string['activity_green'] = 'MID {$a} - High level';
$string['activity_excluded_cmids'] = 'MID {$a} - Excluded CMIDs';
$string['activity_excluded_cmids_desc'] = 'MID {$a} - Enter list of excluded CMIDs';
$string['activity_excluded_questionids'] = 'MID {$a} - Excluded question ids';
$string['activity_excluded_questionids_desc'] = 'MID {$a} - these questions from MID, if found in participating CMIDs, will be skipped';
$string['activity_cutoff'] = 'MID {$a} - Cutoff';
$string['activity_cutoff_desc'] = 'MID {$a} - Enter cutoff value between 0 and 1';
$string['activity_startdate'] = 'MID {$a} - Start date';
$string['activity_startdate_desc'] = 'MID {$a} - Enter start diagnosing date';
$string['activity_enddate'] = 'MID {$a} - End date';
$string['activity_enddate_desc'] = 'MID {$a} - Enter end diagnosing date';
$string['circle_empty'] = 'Empty';

$string['submit'] = 'Submit';
$string['cancel'] = 'Cancel';
$string['share_activities_course_title'] = 'Assignment of a continuation activity from my course to the';
$string['share_activities_course_title_all'] = 'Assignment of a continuation activity from my course to';
$string['all_clusters'] = 'All Clusters';
$string['selected_from_my_courses'] = 'Selected {$a} activities from my courses';
$string['selected_from_repository'] = 'Selected {$a} activities from repository';
$string['selected_from_recom'] = 'Selected {$a} activities from recomendations';
$string['selected_previously'] = 'Previously Selected {$a} activities from all sources';
$string['severalattempts'] = 'Several attempts';
$string['iskmeans'] = 'K-means';
$string['description_title'] = 'Information about the future activity';
$string['description_text'] = 'Please explain further activities planned to help other teachers:';
$string['recommend_text'] = 'Would you allow other teachers to use this activity';
$string['recommend_repo_text'] = 'Would you recommend other teachers to use this activity';
$string['saved_successfully'] = 'Saved successfully';
$string['server_error'] = 'Server error';

/************************* Events *************************************************/

$string['addedbycentroidevent'] = 'Added by centroid event';
$string['popuploadedevent'] = 'Popup loaded event';
$string['clustersloadedevent'] = 'Clusters loaded event';
$string['excelexportedevent'] = 'Excel exported event';
$string['localclusterssubmittedevent'] = 'Local clusters submitted event';
$string['sharewithclusterssubmittedevent'] = 'Sharewith clusters submitted event';
$string['userdragdropevent'] = 'User drag drop event';

/**************************Recom***************************************************/
$string['shareactivityrecom'] = 'Advised Activities';
$string['activityname'] = 'Activity name';
$string['source'] = 'Source';

$string['descriptionheader'] = 'Information about cluster';

$string['activitiespopuptitle'] = 'Recommended activities from my courses';
$string['activitiestoptitle'] = 'Recommended activities from my courses for';

$string['recomandpopuptitle'] = 'Activities from my courses';
$string['recomandtoptitle'] = 'Activities from my courses for';

$string['repositorypopuptitle'] = 'Activities from repository';
$string['repositorytoptitle'] = 'Activities from repository for';

$string['repoquestionsonly'] = 'Repository questions only for MID {$a}';
$string['repoquestionsonlydesc'] = 'Only Quizzes with all repository questions included (quiz may contain more questions than its repository source, but not less)';

$string['mlnpmenutext'] = '{$a->quizname}(MLNP {$a->questionname})';
$string['rebuildlimit'] = 'Rebuild limit';
$string['rebuildlimitdesc'] = 'Number of attempts starting from which the script will not rebuild centroids';

$string['fixed'] = 'Fixed clusters';
$string['gapestimate'] = 'Gap estimated clusters';
$string['optimal'] = 'Optimal clusters';
$string['nmin'] = 'NMin parameter for optimal clusters';
$string['nmaxdesc'] = 'Maximum number of clusters for optimal script to offer';
$string['nmindesc'] = 'Minimum number of clusters for optimal script to offer';
$string['nmax'] = 'Maximum number of clusters for optimal script to offer';
$string['clusternummethod'] = 'Cluster quantity method';
$string['clusternummethoddesc'] = 'Fixed clusters - Rscript execution based on defined number of clusters <br>Gap estimated clusters - Rscript execution based on caluclated number of cluster by the Rscripts <br>Optimal clusters - Rscript execution based on Python calculation of clusters<br>';
$string['unknownclustermethod'] = 'Unknown Cluster quantity method';
$string['croncustommids'] = 'MIDs of activities to execute and prepare the task';
$string['croncustommidsdesc'] = 'If value is empty the task will not run if value is -1 it will run for all MIDS in the system';
$string['customactivitytext'] = '<b>Custom settings for Activity (MID) {$a} </b>';
$string['customactivitytextdesc'] = 'Please, folow this link to reset & rebuild the cache for current activity <a href="{$a}">Reset&Rebuild</a> <br> 1)Procces should take a lot of time.<br> 2) Before running it clear Core Config cache';
$string['croncustommids'] = 'cron custom mids';
$string['croncustommidsdesc'] = 'cron custom mids';
$string['modelenabled'] = 'Enable model';

$string['allquestions'] = 'All questions';
$string['requiredquestions'] = 'Required questions';
$string['importantquestions'] = 'Enable Important questions functionality';
$string['importantnum'] = 'Number of important questions';
$string['importantnumdesc'] = 'How many of top heatmapped questions will be rendered as important in grouper table';
$string['oercatalogtooltip'] = 'Adapted to Grouper - a tool for advanced diagnosis';

$string['midurl'] = 'Custom url for MID {$a}';
$string['midurldesc'] = 'This URL will be used in question table for this MID. Url has higher priority over file setting below.';
$string['midfile'] = 'Custom file for MID {$a}';
$string['midfiledesc'] = 'Link to this file will be used in question table for this MID. File has lower priority over url setting above.';
$string['custommidlink'] = 'Question Analysis File';

// Cache.
$string['cachedef_quizzes'] = 'Cache of quizzes';

$string['recommendedbyusers'] = 'Recommended users to be first in the list';
$string['recommendedbyusersdesc'] = 'Show Recommended users first in the top of the list, example 122,455,...';
$string['excludeopenquestions'] = 'Exlude Open questions in proccess';
$string['excludeopenquestionsdesc'] = 'This mode will exclude all open questions as essay, essayrubric, poodllrecording, opensheet and combined if it has essay question';
$string['usertableevent'] = 'User open analytics table';
$string['rebuildqueued'] = 'Cache rebuild was queued, but it takes time. Please close this window and wait for about 5 minutes.';

