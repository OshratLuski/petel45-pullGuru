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
 * Web service external functions and service definitions.
 *
 * @package    community_sharecourse
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
        'community_sharecourse_submit_upload_course' => array(
                'classname' => 'community_sharecourse_external',
                'methodname' => 'submit_upload_course',
                'classpath' => 'local/community/plugins/sharecourse/externallib.php',
                'description' => 'Upload course to catalog',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharecourse_unshare_course' => array(
                'classname' => 'community_sharecourse_external',
                'methodname' => 'unshare_course',
                'classpath' => 'local/community/plugins/sharecourse/externallib.php',
                'description' => 'Unshare course from catalog',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharecourse_submit_teachers' => array(
                'classname' => 'community_sharecourse_external',
                'methodname' => 'submit_teachers',
                'classpath' => 'local/community/plugins/sharecourse/externallib.php',
                'description' => 'Submit teachers',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharecourse_popup_copy_course' => array(
                'classname' => 'community_sharecourse_external',
                'methodname' => 'popup_copy_course',
                'classpath' => 'local/community/plugins/sharecourse/externallib.php',
                'description' => 'Get popup copy course',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharecourse_add_sharecourse_task' => array(
                'classname' => 'community_sharecourse_external',
                'methodname' => 'add_sharecourse_task',
                'classpath' => 'local/community/plugins/sharecourse/externallib.php',
                'description' => 'Add sharing course task to cron',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Share courses' => array(
                'functions' => array(
                        'community_sharecourse_submit_upload_course',
                        'community_sharecourse_unshare_course',
                        'community_sharecourse_submit_teachers',
                        'community_sharecourse_popup_copy_course',
                        'community_sharecourse_add_sharecourse_task',
                ),
                'enabled' => 1,
                'shortname' => 'sharecourse'
        )
);
