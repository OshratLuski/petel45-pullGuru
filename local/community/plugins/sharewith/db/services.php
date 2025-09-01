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
 * Core external functions and service definitions.
 *
 * The functions and services defined on this file are
 * processed and registered into the Moodle DB after any
 * install or upgrade operation. All plugins support this.
 *
 * For more information, take a look to the documentation available:
 *     - Webservices API: {@link http://docs.moodle.org/dev/Web_services_API}
 *     - External API: {@link http://docs.moodle.org/dev/External_functions_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    community_sharewith
 * @category   webservice
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
        'community_sharewith_add_sharewith_task' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'add_sharewith_task',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Add sharing activity task to cron',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_submit_upload_activity' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'submit_upload_activity',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Upload activity to catalog',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_add_saveactivity_task' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'add_saveactivity_task',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Add sharing activity task to cron',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_categories' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_categories',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get categories by user',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_courses' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_courses',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get courses by user',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_sections' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_sections',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get sections by course',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_sections_html' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_sections_html',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get sections by course in HTML',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_community' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_community',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get community',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_teachers' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_teachers',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get teachers',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_autocomplete_teachers' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'autocomplete_teachers',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get teachers',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_submit_teachers' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'submit_teachers',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Send to teachers',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_check_cm_status' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'check_cm_status',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Check mod status',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_amit_teacher' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_amit_teacher',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Obtain data for teacher colleagues',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_sectionid' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_sectionid',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get id of the current section',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharewith_get_oercatalog_hierarchy' => array(
                'classname' => 'community_sharewith_external',
                'methodname' => 'get_oercatalog_hierarchy',
                'classpath' => 'local/community/plugins/sharewith/externallib.php',
                'description' => 'Get oercatalog hierarchy',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);
