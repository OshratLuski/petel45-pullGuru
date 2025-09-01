<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_codescore
 * @copyright   2024 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function codescore_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_codescore into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_codescore_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function codescore_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('codescore', $moduleinstance);
    $moduleinstance->id = $id;
    $moduleinstance->grade = 0;

    codescore_grade_item_update($moduleinstance, false, true);

    return $id;
}

/**
 * Updates an instance of the mod_codescore in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_codescore_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function codescore_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('codescore', $moduleinstance);
}

/**
 * Removes an instance of the mod_codescore from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function codescore_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('codescore', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('codescore', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of mod_codescore?
 *
 * This function returns if a scale is being used by one mod_codescore
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_codescore instance.
 */
function codescore_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('codescore', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_codescore.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_codescore instance.
 */
function codescore_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('codescore', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_codescore instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function codescore_grade_item_update($moduleinstance, $reset=false, $new = false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade >= 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = 100;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }
    $grades;
    if ($new) {
        $grades = null;
    } else {
        $grades = array(
            'userid' => (int)$moduleinstance->userid,
            'rawgrade' => (int)$moduleinstance->grade,
        );
    }
    grade_update('/mod/codescore', $moduleinstance->course, 'mod', 'codescore', $moduleinstance->id, 0, $grades, $item);
}

/**
 * Delete grade item for given mod_codescore instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return int.
 */
function codescore_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/codescore', $moduleinstance->course, 'mod', 'codescore',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_codescore grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function codescore_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/codescore', $moduleinstance->course, 'mod', 'mod_codescore', $moduleinstance->id, 0, $grades);
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_codescore
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function codescore_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_codescore file areas.
 *
 * @package     mod_codescore
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function codescore_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_codescore file areas.
 *
 * @package     mod_codescore
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_codescore's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function codescore_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Extends the global navigation tree by adding mod_codescore nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $codescorenode An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function codescore_extend_navigation($codescorenode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_codescore settings.
 *
 * This function is called when the context for the page is a mod_codescore module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $codescorenode {@see navigation_node}
 */
function codescore_extend_settings_navigation($settingsnav, $codescorenode = null) {
}

/**
 * Calculates help text for rubrics in localization files.
 *
 * @param int $i The rubric number.
 * @param string $element The rubric element.
 * @return string.
 */

function get_rubric_help($i, $element) {
    if(isset(get_config('mod_codescore')->{"rubric" . $i . $element})) {
        return get_config('mod_codescore')->{"rubric" . $i . $element};
    }
    return '';
}

/**
 * Return the preconfigured tools which are configured for inclusion in the activity picker.
 *
 * @param \core_course\local\entity\content_item $defaultmodulecontentitem reference to the content item for the codescore module.
 * @param \stdClass $user the user object, to use for cap checks if desired.
 * @param stdClass $course the course to scope items to.
 * @return array the array of content items.
 */
function codescore_get_course_content_items(\core_course\local\entity\content_item $defaultmodulecontentitem, \stdClass $user,
    \stdClass $course) {
    global $CFG, $OUTPUT;
    require_once ($CFG->dirroot . '/mod/codescore/locallib.php');

    $types = [];

    // The 'External tool' entry (the main module content item), should always take the id of 1.
    if (has_capability('mod/codescore:addmanualinstance', context_course::instance($course->id), $user)) {
        $types = [new \core_course\local\entity\content_item(
            1,
            $defaultmodulecontentitem->get_name(),
            $defaultmodulecontentitem->get_title(),
            $defaultmodulecontentitem->get_link(),
            $defaultmodulecontentitem->get_icon(),
            $defaultmodulecontentitem->get_help(),
            $defaultmodulecontentitem->get_archetype(),
            $defaultmodulecontentitem->get_component_name(),
            $defaultmodulecontentitem->get_purpose()
        )];
    }

    return $types;
}
