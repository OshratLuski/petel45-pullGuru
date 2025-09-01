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
 * @package    community_oer
 * @category   webservice
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
        'community_oer_get_activity_instance' => array(
                'classname' => 'community_oer_activity_external',
                'methodname' => 'get_activity_instance',
                'classpath' => 'local/community/plugins/oer/external/activitylib.php',
                'description' => 'Get activity instance',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_activity_blocks' => array(
                'classname' => 'community_oer_activity_external',
                'methodname' => 'get_activity_blocks',
                'classpath' => 'local/community/plugins/oer/external/activitylib.php',
                'description' => 'Get activity blocks',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_my_courses_and_sections' => array(
                'classname' => 'community_oer_activity_external',
                'methodname' => 'get_my_courses_and_sections',
                'classpath' => 'local/community/plugins/oer/external/activitylib.php',
                'description' => 'Get my corses and sections',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_copy_activity_to_section' => array(
                'classname' => 'community_oer_activity_external',
                'methodname' => 'copy_activity_to_section',
                'classpath' => 'local/community/plugins/oer/external/activitylib.php',
                'description' => 'Copy activity to section',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_activity_get_single_page' => array(
                'classname' => 'community_oer_activity_external',
                'methodname' => 'activity_get_single_page',
                'classpath' => 'local/community/plugins/oer/external/activitylib.php',
                'description' => 'Get activity single page',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_question_instance' => array(
                'classname' => 'community_oer_question_external',
                'methodname' => 'get_question_instance',
                'classpath' => 'local/community/plugins/oer/external/questionlib.php',
                'description' => 'Get question instance',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_question_blocks' => array(
                'classname' => 'community_oer_question_external',
                'methodname' => 'get_question_blocks',
                'classpath' => 'local/community/plugins/oer/external/questionlib.php',
                'description' => 'Get question blocks',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_selected_question_blocks' => array(
                'classname' => 'community_oer_question_external',
                'methodname' => 'get_selected_question_blocks',
                'classpath' => 'local/community/plugins/oer/external/questionlib.php',
                'description' => 'Get selected question blocks',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_change_hidden_questions' => array(
                'classname' => 'community_oer_question_external',
                'methodname' => 'change_hidden_questions',
                'classpath' => 'local/community/plugins/oer/external/questionlib.php',
                'description' => 'Change hidden questions',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_delete_questions' => array(
                'classname' => 'community_oer_question_external',
                'methodname' => 'delete_questions',
                'classpath' => 'local/community/plugins/oer/external/questionlib.php',
                'description' => 'Delete questions',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_show_review_popup' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'show_review_popup',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Update and view invitation for review in the course page',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_send_review_later' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'send_review_later',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Remind me later',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_reject_review' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'reject_review',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Reject review',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_show_review' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'show_review',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Show review',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_send_comment' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'send_comment',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Send new comment',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_edit_comment' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'edit_comment',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Edit comment',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_delete_comment' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'delete_comment',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Delete comment',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_delete_review' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'delete_review',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Delete review',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_open_popup_remind' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'open_popup_remind',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Open popup remind',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_send_remind' => array(
                'classname' => 'community_oer_review_external',
                'methodname' => 'send_remind',
                'classpath' => 'local/community/plugins/oer/external/reviewlib.php',
                'description' => 'Send remind',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_sequence_instance' => array(
                'classname' => 'community_oer_sequence_external',
                'methodname' => 'get_sequence_instance',
                'classpath' => 'local/community/plugins/oer/external/sequencelib.php',
                'description' => 'Get sequence instance',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_sequence_blocks' => array(
                'classname' => 'community_oer_sequence_external',
                'methodname' => 'get_sequence_blocks',
                'classpath' => 'local/community/plugins/oer/external/sequencelib.php',
                'description' => 'Get sequence blocks',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_copy_sequence_to_course' => array(
                'classname' => 'community_oer_sequence_external',
                'methodname' => 'copy_sequence_to_course',
                'classpath' => 'local/community/plugins/oer/external/sequencelib.php',
                'description' => 'Copy sequence to course',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_sequence_get_single_page' => array(
                'classname' => 'community_oer_sequence_external',
                'methodname' => 'sequence_get_single_page',
                'classpath' => 'local/community/plugins/oer/external/sequencelib.php',
                'description' => 'Get sequence single page',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_course_instance' => array(
                'classname' => 'community_oer_course_external',
                'methodname' => 'get_course_instance',
                'classpath' => 'local/community/plugins/oer/external/courselib.php',
                'description' => 'Get course instance',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_oer_get_course_blocks' => array(
                'classname' => 'community_oer_course_external',
                'methodname' => 'get_course_blocks',
                'classpath' => 'local/community/plugins/oer/external/courselib.php',
                'description' => 'Get courses blocks',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Oer plugin' => array(
                'functions' => array(
                        'community_oer_get_activity_instance',
                        'community_oer_get_activity_blocks',
                        'community_oer_get_my_courses_and_sections',
                        'community_oer_copy_activity_to_section',
                        'community_oer_activity_get_single_page',
                        'community_oer_get_question_instance',
                        'community_oer_get_question_blocks',
                        'community_oer_change_hidden_questions',
                        'community_oer_delete_questions',
                        'community_oer_show_review_popup',
                        'community_oer_send_review_later',
                        'community_oer_reject_review',
                        'community_oer_show_review',
                        'community_oer_send_comment',
                        'community_oer_edit_comment',
                        'community_oer_delete_comment',
                        'community_oer_delete_review',
                        'community_oer_open_popup_remind',
                        'community_oer_send_remind',
                        'community_oer_get_sequence_instance',
                        'community_oer_get_sequence_blocks',
                        'community_oer_copy_sequence_to_course',
                        'community_oer_sequence_get_single_page',
                        'community_oer_get_course_instance',
                        'community_oer_get_course_blocks',
                ),
                'enabled' => 1,
                'shortname' => 'oer'
        )
);
