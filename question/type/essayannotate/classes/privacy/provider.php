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
 * Privacy Subsystem implementation for qtype_essayannotate.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @copyright  based on work done by 2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_essayannotate\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for qtype_essayannotate implementing user_preference_provider.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This component has data.
        // We need to return default options that have been set a user preferences.
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\user_preference_provider {


    /**
     * Returns meta data about this system.
     *
     * @param   collection     $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference('qtype_essayannotate_defaultmark', 'privacy:preference:defaultmark');
        $collection->add_user_preference('qtype_essayannotate_responseformat', 'privacy:preference:responseformat');
        $collection->add_user_preference('qtype_essayannotate_responserequired', 'privacy:preference:responserequired');
        $collection->add_user_preference('qtype_essayannotate_responsefieldlines', 'privacy:preference:responsefieldlines');
        $collection->add_user_preference('qtype_essayannotate_attachments', 'privacy:preference:attachments');
        $collection->add_user_preference('qtype_essayannotate_attachmentsrequired', 'privacy:preference:attachmentsrequired');
        $collection->add_user_preference('qtype_essayannotate_maxbytes', 'privacy:preference:maxbytes');
        return $collection;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('qtype_essayannotate_defaultmark', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:defaultmark', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'defaultmark', $preference, $desc);
        }

        $preference = get_user_preferences('qtype_essayannotate_responseformat', null, $userid);
        if (null !== $preference) {
            switch($preference) {
                case 'editor':
                    $stringvalue = get_string('formateditor', 'qtype_essayannotate');
                    break;
                case 'editorfilepicker':
                    $stringvalue = get_string('formateditorfilepicker', 'qtype_essayannotate');
                    break;
                case 'plain':
                    $stringvalue = get_string('formatplain', 'qtype_essayannotate');
                    break;
                case 'monospaced':
                    $stringvalue = get_string('formatmonospaced', 'qtype_essayannotate');
                    break;
                case 'noinline':
                    $stringvalue = get_string('formatnoinline', 'qtype_essayannotate');
                    break;
                default:
                    $stringvalue = get_string('formateditor', 'qtype_essayannotate');
                    break;
            }
            $desc = get_string('privacy:preference:responseformat', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'responseformat', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_essayannotate_responserequired', null, $userid);
        if (null !== $preference) {
            if ($preference) {
                $stringvalue = get_string('responseisrequired', 'qtype_essayannotate');
            } else {
                $stringvalue = get_string('responsenotrequired', 'qtype_essayannotate');
            }
            $desc = get_string('privacy:preference:responserequired', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'responserequired', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_essayannotate_responsefieldlines', null, $userid);
        if (null !== $preference) {
            $desc = get_string('privacy:preference:responsefieldlines', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'responsefieldlines',
                    get_string('nlines', 'qtype_essayannotate', $preference), $desc);
        }
        $preference = get_user_preferences('qtype_essayannotate_attachments', null, $userid);
        if (null !== $preference) {
            if ($preference == 0) {
                $stringvalue = get_string('no');
            } else if ($preference == -1) {
                    $stringvalue = get_string('unlimited');
            } else {
                $stringvalue = $preference;
            }
            $desc = get_string('privacy:preference:attachments', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'attachments', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_essayannotate_attachmentsrequired', null, $userid);
        if (null !== $preference) {
            if ($preference == 0) {
                $stringvalue = get_string('attachmentsoptional', 'qtype_essayannotate');
            } else {
                $stringvalue = $preference;
            }
            $desc = get_string('privacy:preference:attachmentsrequired', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'attachmentsrequired', $stringvalue, $desc);
        }

        $preference = get_user_preferences('qtype_essayannotate_maxbytes', null, $userid);
        if (null !== $preference) {
            switch ($preference) {
                case 52428800:
                    $stringvalue = '50MB';
                    break;
                case 20971520:
                    $stringvalue = '20MB';
                    break;
                case 10485760:
                    $stringvalue = '10MB';
                    break;
                case 5242880:
                    $stringvalue = '5MB';
                    break;
                case 2097152:
                    $stringvalue = '2MB';
                    break;
                case 1048576:
                    $stringvalue = '1MB';
                    break;
                case 512000:
                    $stringvalue = '500KB';
                    break;
                case 102400:
                    $stringvalue = '100KB';
                    break;
                case 51200:
                    $stringvalue = '50KB';
                    break;
                case 10240:
                    $stringvalue = '10KB';
                    break;
                default:
                    $stringvalue = '50MB';
                    break;
            }
            $desc = get_string('privacy:preference:maxbytes', 'qtype_essayannotate');
            writer::export_user_preference('qtype_essayannotate', 'maxbytes', $stringvalue, $desc);
        }
    }
}
