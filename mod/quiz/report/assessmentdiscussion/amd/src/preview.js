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
 * Javascript for lazy load of statuses.
 *
 * @package
 * @copyright  2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'jquery',
        'core/ajax',
        'quiz_assessmentdiscussion/inview',
        'core/notification'
    ],
    function($, Ajax, inView, Notification) {
        var courseid = null;

        let getPreview = function(attempts) {

            if (attempts.length > 0) {

                Ajax.call([{
                    methodname: 'quiz_assessmentdiscussion_preview',
                    args: {
                        attempts: JSON.stringify(attempts),
                    },
                    done: function(resp) {
                        let response = JSON.parse(resp.data);
                        response.forEach(function(item) {
                            $('#preview_answer_block_' + item.attemptid).html(item.html);
                            $('#preview_overlay_block_' + item.attemptid).html(item.html);
                        });
                    },
                    fail: Notification.exception
                }]);
            }

        };
        let inview = function() {
            let attempts = [];
            inView('.inview_preview')
                .on('enter', function(e) {

                    if (!$(e).hasClass('inview-done')) {

                        let tmp = {
                            'attemptid': $(e).attr("data-attemptid"),
                            'qid': $(e).attr("data-qid"),
                            'cmid': $(e).attr("data-cmid"),
                            'slot': $(e).attr("data-slot"),
                        };

                        attempts.push(tmp);

                        $(e).addClass('inview-done');

                        let spinner = '<div class="spinner-border" style="display: block; margin-left: auto; margin-right: auto;" role="status">' +
                            '<span class="sr-only"></span></div>';
                        $(e).html(spinner);

                    }
                });

            setInterval(function() {
                if (attempts.length > 0) {
                    getPreview(attempts);
                    attempts = [];
                }
            }, 500);

        };
        return {
            inview: inview,
        };
    });
