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
 * Specialised restore for format_flexsections
 *
 * @package   format_flexsections
 * @category  backup
 * @copyright 2017 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/flexsections/classes/restore_course_flexsections_parser_processor.class.php');

/**
 * Specialised restore for format_flexsections
 *
 * Processes 'numsections' from the old backup files and hides sections that used to be "orphaned"
 *
 * @package   format_flexsections
 * @category  backup
 * @copyright 2017 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_format_flexsections_plugin extends restore_format_plugin {

    /** @var int */
    protected $originalnumsections = 0;

    /**
     * Checks if backup file was made on Moodle before 3.3 and we should respect the 'numsections'
     * and potential "orphaned" sections in the end of the course.
     *
     * @return bool
     */
    protected function need_restore_numsections() {
        $backupinfo = $this->step->get_task()->get_info();
        $backuprelease = $backupinfo->backup_release;
        return version_compare($backuprelease, '3.3', 'lt');
    }

    /**
     * Creates a dummy path element in order to be able to execute code after restore
     *
     * @return restore_path_element[]
     */
    public function define_course_plugin_structure() {
        global $DB;

        // Since this method is executed before the restore we can do some pre-checks here.
        // In case of merging backup into existing course find the current number of sections.
        $target = $this->step->get_task()->get_target();
        if (($target == backup::TARGET_CURRENT_ADDING || $target == backup::TARGET_EXISTING_ADDING) &&
                $this->need_restore_numsections()) {
            $maxsection = $DB->get_field_sql(
                    'SELECT max(section) FROM {course_sections} WHERE course = ?',
                    [$this->step->get_task()->get_courseid()]);
            $this->originalnumsections = (int) $maxsection;
        }

        // Dummy path element is needed in order for after_restore_course() to be called.

        $paths = array();

        // Add own format stuff.
        $elename = 'flexsections'; // This defines the postfix of 'process_*' below.
        /*
         * This is defines the nested tag within 'plugin_format_grid_course' to allow '/course/plugin_format_grid_course' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/flexsections');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the 'plugin_format_grid_course' element within the 'course' element in the 'course.xml' file in the '/course'
     * folder of the zipped backup 'mbz' file.
     */
    public function process_flexsections($data) {
        global $DB;

        $data = (object) $data;

        /* We only process this information if the course we are restoring to
          has 'flexsections' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'flexsections') {
            return;
        }

        $data->courseid = $this->task->get_courseid();

        if (!($course = $DB->get_record('course', array('id' => $data->courseid)))) {
            throw new \moodle_exception('invalidcourseid', 'error');
        }
    }

    /**
     * Executed after course restore is complete
     *
     * This method is only executed if course configuration was overridden
     */
    public function after_restore_course() {
        global $DB;

        $data = $this->connectionpoint->get_data();
        $backupinfo = $this->step->get_task()->get_info();
        if ($backupinfo->original_course_format !== 'flexsections') {
            // Backup from another course format.
            return;
        }

        $coursebackup = $this->connectionpoint->get_processing_object()->get_task();

        // Load the entire course.xml file to in-memory array.
        $xmlparser = new progressive_parser();
        $xmlparser->set_file($coursebackup->get_taskbasepath() . '/course.xml');


        $xmlprocessor = new restore_course_flexsections_parser_processor();
        $xmlparser->set_processor($xmlprocessor);
        $xmlparser->process();
        $infoarr = $xmlprocessor->get_all_chunks();

        $this->add_flexsections_image($infoarr);

        // Get new courseid.
        $courseid = $this->task->get_courseid();

        // Get visibleitems from course_format_options.
        $visibleitemsraw = $DB->get_record('course_format_options', array(
            'courseid' => $courseid,
            'format' => 'flexsections',
            'name' => 'flexsectionsvisibleitems'
            ));
        if ($visibleitemsraw) {
            $visibleitems = json_decode($visibleitemsraw->value);
            $newvisibleitems = array();
            foreach ($visibleitems as $id => $item) {
                if ($item[0][0] == 's') {
                    $newsectionid = $this->get_mappingid('course_section', substr($item[0], 1));
                    $newvisibleitems[$id][0] = (string) 's' . $newsectionid;
                    $newvisibleitems[$id][1] = (string) $item[1];
                } else {
                    $newcmid = $this->get_mappingid('course_module', $item[0]);
                    $newvisibleitems[$id][0] = (string) $newcmid;
                    $newvisibleitems[$id][1] = (string) $item[1];
                }
            }
            $newvisibleitemsrecord = new stdClass();
            $newvisibleitemsrecord->id = $visibleitemsraw->id;
            $newvisibleitemsrecord->value = json_encode($newvisibleitems);
            $DB->update_record('course_format_options', $newvisibleitemsrecord);
        }

        // Get flexsectionscoords from course_format_options.
        $plcoordsraw = $DB->get_record('course_format_options', array(
            'courseid' => $courseid,
            'format' => 'flexsections',
            'name' => 'flexsectionscoords'
            ));
        if ($plcoordsraw) {
            $plcoords = json_decode($plcoordsraw->value);
            $newplcoords = array();
            foreach ($plcoords as $id => $item) {
                if ($item->id[0] == 's') {
                    $newsectionid = $this->get_mappingid('course_section', substr($item->id, 1));
                    $newplcoords[$id] = new stdClass();
                    $newplcoords[$id]->id = (string) 's' . $newsectionid;
                    $newplcoords[$id]->coordx = (string) $item->coordx;
                    $newplcoords[$id]->coordy = (string) $item->coordy;
                } else {
                    $newcmid = $this->get_mappingid('course_module', $item->id);
                    $newplcoords[$id] = new stdClass();
                    $newplcoords[$id]->id = (string) $newcmid;
                    $newplcoords[$id]->coordx = (string) $item->coordx;
                    $newplcoords[$id]->coordy = (string) $item->coordy;
                }
            }
            $newplcoordsrecord = new stdClass();
            $newplcoordsrecord->id = $plcoordsraw->id;
            $newplcoordsrecord->value = json_encode($newplcoords);
            $DB->update_record('course_format_options', $newplcoordsrecord);
        }

        // Get flexsectionspinnedsections from course_format_options.
        $psecsraw = $DB->get_record('course_format_options', array(
            'courseid' => $courseid,
            'format' => 'flexsections',
            'name' => 'flexsectionspinnedsections'
            ));
        if ($psecsraw) {
            $psecs = json_decode($psecsraw->value);
            $newpsecs = array();
            foreach ($psecs as $id => $item) {
                if ($item[0][0] == 's') {
                    $newsectionid = $this->get_mappingid('course_section', substr($item[0], 1));
                    $newpsecs[$id][0] = (string) 's' . $newsectionid;
                    $newpsecs[$id][1] = (string) $item[1];
                }
            }
            $newpsecsrecord = new stdClass();
            $newpsecsrecord->id = $psecsraw->id;
            $newpsecsrecord->value = json_encode($newpsecs);
            $DB->update_record('course_format_options', $newpsecsrecord);
        }

        if (!$this->need_restore_numsections()) {
            // Backup file was made in Moodle 3.3 or later, we don't need to process 'numsecitons'.
            return;
        }

        if (!isset($data['tags']['numsections'])) {
            // Backup from another course format or backup file does not even have 'numsections'.
            return;
        }

        $numsections = (int) $data['tags']['numsections'];
        foreach ($backupinfo->sections as $key => $section) {
            // For each section from the backup file check if it was restored and if was "orphaned" in the original
            // course and mark it as hidden. This will leave all activities in it visible and available just as it was
            // in the original course.
            // Exception is when we restore with merging and the course already had a section with this section number,
            // in this case we don't modify the visibility.
            if ($this->step->get_task()->get_setting_value($key . '_included')) {
                $sectionnum = (int) $section->title;
                if ($sectionnum > $numsections && $sectionnum > $this->originalnumsections) {
                    $DB->execute("UPDATE {course_sections} SET visible = 0 WHERE course = ? AND section = ?",
                            [$this->step->get_task()->get_courseid(), $sectionnum]);
                }
            }
        }
    }

    /**
     * Add backuped flexsections image to a restored course
     */
    private function add_flexsections_image($infoarr) {
        global $USER, $DB;
        try {
            $courseid = $this->task->get_courseid();
            $context = context_course::instance($courseid);
            $contextid = $context->id;
            $fs = get_file_storage();

            $sectionsset = $DB->get_records_menu('course_sections', ['course' => $courseid], '', 'section, id');

            if (isset($infoarr[0]['tags']['flexsectionsimages']['flexsectionsimage']) &&
                    !empty($infoarr[0]['tags']['flexsectionsimages']['flexsectionsimage'])) {

                foreach ($infoarr[0]['tags']['flexsectionsimages']['flexsectionsimage'] as $key => $image) {

                    if (isset($image['flexsectionsimagepath']) and
                            isset($image['flexsectionsimagesection']) and
                            isset($image['flexsectionsimagename']) and
                            isset($image['flexsectionsimageauthor']) and
                            isset($image['flexsectionsimagelicense']) and
                            isset($image['flexsectionsimagehash'])) {

                        $DB->delete_records('files', array(
                                'itemid' => $sectionsset[$image['flexsectionsimagesection']],
                                'component' => "format_flexsections",
                                'filearea' => "image",
                                'contextid' => $contextid,
                        ));

                        $filerecord = array(
                                'contextid' => $contextid,
                                'component' => 'format_flexsections',
                                'filearea' => 'image',
                                'itemid' => $sectionsset[$image['flexsectionsimagesection']],
                                'filepath' => $image['flexsectionsimagepath'],
                                'filename' => $image['flexsectionsimagename'],
                                'timecreated' => time(),
                                'timemodified' => time(),
                                'userid' => $USER->id,
                                'source' => $image['flexsectionsimagename'],
                                'author' => $image['flexsectionsimageauthor'],
                                'license' => $image['flexsectionsimagelicense'],
                                'sortorder' => 0,
                        );

                        $content = base64_decode($image['flexsectionsimagehash']);

                        $saved = $fs->create_file_from_string($filerecord, $content);

                    }

                }
            }
        } catch (Exception $exc) {
            // Exception.
            echo $exc;
        }
    }

}
