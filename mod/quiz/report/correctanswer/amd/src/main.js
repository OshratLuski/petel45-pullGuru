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
 * Some UI stuff for participants page.
 * This is also used by the report/participants/index.php because it has the same functionality.
 *
 * @module     quiz_correctanswer/main
 * @package
 * @copyright  2025 devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Devlion Moodle Development <service@devlion.co>
 */
define(['jquery'],
    function($) {

        return {

            'init': function() {

                $("form.correct-answer").submit(function(event) {
                    event.preventDefault();
                });

                $("#ca_print").click(function() {
                    var printContents = document.querySelector('div[role="main"]').innerHTML;
                    var originalContents = document.body.innerHTML;
                    document.body.innerHTML = printContents;
                    window.print();
                    document.body.innerHTML = originalContents;
                });

                return true;
            }
        };
    });
