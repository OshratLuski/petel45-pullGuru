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
 * Behat steps definitions for essay annotate.
 *
 * @package    qtype_essayannotate
 * @subpackage essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Nideesh N, VM Sreeram
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Steps definitions related with the essayannotate question type.
 *
 * @copyright 2024 IIT Palakkad
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qtype_essayannotate extends behat_base {

    /**
     * Simulate annotating the pdf
     *
     * @When I annotate the pdf
     */
    public function i_annotate_the_pdf() {

        $js = "
        var pathString = 'M 100 100 Q 125 125 150 150 Q 175 175 200 200 L 200 200';

        var path = new fabric.Path(pathString, {
            fill: '',
            stroke: 'black',
            strokeWidth: 2,
            strokeLineCap: 'round',
            strokeLineJoin: 'round'
        });
        pdf.fabricObjects[0]._objects.push(path)
        pdf.fabricObjects[0].renderAll()
        ";
        $this->execute_script($js);
        sleep(3);
    }

    /**
     * Set the path to gs and convert in behat test environment
     *
     * @When path to gs and covert is set
     */
    public function path_to_gs_and_convert_is_set() {
        $plugin = 'qtype_essayannotate';

        $gspath = shell_exec(escapeshellcmd("which gs"));
        $convertpath = shell_exec(escapeshellcmd("which convert"));

        $configurations = [
            'ghostscriptpath' => $gspath,
            'imagemagickpath' => $convertpath,
        ];

        // Update existing configurations in the database.
        foreach ($configurations as $name => $value) {
            global $DB;
            $existingrecord = $DB->get_record('config_plugins', ['plugin' => $plugin, 'name' => $name]);

            if ($existingrecord) {
                $existingrecord->value = $value;
                $DB->update_record('config_plugins', $existingrecord);
            } else {
                $record = new stdClass();
                $record->plugin = $plugin;
                $record->name = $name;
                $record->value = $value;
                $DB->insert_record('config_plugins', $record);
            }
        }
    }

    /**
     * Navigating to the new window from current window as comment.php opens in new window
     *
     * @Then /^The document should open in a new tab$/
     */
    public function document_should_open_in_new_tab() {
        $session     = $this->getSession();
        $windownames = $session->getWindowNames();
        if (count((array)$windownames) < 2) {
            throw new \ErrorException("Expected to see at least 2 windows opened");
        }

        $session->switchToWindow($windownames[1]);
    }

    /**
     * Navigating to the new window from current window as comment.php opens in new window
     *
     * @Then /^shift focus to the latest tab$/
     */
    public function shift_focus_to_latest_tab() {
        $session     = $this->getSession();
        $windownames = $session->getWindowNames();
        $numwindows = count((array)$windownames);
        $session->switchToWindow($windownames[$numwindows - 1]);
    }

    /**
     * Switching back to review.php window from new window opened for annotation
     *
     * @Then /^I switch to main window$/
     */
    public function i_switch_to_main_window() {
        $session = $this->getSession();
        $windownames = $session->getWindowNames();
        $session->switchToWindow($windownames[0]);
    }
}
