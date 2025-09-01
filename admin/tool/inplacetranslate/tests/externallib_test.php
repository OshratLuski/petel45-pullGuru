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

namespace tool_inplacetranslate;

use moodle_exception;
use tool_inplacetranslate_external;

/**
 * Unit tests for external functions
 *
 * @package     tool_inplacetranslate
 * @covers      \tool_inplacetranslate_external
 * @copyright   2021 Devlionco <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class externallib_test extends \externallib_advanced_testcase {

    /**
     * Test tool_inplacetranslate_external::translation_get_string
     */
    public function test_translation_get_string() {

        $this->setGuestUser();
        $stringid = "345g3456365n476nndfv";

        try {
            $result = tool_inplacetranslate_external::translation_get_string($stringid);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

        $this->setAdminUser();
        $result = tool_inplacetranslate_external::translation_get_string($stringid);
        $this->assertTrue($result->error);

        $stringid = "yes";
        $result = tool_inplacetranslate_external::translation_get_string($stringid);

        $this->assertNotTrue($result->error);
    }

    /**
     * Test tool_inplacetranslate_external::translation_update_string
     */
    public function test_translation_update_string() {
        $this->setGuestUser();
        $stringid = "345g3456365n476nndfv";
        $string = "New test string";

        try {
            $result = tool_inplacetranslate_external::translation_update_string($stringid, $string);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

        $this->setAdminUser();
        $result = tool_inplacetranslate_external::translation_update_string($stringid, $string);
        $this->assertTrue($result->error);
        $this->assertEquals($result->response, get_string('something_wrong', 'tool_inplacetranslate'));

        $stringid = "yes";
        $result = tool_inplacetranslate_external::translation_update_string($stringid, $string);

        $this->assertNotTrue($result->error);
        $this->assertEquals($result->response, get_string('string_updated', 'tool_inplacetranslate'));
    }
}
