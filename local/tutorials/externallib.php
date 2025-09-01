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
 * External functions.
 *
 * Tutorials Moodle system Web services for Human resources and Safety unit SIS via ESB
 *
 * If a user does not exists, create user.
 * If a user exists, update it.
 * Enrol user to course with given role.
 *
 * @package    local_tutorials
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/cohort/lib.php');

class local_tutorials_external extends external_api {

    public static function enrol_users_tutorials_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'courseid'),
                'enrollments' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'username' => new external_value(PARAM_RAW, 'username',VALUE_DEFAULT),
                            'firstname' => new external_value(PARAM_TEXT, 'firstname', VALUE_DEFAULT),
                            'lastname' => new external_value(PARAM_TEXT, 'lastname', VALUE_DEFAULT),
                            'email' => new external_value(PARAM_EMAIL, 'email', VALUE_DEFAULT),
                            'rolename' => new external_value(PARAM_ALPHA, 'rolename',VALUE_DEFAULT)
                        )
                    )
                )
            )
        );
    }

    /**
     * Enrolment of users.
     *
     * Function throw an exception at the first error encountered.
     * @param integer $courseid An array of user enrolment
     * @param array $enrolments An array of user enrolment
     *              $enrolments is array of arrays containing user details
     * @throws dml_exception
     * @since Moodle 3.9
     */
    public static function enrol_users_tutorials($courseid, $enrollments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/lib/moodlelib.php');


        $course = get_course($courseid);
        if (empty($course)) {
            throw new moodle_exception('cant find course', 'enrol_manual');
        }

        $params = self::validate_parameters(self::enrol_users_tutorials_parameters(),
            array('courseid' => $courseid, 'enrollments' => $enrollments));

        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs

        // Retrieve the manual enrolment plugin.
        $enroll = enrol_get_plugin('manual');
        if (empty($enroll)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        foreach ($params['enrollments'] as $enrollment) {

            // Update existing user
            $existinguser = $DB->get_record('user', ['username' => $enrollment['username']]);
            if ($existinguser) { // Check if user exists.
                $user = new \stdClass();
                $user->id = $existinguser->id;
                $user->username = strtolower($enrollment['username']);
                $user->firstname = $enrollment['firstname'];
                $user->lastname = $enrollment['lastname'];
                $user->email = $enrollment['email'];
                user_update_user($user,false,false); // update user record, based on userid.
                $enrollment['userid'] = $user->id;

            }
            else {  // Create user, if it does not exists
                $newuser = new \stdClass();
                $newuser->username = strtolower($enrollment['username']);
                $newuser->firstname = $enrollment['firstname'];
                $newuser->lastname = $enrollment['lastname'];
                $newuser->email = $enrollment['email'];
                $newuser->lang = 'en';
                $newuser->confirm = 1;
                $newuser->mnethostid = $CFG->mnet_localhost_id;
                $newuser->password = generate_password();
                $new_user = user_create_user($newuser, false,false);
                $enrollment['userid'] = $new_user;

            }

            $roleid = $DB->get_record('role', ['shortname' => $enrollment['rolename']],'id,shortname');
            $enrollment['roleid'] = $roleid -> id;
            $enrollment['rolename'] = $roleid -> shortname;
            // Ensure the current user is allowed to run this function in the enrolment context.
            $context = context_course::instance($courseid, IGNORE_MISSING);

            // TODO: not sure if we can do this in a web service (nadavkav)
            //validate_context($context);
            // Check that the user has the permission to manual enrol.
            require_capability('enrol/manual:enrol', $context);

            // Throw an exception if user is not able to assign the role.
            $roles = get_assignable_roles($context);
            if (!array_key_exists($enrollment['roleid'], $roles)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrollment['roleid'];
                $errorparams->courseid = $courseid;
                $errorparams->userid = $enrollment['userid'];
                throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
            }

            // Check manual enrolment plugin instance is enabled/exist.
            $instance = null;
            $enrollinstances = enrol_get_instances($courseid, true);
            foreach ($enrollinstances as $courseenrolinstance) {
                if ($courseenrolinstance->enrol == "manual") {
                    $instance = $courseenrolinstance;
                    break;
                }
            }
            if (empty($instance)) {
                $errorparams = new stdClass();
                $errorparams->courseid = $courseid;
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
            }

            // Check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin).
            if (!$enroll->allow_enrol($instance)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrollment['roleid'];
                $errorparams->courseid = $courseid;
                $errorparams->userid = $enrollment['userid'];
                throw new moodle_exception('wscannotenrol', 'enrol_manual', '', $errorparams);
            }

            // Finally proceed the enrolment.
            $enrollment['timestart'] = isset($enrollment['timestart']) ? $enrollment['timestart'] : time();
            $enrollment['timeend'] = isset($enrollment['timeend']) ? $enrollment['timeend'] : 0;
            $enrollment['status'] = (isset($enrollment['suspend']) && !empty($enrollment['suspend'])) ?
                ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enroll->enrol_user($instance, $enrollment['userid'], $enrollment['roleid'],
                $enrollment['timestart'], $enrollment['timeend'], $enrollment['status']);

            // if role is editingteacher or assistantteacher enroll to cohort teachers
            $support_roles = ($CFG->feinberg_support_roles) ? $CFG->feinberg_support_roles : 'editingteacher,assistantteacher';
            $supportrole = explode(',',$support_roles);
            if (in_array($enrollment['rolename'], $supportrole))
            {
                $cohortid = $DB->get_record('cohort', ['idnumber' => $CFG->defaultcohortscourserequest],'id');
                cohort_add_member($cohortid->id, $enrollment['userid']);
            }

        }
        $transaction->allow_commit();
        return ['result' => 'OK'];
    }

    /**
     * Returns description of method result value.
     *
     * @returncourseid
     * @since Moodle 2.2courseid */
    public static function enrol_users_tutorials_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_TEXT, 'text'),
            )
        );
    }

    public static function unenrol_users_tutorials_parameters() {
        return new external_function_parameters(array(
            'enrollments' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'username' => new external_value(PARAM_RAW, 'The user that is going to be unenrolled'),
                        'courseid' => new external_value(PARAM_RAW, 'The course to unenrol the user from'),
                        'rolename' => new external_value(PARAM_RAW, 'The user role name', VALUE_OPTIONAL)
                    )
                )
            )
        ));
    }

    /**
     * Unenrolment of users.
     *
     * @param array $enrollments an array of course user and role ids
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function unenrol_users_tutorials($enrollments) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::unenrol_users_tutorials_parameters(), array('enrollments' => $enrollments));
        require_once($CFG->libdir . '/enrollib.php');
        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }
        $usersunenrolled = [];
        foreach ($params['enrollments'] as $enrollment) {
            $context = context_course::instance($enrollment['courseid']);
            require_capability('enrol/manual:unenrol', $context);

            $user = $DB->get_record('user', array('username' => strtolower($enrollment['username'])));
            if (!$user) {
                throw new moodle_exception('Username not exist: '.$enrollment['username']);
            }

            if ($enrollment['rolename']) {
                $roleid = $DB->get_record('role', ['shortname' => $enrollment['rolename']], 'id');

                $isenrole = $DB->get_record('role_assignments',array('contextid' => $context->id, 'userid' => $user->id, 'roleid' =>$roleid->id));
                if (!$isenrole) {
                    throw new moodle_exception("The user:".$user->username." is not registered as ".$enrollment['rolename']." in courseid = ".$enrollment['courseid'] );
                }
                $assignments = $DB->get_records('role_assignments',array('contextid' => $context->id, 'userid' => $user->id));

                //Checking if enrolled the course in several rolles
                if (sizeof($assignments) > 1) {
                    // Deleting a specific role
                    role_unassign_all(array('userid'=>$user->id, 'contextid'=>$context->id,'roleid' =>$roleid->id), false, false);
                    $transaction->allow_commit();
                    return ['result' => 'OK' ];
                }
            }

            $instance = $DB->get_record('enrol', array('courseid' => $enrollment['courseid'], 'enrol' => 'manual'));
            if (!$instance) {
                //throw new moodle_exception('wsnoinstance', 'local_petel','', $enrollment);
                throw new moodle_exception("Manual enrolment doesn't exist or is disabled for role ".$enrollment['rolename']." in courseid = ".$enrollment['courseid'] );
            }

            if (!$enrol->allow_unenrol($instance)) {
                throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrollment);
            }
            $enrol->unenrol_user($instance, $user->id);
            $usersunenrolled[] = ['username' => $enrollment['username']];

            //unenroll from teachers cohort  17.2.20 ntberko

            $support_roles = ($CFG->feinberg_support_roles) ? $CFG->feinberg_support_roles : 'editingteacher,assistantteacher';
            $supportrole = explode(',',$support_roles);
            if (in_array($enrollment['rolename'], $supportrole)) {
                $sql = "SELECT r.shortname
                        FROM {role_assignments} rs
                        JOIN {role} r ON r.id = rs.roleid
                        WHERE rs.userid = $user->id
                        GROUP BY r.shortname";
                //get user other roles in courses
                $user_roles = $DB->get_records_sql($sql);
                $unenroll_from_cohort = true;
                //if the user does not teach any course, his cohort registration deleted
                foreach ($user_roles as $role) {
                    if (in_array($role->shortname, $supportrole)) {
                        $unenroll_from_cohort = false;
                    }
                }
                if ($unenroll_from_cohort) {
                    $cohortid = $DB->get_record('cohort', ['idnumber' => $CFG->defaultcohortscourserequest], 'id');
                    cohort_remove_member($cohortid->id, $user->id);
                }
            }
        }
        $transaction->allow_commit();
        return ['result' => 'OK' ];
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     */
    public static function unenrol_users_tutorials_returns() {
        /*
        return new external_function_parameters(array(
            'unenrolled' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'username' => new external_value(PARAM_RAW, 'The user that was unenrolled'),
                    )
                )
            )
        ));
        */

        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_TEXT, 'text'),
            )
        );

    }

}