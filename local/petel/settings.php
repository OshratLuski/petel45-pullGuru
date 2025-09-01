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
 * Plugin administration pages are defined here.
 *
 * @package     local_petel
 * @category    admin
 * @copyright   2017 nadavkav@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/petel/locallib.php');

if ($hassiteconfig) {

    // New settings page.
    $page = new admin_settingpage('local_petel', get_string('pluginname', 'local_petel', null, false));

    if ($ADMIN->fulltree) {

        // Add select course.
        $options = [];
        $sql = "SELECT c.id, c.fullname FROM {course} c WHERE c.id > 1 AND c.visible = 1";
        foreach ($DB->get_records_sql($sql) as $item) {
            $options[$item->id] = $item->fullname;
        }

        reset($options);
        $default = key($options);

        $setting = new admin_setting_configselect('local_petel/default_course',
                get_string('settings_defaultcourse', 'local_petel'), '', $default, $options);
        $page->add($setting);

        // Add smartselect js and css call.
        $PAGE->requires->js_amd_inline('
            require(["jquery", "core_form/select2"], function($) {$(".custom-select").select2({dropdownAutoWidth: true})});
        ');
        //$PAGE->requires->css('/lib/form/css/select2.min.css');

        // Add admin email.
        $admin = get_admin();
        $setting = new admin_setting_configtext('local_petel/admin_email',
                get_string('settings_adminemail', 'local_petel'), '', $admin->email, PARAM_EMAIL);
        $page->add($setting);

        $setting = new admin_setting_configtext('local_petel/sms_securtity_number',
                get_string('setting_smssecurtitynumber', 'local_petel'),
                get_string('setting_smssecurtitynumber_desc', 'local_petel'), 5, PARAM_INT);
        $page->add($setting);

        $setting = new admin_setting_configtext('local_petel/sms_securtity_time_reset',
                get_string('setting_smssecurtitytimereset', 'local_petel'),
                get_string('setting_smssecurtitytimereset_desc', 'local_petel'), 30, PARAM_INT);
        $page->add($setting);

        $page->add(
                new admin_setting_configcheckbox(
                        'local_petel/enabledemo',
                        get_string('enabledemo', 'local_petel'),
                        '',
                        0
                )
        );

        $setting = new admin_setting_configcheckbox('recaptchav3demoenable', new lang_string('recaptchav3demoenable', 'local_petel'), '', 0);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configcheckbox('recaptchav2enable', new lang_string('recaptchav2enable', 'local_petel'), new lang_string('configrecaptchav2enable', 'local_petel'), 0);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav2url', new lang_string('recaptchav2url', 'local_petel'), new lang_string('configrecaptchav2url', 'local_petel'), 'https://www.google.com/recaptcha/api/siteverify', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav2sitekey', new lang_string('recaptchav2sitekey', 'local_petel'), new lang_string('configrecaptchav2sitekey', 'local_petel'), '', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav2privatekey', new lang_string('recaptchav2privatekey', 'local_petel'), new lang_string('configrecaptchav2privatekey', 'local_petel'), '', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configcheckbox('recaptchav3enable', new lang_string('recaptchav3enable', 'local_petel'), new lang_string('configrecaptchav3enable', 'local_petel'), 0);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav3url', new lang_string('recaptchav3url', 'local_petel'), new lang_string('configrecaptchav3url', 'local_petel'), 'https://www.google.com/recaptcha/api', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav3sitekey', new lang_string('recaptchav3sitekey', 'local_petel'), new lang_string('configrecaptchav3sitekey', 'local_petel'), '', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('recaptchav3privatekey', new lang_string('recaptchav3privatekey', 'local_petel'), new lang_string('configrecaptchav3privatekey', 'local_petel'), '', PARAM_NOTAGS);
        $setting->set_force_ltr(true);
        $page->add($setting);
        $setting = new admin_setting_configtext('failedlogins_without_recaptcha', new lang_string('failedlogins_without_recaptcha', 'local_petel'), new lang_string('configfailedlogins_without_recaptcha', 'local_petel'), 2, PARAM_INT);
        $setting->set_force_ltr(true);
        $page->add($setting);


        $coursecontext = context_course::instance(SITEID);
        $courserolearray = get_all_roles($coursecontext);
        $courserolearray = role_fix_names($courserolearray, $coursecontext, ROLENAME_ALIAS, true);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        $page->add(
                new admin_setting_configselect(
                        'local_petel/demorole',
                        get_string('demorole', 'local_petel'),
                        get_string('demorole_desc', 'local_petel'),
                        $studentrole->id ?? 0,
                        $courserolearray
                )
        );

    }
    // Add settings page to the appearance settings category.
    $ADMIN->add('localplugins', $page);

    $ADMIN->add('root', new admin_category('petel', new lang_string('pluginname', 'local_petel')));

    $ADMIN->add('petel', new admin_externalpage('petel_localpetel',
            new lang_string('pluginname', 'local_petel'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=local_petel'));
    $ADMIN->add('petel', new admin_externalpage('petel_theme',
            new lang_string('configtitle', 'theme_petel'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=themesettingpetel'));
    $ADMIN->add('petel', new admin_externalpage('petel_question_recommendations',
            new lang_string('questionchooserrecommendations', 'local_petel'),
            $CFG->wwwroot . '/local/question_chooser/recommendations.php'));
    $ADMIN->add('petel', new admin_externalpage('petel_copy_metadata_activity',
            new lang_string('copymetadataactivity', 'local_petel'),
            $CFG->wwwroot . '/local/petel/copy_metadata_activity.php'));

    $ADMIN->add('root', new admin_category('oercatalog', new lang_string('pluginname', 'community_oer')));

    $ADMIN->add('oercatalog', new admin_externalpage('petel_community_sharewith',
            new lang_string('pluginname', 'community_sharewith'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=community_sharewith'));
    $ADMIN->add('oercatalog', new admin_externalpage('petel_community_sharequestion',
            new lang_string('pluginname', 'community_sharequestion'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=community_sharequestion'));
    $ADMIN->add('oercatalog', new admin_externalpage('petel_community',
            new lang_string('pluginname', 'local_community'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=local_community'));
    $ADMIN->add('oercatalog', new admin_externalpage('petel_community_oer',
            new lang_string('pluginname', 'community_oer'),
            $CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=community_oer'));
    $ADMIN->add('oercatalog', new admin_externalpage('petel_cache_oercatalog',
            new lang_string('cacheoercatalog', 'local_petel'),
            $CFG->wwwroot . '/local/community/plugins/oer/page_recache.php'));

    // Participiant filter default value.
    $options = [
            PP_ACTIVE_STUDENTS => get_string('ppactivestudents', 'local_petel'),
            PP_ALL_STUDENTS => get_string('ppallstudents', 'local_petel'),
            PP_ACTIVE_STUDENTS_AND_TEACHERS => get_string('ppactivestudentsandteachers', 'local_petel'),
            PP_SUSPENDED_USERS => get_string('ppsuspendedusers', 'local_petel'),
            PP_FELLOW_TEACHERS => get_string('ppfellowteachers', 'local_petel'),
            PP_TEACHERS_PAYOFF => get_string('ppteacherspayoff', 'local_petel'),
            PP_TEACHER_DOES_NOT_EDIT => get_string('ppteacherdoesnotedit', 'local_petel'),
            PP_NO_PERSONAL_CATEGORY => get_string('ppnopersonalcategory', 'local_petel'),
            PP_ALL => get_string('ppall', 'local_petel'),
    ];

    $default = PP_ALL;

    $setting = new admin_setting_configselect('local_petel/participiant_filter',
            get_string('settings_participiant_filter', 'local_petel'), '', $default, $options);
    $page->add($setting);

    // Count view popup course change.
    $options = [
            0 => '0',
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
    ];

    $default = 0;

    $setting = new admin_setting_configselect('local_petel/count_view_popup_course_change_metadata',
            get_string('settings_countviewpopupcoursechangemetadata', 'local_petel'), '', $default, $options);
    $page->add($setting);
}
