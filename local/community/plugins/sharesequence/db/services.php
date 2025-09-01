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
 * @package    community_sharesequence
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(

        'community_sharesequence_get_oercatalog_hierarchy' => array(
                'classname' => 'community_sharesequence_external',
                'methodname' => 'get_oercatalog_hierarchy',
                'classpath' => 'local/community/plugins/sharesequence/externallib.php',
                'description' => 'Get oercatalog hierarchy',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharesequence_submit_sequence_page_1' => array(
                'classname' => 'community_sharesequence_external',
                'methodname' => 'submit_sequence_page_1',
                'classpath' => 'local/community/plugins/sharesequence/externallib.php',
                'description' => 'Submit sequence page 1',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharesequence_get_data_for_section' => array(
                'classname' => 'community_sharesequence_external',
                'methodname' => 'get_data_for_section',
                'classpath' => 'local/community/plugins/sharesequence/externallib.php',
                'description' => 'Get data for section',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharesequence_submit_sequence_page_2' => array(
                'classname' => 'community_sharesequence_external',
                'methodname' => 'submit_sequence_page_2',
                'classpath' => 'local/community/plugins/sharesequence/externallib.php',
                'description' => 'Submit sequence page 2',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharesequence_check_availability' => array(
                'classname' => 'community_sharesequence_external',
                'methodname' => 'check_availability',
                'classpath' => 'local/community/plugins/sharesequence/externallib.php',
                'description' => 'Check availability',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Share sequence' => array(
                'functions' => array(
                        'community_sharesequence_get_oercatalog_hierarchy',
                        'community_sharesequence_submit_sequence_page_1',
                        'community_sharesequence_get_data_for_section',
                        'community_sharesequence_submit_sequence_page_2',
                        'community_sharesequence_check_availability',
                ),
                'enabled' => 1,
                'shortname' => 'sharesequence'
        )
);
