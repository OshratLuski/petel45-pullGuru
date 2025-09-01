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
 * A javascript module to handle toggling question chooser recommendations.
 *
 * * @package local_question_chooser
 *  * @copyright 2022 Devlion.co
 *  * @author Devlion
 *  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'core/ajax',
        'core/notification'
    ],
    function(
        Ajax,
        Notification
    ) {

        /**
         * Do an ajax call to toggle the recommendation
         *
         * @param  {object} e The event
         * @return {void}
         */
        const toggleRecommendation = (e) => {
            let data = {
                methodname: 'local_question_chooser_toggle_qtypes_recommendation',
                args: {
                    name: e.currentTarget.dataset.name
                }
            };
            Ajax.call([data])[0].fail(Notification.exception);
        };

        return {
            init: function() {
                const checkboxelements = document.querySelectorAll("[data-name]");
                checkboxelements.forEach((checkbox) => {
                    checkbox.addEventListener('change', toggleRecommendation);
                });
            }
        };
    });
