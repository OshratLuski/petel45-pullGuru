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
 * @package local_diagnostic
 * @copyright 2021 Devlion.co
 * @author Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/cohort/lib.php');

    // Create a settings page and add an enable setting for each metadata context type.
    $settings = new admin_settingpage('local_diagnostic', get_string('pluginname', 'local_diagnostic'));
    $ADMIN->add('localplugins', $settings);

    $cmid = optional_param('cmid', '', PARAM_INT);
    if ($cmid) {
        require_once $CFG->dirroot . '/local/diagnostic/classes/external.php';
        if ($cache = \local_diagnostic\cache::get_record(['mid' => $cmid])) {
            $cache->delete();
        }
        ob_start();
        @local_diagnotic_rebuild([$cmid],true);
        $result = ob_get_clean();
        $settings->add(new admin_setting_heading('local_diagnostic/log',
                '',
                "The outpit results" . $result));
    }

    $cohorts = $DB->get_records('cohort');
    if ($cohorts) {
        $cohortnames = [];
        foreach ($cohorts as $cohort) {
            $cohortnames[$cohort->id] = $cohort->name;
        }

        $settings->add(
            new admin_setting_configmultiselect(
                'local_diagnostic/cohorts',
                get_string('cohorts', 'local_diagnostic'),
                '',
                array(1),
                $cohortnames
            )
        );
    }

    $settings->add(
            new admin_setting_configtext(
                    'local_diagnostic/croncustommids',
                    get_string('croncustommids', 'local_diagnostic'),
                    get_string('croncustommidsdesc', 'local_diagnostic'),
                    '', PARAM_TEXT
            )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_diagnostic/demomode',
            get_string('demomode', 'local_diagnostic'),
            '',
            0
        )
    );

    $clusternumkeys = [
        \local_diagnostic_external::CLUSTERNUM_FIXED,
        \local_diagnostic_external::CLUSTERNUM_GAPESTIMATE,
        \local_diagnostic_external::CLUSTERNUM_OPTIMAL
    ];
    $clusternumoptions = [];
    foreach ($clusternumkeys as $clusternumkey) {
        $clusternumoptions[$clusternumkey] = get_string($clusternumkey, 'local_diagnostic');
    }

    $settings->add(
        new admin_setting_configselect(
            'local_diagnostic/clusternummethod',
            get_string('clusternummethod', 'local_diagnostic'),
            get_string('clusternummethoddesc', 'local_diagnostic'),
            0,
            $clusternumoptions
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/nmin',
            get_string('nmin', 'local_diagnostic'),
            get_string('nmindesc', 'local_diagnostic'),
            1,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/nmax',
            get_string('nmax', 'local_diagnostic'),
            get_string('nmaxdesc', 'local_diagnostic'),
            4,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_diagnostic/importantquestions',
            get_string('importantquestions', 'local_diagnostic'),
            '',
            1
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/importantnum',
            get_string('importantnum', 'local_diagnostic'),
            get_string('importantnumdesc', 'local_diagnostic'),
            3,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_diagnostic/severalattempts',
            get_string('severalattempts', 'local_diagnostic'),
            '',
            0
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/rebuildlimit',
            get_string('rebuildlimit', 'local_diagnostic'),
            get_string('rebuildlimitdesc', 'local_diagnostic'),
            350,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/yellow',
            get_string('yellow', 'local_diagnostic'),
            get_string('yellowdesc', 'local_diagnostic'),
            10,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/green',
            get_string('green', 'local_diagnostic'),
            get_string('greendesc', 'local_diagnostic'),
            70,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/clusternum',
            get_string('clusternum', 'local_diagnostic'),
            get_string('clusternum_desc', 'local_diagnostic'),
            5,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_diagnostic/viewemptyclusters',
            get_string('viewemptyclusters', 'local_diagnostic'),
            '',
            1
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_diagnostic/enabletags',
            get_string('enabletags', 'local_diagnostic'),
            '',
            0
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_diagnostic/allowedtags',
            get_string('allowedtags', 'local_diagnostic'),
            get_string('allowedtags_desc', 'local_diagnostic'),
            ''
        )
    );

	$setting = new admin_setting_configtext('local_diagnostic/custommids',
        get_string('custommids', 'local_diagnostic'),
        get_string('custommidssesc', 'local_diagnostic'),
    ''
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext('local_diagnostic/recommendedbyusers',
        get_string('recommendedbyusers', 'local_diagnostic'),
        get_string('recommendedbyusersdesc', 'local_diagnostic'),
        ''
    );

    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

	$custommids = get_config('local_diagnostic', 'custommids');
	foreach (explode(',', $custommids) as $custommid) {
        $custommid = intval(trim($custommid));
        if ($custommid) {

            $reseturl = new moodle_url('/admin/settings.php' , ['section'=>'local_diagnostic','cmid'=>$custommid]);
            $settings->add(new admin_setting_heading('local_diagnostic/activitytitle_' . $custommid,
                    get_string('customactivitytext', 'local_diagnostic', $custommid),
                    get_string('customactivitytextdesc', 'local_diagnostic', $reseturl->out())));


            // Set medium and high level to each activity
            $setting = new admin_setting_configtext(
                    'local_diagnostic/activityclusternum_' . $custommid,
                    get_string('activityclusternum', 'local_diagnostic', $custommid),
                    '',
                    5,
                    PARAM_INT

            );
            $setting->set_updatedcallback('theme_reset_all_caches');
            $settings->add($setting);

            $activityclusternum = get_config('local_diagnostic', 'activityclusternum_' . $custommid);
            if (!empty($activityclusternum)) {
                for ($j = 1; $j <= $activityclusternum; ++$j) {
                    // Text area for each cluster

                    $setting = new admin_setting_configtextarea('local_diagnostic/cluster' . $j . 'descriptionactivity' . $custommid,
                        get_string('cluster_text_area', 'local_diagnostic', $j),
                        get_string('cluster_text_area_desc', 'local_diagnostic', $j),
                        ''
                    );

                    $setting->set_updatedcallback('theme_reset_all_caches');
                    $settings->add($setting);
                }
            }

            // Set medium and high level to each activity
            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'yellow',
                    get_string('activity_yellow', 'local_diagnostic', $custommid),
                    get_string('yellowdesc', 'local_diagnostic'),
                    get_config('local_diagnostic', 'yellow'),
                    PARAM_INT
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'green',
                    get_string('activity_green', 'local_diagnostic', $custommid),
                    get_string('greendesc', 'local_diagnostic'),
                    get_config('local_diagnostic', 'green'),
                    PARAM_INT
                )
            );

            // Set excluded cmid's
            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'exludedcmids',
                    get_string('activity_excluded_cmids', 'local_diagnostic', $custommid),
                    get_string('activity_excluded_cmids_desc', 'local_diagnostic', $custommid),
                    ''
                )
            );

            // Set excluded questions
            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'exludedquestionids',
                    get_string('activity_excluded_questionids', 'local_diagnostic', $custommid),
                    get_string('activity_excluded_questionids_desc', 'local_diagnostic', $custommid),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'cutoff',
                    get_string('activity_cutoff', 'local_diagnostic', $custommid),
                    get_string('activity_cutoff_desc', 'local_diagnostic', $custommid),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'startdate',
                    get_string('activity_startdate', 'local_diagnostic', $custommid),
                    get_string('activity_startdate_desc', 'local_diagnostic', $custommid),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/activity' . $custommid . 'enddate',
                    get_string('activity_enddate', 'local_diagnostic', $custommid),
                    get_string('activity_enddate_desc', 'local_diagnostic', $custommid),
                    ''
                )
            );

            $settings->add(
                new admin_setting_configcheckbox(
                    'local_diagnostic/repoquestionsonly' . $custommid,
                    get_string('repoquestionsonly', 'local_diagnostic', $custommid),
                    get_string('repoquestionsonlydesc', 'local_diagnostic'),
                    1
                )
            );

            $settings->add(
                new admin_setting_configcheckbox(
                    'local_diagnostic/excludeopenquestions' . $custommid,
                    get_string('excludeopenquestions', 'local_diagnostic', $custommid),
                    get_string('excludeopenquestionsdesc', 'local_diagnostic'),
                    0
                )
            );

            $settings->add(
                new admin_setting_configtext(
                    'local_diagnostic/midurl' . $custommid,
                    get_string('midurl', 'local_diagnostic', $custommid),
                    get_string('midurldesc', 'local_diagnostic'),
                    '',
                    PARAM_URL
                )
            );

            $settings->add(
                new admin_setting_configstoredfile(
                    'local_diagnostic/midfile' . $custommid,
                    get_string('midfile', 'local_diagnostic', $custommid),
                    get_string('midfiledesc', 'local_diagnostic'),
                    'midfile',
                    $custommid
                )
            );
        }
	}
}
