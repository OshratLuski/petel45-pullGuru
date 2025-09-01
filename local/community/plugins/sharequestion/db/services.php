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
 * @package    community_sharequestion
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
        'community_sharequestion_copy_to_quiz_html' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'copy_to_quiz_html',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get copy to quiz html',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharequestion_get_quizes_by_course' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_quizes_by_course',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get quizes by course',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharequestion_save_questions_to_cron' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'save_questions_to_cron',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Save questions to cron',
                'type' => 'write',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharequestion_copy_to_category_html' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'copy_to_category_html',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get copy to category html',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),

        'community_sharequestion_get_categories_by_course' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_categories_by_course',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get categories by course',
                'type' => 'read',
                'ajax' => true,
                'capabilities' => '',
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_get_oercatalog_hierarchy' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_oercatalog_hierarchy',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get oercatalog hierarchy',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_submit_upload_activity' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'submit_upload_activity',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Upload activity to catalog',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_copy_to_teacher_html' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'copy_to_teacher_html',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Copy to teacher html',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_autocomplete_teachers' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'autocomplete_teachers',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Autocomplete teachers',
                'type' => 'read',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_submit_teachers' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'submit_teachers',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Submit teachers',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_get_courses_by_user' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_courses_by_user',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get courses by user',
                'type' => 'read',
                'ajax' => true,
                'readonlysession' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_get_bank_categories_on_course' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_bank_categories_on_course',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get bank categories on course',
                'type' => 'read',
                'ajax' => true,
                'readonlysession' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_get_question_categories_by_course' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_question_categories_by_course',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get question categories by course',
                'type' => 'read',
                'ajax' => true,
                'readonlysession' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_get_questions_by_category' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'get_questions_by_category',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Get question by category',
                'type' => 'read',
                'ajax' => true,
                'readonlysession' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
        'community_sharequestion_save_questions_to_quiz' => array(
                'classname' => 'community_sharequestion_external',
                'methodname' => 'save_questions_to_quiz',
                'classpath' => 'local/community/plugins/sharequestion/externallib.php',
                'description' => 'Save questions to quiz',
                'type' => 'write',
                'ajax' => true,
                'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Share question' => array(
                'functions' => array(
                        'community_sharequestion_copy_to_quiz_html',
                        'community_sharequestion_get_quizes_by_course',
                        'community_sharequestion_save_questions_to_cron',
                        'community_sharequestion_copy_to_category_html',
                        'community_sharequestion_get_categories_by_course',
                        'community_sharequestion_get_oercatalog_hierarchy',
                        'community_sharequestion_submit_upload_activity',
                        'community_sharequestion_copy_to_teacher_html',
                        'community_sharequestion_autocomplete_teachers',
                        'community_sharequestion_submit_teachers',
                        'community_sharequestion_get_courses_by_user',
                        'community_sharequestion_get_bank_categories_on_course',
                        'community_sharequestion_get_question_categories_by_course',
                        'community_sharequestion_get_questions_by_category',
                        'community_sharequestion_save_questions_to_quiz',
                ),
                'enabled' => 1,
                'shortname' => 'sharequestion'
        )
);
