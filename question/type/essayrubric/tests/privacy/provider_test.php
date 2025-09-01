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
 * Privacy provider tests.
 *
 * @package    qtype_essayrubric
 * @copyright  2021 The Open university
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_essayrubric\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\user_preference_provider;
use qtype_essayrubric\privacy\provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/essayrubric/classes/privacy/provider.php');

/**
 * Privacy provider tests class.
 *
 * @package    qtype_essayrubric
 * @copyright  2021 The Open university
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider_test extends \core_privacy\tests\provider_testcase {
    // Include the privacy helper which has assertions on it.

    public function test_get_metadata() {
        $collection = new \core_privacy\local\metadata\collection('qtype_essayrubric');
        $actual = \qtype_essayrubric\privacy\provider::get_metadata($collection);
        $this->assertEquals($collection, $actual);
    }

    public function test_export_user_preferences_no_pref() {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test the export_user_preferences given different inputs
     * @dataProvider user_preference_provider

     * @param string $name The name of the user preference to get/set
     * @param string $value The value stored in the database
     * @param string $expected The expected transformed value
     */
    public function test_export_user_preferences($name, $value, $expected) {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        set_user_preference("qtype_essayrubric_$name", $value, $user);
        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());
        $this->assertTrue($writer->has_any_data());
        $preferences = $writer->get_user_preferences('qtype_essayrubric');
        foreach ($preferences as $key => $pref) {
            $preference = get_user_preferences("qtype_essayrubric_{$key}", null, $user->id);
            if ($preference === null) {
                continue;
            }
            $desc = get_string("privacy:preference:{$key}", 'qtype_essayrubric');
            $this->assertEquals($expected, $pref->value);
            $this->assertEquals($desc, $pref->description);
        }
    }

    /**
     * Create an array of valid user preferences for the multiple choice question type.
     *
     * @return array Array of valid user preferences.
     */
    public function user_preference_provider() {
        return [
                'default mark 2' => ['defaultmark', 2, 2],
                'responseformat editror ' => ['responseformat', 'editor', get_string('formateditor', 'qtype_essayrubric')],
                'responseformat editor and filepicker ' =>
                        ['responseformat', 'editorfilepicker', get_string('formateditorfilepicker', 'qtype_essayrubric')],
                'responseformat plain ' => ['responseformat', 'plain', get_string('formatplain', 'qtype_essayrubric')],
                'responseformat monospaced ' => ['responseformat', 'monospaced', get_string('formatmonospaced', 'qtype_essayrubric')],
                'responseformat noinline ' => ['responseformat', 'noinline', get_string('formatnoinline', 'qtype_essayrubric')],
                'responserequired yes' => ['responserequired', 1, get_string('responseisrequired', 'qtype_essayrubric')],
                'responserequired no ' => ['responserequired', 0, get_string('responsenotrequired', 'qtype_essayrubric')],
                'responsefieldlines 10' => ['responsefieldlines', 10, '10 lines'],
                'attachments none' => ['attachments', 0, get_string('no')],
                'attachments 3' => ['attachments', 3, '3'],
                'attachments unlimited' => ['attachments', -1, get_string('unlimited')],
                'attachmentsrequired optional' => ['attachmentsrequired', 0, get_string('attachmentsoptional', 'qtype_essayrubric')],
                'attachmentsrequired 1' => ['attachmentsrequired', 1, '1'],
                'maxbytes 50KB' => ['maxbytes', 51200, '50KB']
        ];
    }
}
