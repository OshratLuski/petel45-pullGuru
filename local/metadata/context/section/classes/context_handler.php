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

namespace metadatacontext_section;

defined('MOODLE_INTERNAL') || die;

// Section context has never existed. Define it here using the '9000' category.
define('CONTEXT_SECTION', 9200);

/**
 * Section metadata context handler class..
 *
 * @package metadatacontext_section
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @copyright  2017, onwards Poet
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context_handler extends \local_metadata\context\context_handler {

    /**
     * Constructor.
     * @param int $instanceid The instance of the context in section.
     * @param int $contextlevel The context level for this metadata.
     * @param int $contextname The name of this context (must be static - no language string).
     */
    public function __construct($instanceid = null, $contextlevel = null, $contextname = '') {
        return parent::__construct($instanceid, CONTEXT_SECTION, 'section');
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle data record for the instance.
     */
    public function get_instance() {
        global $DB;

        if (empty($this->instance)) {
            if (!empty($this->instanceid)) {
                $this->instance = $DB->get_record('course_sections', ['id' => $this->instanceid], '*', MUST_EXIST);
            } else {
                $this->instance = false;
            }
        }

        return $this->instance;
    }

    /**
     * Return the instance of the context. Must be handled by the implementing class.
     * @return object The Moodle context.
     */
    public function get_context() {
        if (empty($this->context)) {
            $this->context = \context_system::instance();
        }
        return $this->context;
    }

    /**
     * Return the instance id of the currently accessed context. Used by page displays (filter). Must be handled by the implementing
     * class.
     * @return int|boolean Instance id or false if not determined.
     */
    public function get_instanceid_from_currentcontext() {
        if (empty($this->instanceid)) {
            debugging('Must provide a section id.');
            $this->instanceid = false;
        }
        return $this->instanceid;
    }

    /**
     * Return the instance of the context. Defaults to the home page.
     * @return object The Moodle redirect URL.
     */
    public function get_redirect() {
        global $DB;

//        $section = $DB->get_record('course_sections', ['id' => $this->instanceid], '*', MUST_EXIST);
//        return new \moodle_url('/course/view.php', ['id' => $section->course, 'sectionid' => $this->instanceid]);

        return new \moodle_url('/course/editsection.php', ['id' => $this->instanceid]);
    }

    /**
     * Check any necessary access restrictions and error appropriately. Must be implemented.
     * e.g. "require_login()". "require_capability()".
     * @return boolean False if access should not be granted.
     */
    public function require_access() {
        require_login();
        require_capability('moodle/course:create', $this->context);
        return true;
    }

    /**
     * Implement if specific context settings can be added to a context settings page (e.g. Quiz / section bank).
     */
    public function add_settings_to_context_menu(\admin_root $navmenu): bool {
        // Add the settings page to the sections settings menu, if enabled.
        if (method_exists($navmenu, 'find') && $navmenu->find('modsettings', \settings_navigation::TYPE_SETTING)) {
            $navmenu->add('modsettings',
                new \admin_externalpage('metadatacontext_sections', get_string('metadatatitle', 'metadatacontext_section'),
                    new \moodle_url('/local/metadata/index.php', ['contextlevel' => CONTEXT_SECTION]), ['moodle/site:config']),
                'sections');
        }

        return true;
    }

    /**
     * Hook function that is called when settings blocks are being built.
     */
    public function extend_settings_navigation($settingsnav, $context) {
        global $PAGE, $COURSE;

        //if (strpos($PAGE->pagetype, 'course-view-flexsections') !== false) {
        //
        //    if ((get_config('metadatacontext_section', 'metadataenabled') == 1) &&
        //            (is_siteadmin() || can_edit_in_category($COURSE->category))) {
        //
        //        if ($settingnode = $settingsnav->find('modulesettings', \settings_navigation::TYPE_SETTING)) {
        //            $questionid = $PAGE->url->param('id');
        //            $this->instanceid = $questionid;
        //            $this->get_instance();
        //            $strmetadata = get_string('metadatatitle', 'metadatacontext_question');
        //            $url = new \moodle_url('/local/metadata/index.php',
        //                    ['id' => $questionid, 'action' => 'questiondata', 'contextlevel' => CONTEXT_SECTION, 'returnurl' =>$PAGE->url]);
        //            $metadatanode = \navigation_node::create(
        //                    $strmetadata,
        //                    $url,
        //                    \navigation_node::NODETYPE_LEAF,
        //                    'metadata',
        //                    'metadataquestion',
        //                    new \pix_icon('i/settings', $strmetadata)
        //            );
        //            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
        //                $metadatanode->make_active();
        //            }
        //            $settingnode->add_node($metadatanode);
        //        }
        //
        //    }
        //}
    }
}
