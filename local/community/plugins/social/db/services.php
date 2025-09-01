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
 * @package    community_social
 * @category   webservice
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
        'community_social_follow_teacher' => array(
                'classname' => 'community_social_external',
                'methodname' => 'follow_teacher',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'Follow teacher',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_render_block_user_data' => array(
                'classname' => 'community_social_external',
                'methodname' => 'render_block_user_data',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'render_block_user_data',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_user_collegues_list' => array(
                'classname' => 'community_social_external',
                'methodname' => 'user_collegues_list',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'user_collegues_list',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_user_follower_list' => array(
                'classname' => 'community_social_external',
                'methodname' => 'user_follower_list',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'user_follower_list',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_social_disable' => array(
                'classname' => 'community_social_external',
                'methodname' => 'social_disable',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'social_disable',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_send_followed_courses' => array(
                'classname' => 'community_social_external',
                'methodname' => 'send_followed_courses',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'send_followed_courses',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_render_profile_blocks' => array(
                'classname' => 'community_social_external',
                'methodname' => 'render_profile_blocks',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'render_profile_blocks',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_render_teacher_block' => array(
                'classname' => 'community_social_external',
                'methodname' => 'render_teacher_block',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'render_teacher_block',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_popup_public_course' => array(
                'classname' => 'community_social_external',
                'methodname' => 'popup_public_course',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'popup_public_course',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_save_selected_pombim_courses' => array(
                'classname' => 'community_social_external',
                'methodname' => 'save_selected_pombim_courses',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'save_selected_pombim_courses',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_render_block_aside_courses_pombim' => array(
                'classname' => 'community_social_external',
                'methodname' => 'render_block_aside_courses_pombim',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'render_block_aside_courses_pombim',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_school_settings' => array(
                'classname' => 'community_social_external',
                'methodname' => 'school_settings',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'school_settings',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_school_save' => array(
                'classname' => 'community_social_external',
                'methodname' => 'school_save',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'school_save',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_remove_teacher_from_course' => array(
                'classname' => 'community_social_external',
                'methodname' => 'remove_teacher_from_course',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'remove_teacher_from_course',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_request_followed_courses' => array(
                'classname' => 'community_social_external',
                'methodname' => 'request_followed_courses',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'request_followed_courses',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_change_follow_teacher_by_user' => array(
                'classname' => 'community_social_external',
                'methodname' => 'change_follow_teacher_by_user',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'change_follow_teacher_by_user',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_approve_message_from_teacher' => array(
                'classname' => 'community_social_external',
                'methodname' => 'approve_message_from_teacher',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'approve_message_from_teacher',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_decline_message_from_teacher' => array(
                'classname' => 'community_social_external',
                'methodname' => 'decline_message_from_teacher',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'decline_message_from_teacher',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_remove_teacher_request' => array(
                'classname' => 'community_social_external',
                'methodname' => 'remove_teacher_request',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'remove_teacher_request',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_popup_migrate_public_course' => array(
                'classname' => 'community_social_external',
                'methodname' => 'popup_migrate_public_course',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'popup_migrate_public_course',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_migrate_public_course' => array(
                'classname' => 'community_social_external',
                'methodname' => 'migrate_public_course',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'migrate_popup_public_course',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_lazy_load' => array(
                'classname' => 'community_social_external',
                'methodname' => 'lazy_load',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'lazy_load',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_social_share_corse_pombim' => array(
                'classname' => 'community_social_external',
                'methodname' => 'share_corse_pombim',
                'classpath' => 'local/community/plugins/social/externallib.php',
                'description' => 'share_corse_pombim',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);
