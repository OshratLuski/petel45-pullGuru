<?php

/**
 *
 * The local_tutorials
 * Send please complete your tutorial (SCORM activity) reminder, by intervals.
 * Plugin administration pages are defined here.
 *
 * @category    admin
 * @package    local_tutorials
 * @copyright  2022 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
//require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {

    // New settings page.
    $page = new admin_settingpage('local_tutorials', get_string('pluginname', 'local_tutorials'));

    if ($ADMIN->fulltree) {
        $setting = new admin_setting_configtext('local_tutorials/cmids_monitoring',
            get_string('setting_completion_monitoring', 'local_tutorials'),
            get_string('setting_completion_monitoring_desc', 'local_tutorials'), 30);
        $page->add($setting);

        $setting = new admin_setting_configtext('local_tutorials/esb_ws_url',
            get_string('setting_esb_ws_url', 'local_tutorials'),
            get_string('setting_esb_ws_url_desc', 'local_tutorials'),
            'https://esb-test.weizmann.ac.il/esb/rest/GC_INSERT_PASSED_MDL_TEST_HR');
        $page->add($setting);

        $setting = new admin_setting_configtext('local_tutorials/esb_ws_username',
            get_string('setting_esb_ws_username', 'local_tutorials'),
            get_string('setting_esb_ws_username_desc', 'local_tutorials'), '');
        $page->add($setting);

        $setting = new admin_setting_configpasswordunmask('local_tutorials/esb_ws_password',
            get_string('setting_esb_ws_password', 'local_tutorials'),
            get_string('setting_esb_ws_password_desc', 'local_tutorials'), '');
        $page->add($setting);

        // Send email reminders to user that did not completed activities (SCORM Tutorials)
        // Message templates
        $page->add(new admin_setting_heading('local_tutorials/reminder_section',
            get_string('setting_reminder_section', 'local_tutorials'),''));

        $setting = new admin_setting_configtext('local_tutorials/watch_uncompleted_cmids',
            get_string('setting_reminder_cmids', 'local_tutorials'),
            get_string('setting_reminder_cmids_desc', 'local_tutorials'), '');
        $page->add($setting);

        $setting = new admin_setting_configtext('local_tutorials/reminder_firstmail_subject',
            get_string('setting_reminder_firstmail_subject', 'local_tutorials'),
            get_string('setting_reminder_firstmail_subject_desc', 'local_tutorials'), '');
        $page->add($setting);

        $setting = new admin_setting_confightmleditor('local_tutorials/reminder_firstmail_content',
            get_string('setting_reminder_firstmail_content', 'local_tutorials'),
            get_string('setting_reminder_firstmail_content_desc', 'local_tutorials'), '');
        $page->add($setting);

        // Comma delimited day intervals from the user enrolment date, to send reminders.
        $setting = new admin_setting_configtext('local_tutorials/intervals',
            get_string('setting_reminder_intervals', 'local_tutorials'),
            get_string('setting_reminder_intervals_desc', 'local_tutorials'), '');
        $page->add($setting);


        $page->add(
            new admin_setting_configcheckbox(
                'local_tutorials/send_safety_reminders',
                get_string('send_safety_reminders', 'local_tutorials'),
                '',
                0
            )
        );

        $items = explode(',', trim(get_config('local_tutorials', 'intervals')));
        foreach(array_filter($items) as $day){
            if(!is_number(trim($day))){
                continue;
            }

            $day = trim($day);

            $page->add(new admin_setting_heading(
                    'header'.trim($day),
                    get_string('setting_reminder_intervalblock', 'local_tutorials', $day),
                    get_string('setting_reminder_intervalblockinfo', 'local_tutorials', $day),
                )
            );

            $name = 'local_tutorials/reminder_intervalsmail_subject_'.$day;
            $setting = new admin_setting_configtext($name,
                    get_string('setting_reminder_intervalsmail_subject', 'local_tutorials'),
                    get_string('setting_reminder_intervalsmail_subject_desc', 'local_tutorials'), '');
            $page->add($setting);

            $name = 'local_tutorials/reminder_intervalsmail_content_'.$day;
            $setting = new admin_setting_confightmleditor($name,
                    get_string('setting_reminder_intervalsmail_content', 'local_tutorials'),
                    get_string('setting_reminder_intervalsmail_content_desc', 'local_tutorials'), '');
            $page->add($setting);
        }

        $page->add(new admin_setting_heading('local_tutorials/fire_prevention_reminder_section',
            get_string('fire_prevention_course_reminder_section', 'local_tutorials'),''));

        $page->add(
            new admin_setting_configcheckbox(
                'local_tutorials/send_fire_prevention_reminders',
                get_string('fire_prevention_course_reminder', 'local_tutorials'),
                '', 0));

        $setting = new admin_setting_configtext('local_tutorials/fire_prevention_he_cmid',
            get_string('setting_fire_prevention_he_cmid', 'local_tutorials'),
            get_string('setting_fire_prevention_he_cmid_desc', 'local_tutorials'), '');
        $page->add($setting);

        $setting = new admin_setting_configtext('local_tutorials/fire_prevention_en_cmid',
            get_string('setting_fire_prevention_en_cmid', 'local_tutorials'),
            get_string('setting_fire_prevention_en_cmid_desc', 'local_tutorials'), '');
        $page->add($setting);
    }

    $ADMIN->add('localplugins', $page);

}
