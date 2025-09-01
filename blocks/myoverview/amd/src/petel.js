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
 * Javascript to initialise the myoverview block.
 *
 * @package    block_myoverview
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'jquery',
        'core/ajax',
        'local_petel/inview',
        'core/notification'
    ],
    function ($, Ajax, inView, Notification) {

        var main = function (numberitems, time) {

            if (numberitems == undefined) {
                numberitems = 1;
            }

            if (time == undefined) {
                time = 3000;
            }

            var courses = [];
            let tmp = [];
            let count = 0;
            let count_all = 0;
            let length = $('.block-myoverview-listitem').length;

            $('.block-myoverview-listitem').each(function (index) {
                let courseid = $(this).attr("data-course-id");

                if (count < numberitems) {
                    tmp.push(courseid);
                    count++;
                    count_all++;

                    if (count == numberitems) {
                        if (tmp.length > 0) {
                            courses.push(tmp);
                        }

                        tmp = [];
                        count = 0;
                    }

                    if (count_all == length) {
                        if (tmp.length > 0) {
                            courses.push(tmp);
                        }

                        tmp = [];
                        count = 0;
                    }
                }
            })

            this.ajax(courses, time);
        };

        var inview = function (time) {

            if (time == undefined) {
                time = 3000;
            }

            let stack = [];
            let self = this;

            inView('.selector-inview')
                .on('enter', function (e){
                    if(!$(e).hasClass('inview-done')){
                        $(e).addClass('inview-done');

                        let courseid = $(e).attr("data-course-id");
                        stack.push(courseid);
                    }
                })
                .on('exit', el => {
                    //el.style.opacity = 0.5;
                });

            setInterval(function(){

                if(stack.length > 0){
                    let courses = [];
                    courses.push(stack);

                    stack = [];
                    self.ajax(courses, 0);
                }

            }, time);

        };

        var ajax = function (courses, time) {

            courses.forEach(function (course, i) {

                setTimeout(function () {

                    Ajax.call([{
                        methodname: 'block_myoverview_get_custom_info_by_course',
                        args: {
                            courseids: JSON.stringify(course)
                        },
                        done: function (response) {

                            JSON.parse(response.result).forEach((element) => {

                                let courseid = element.courseid;
                                let obj = $('.type-petel[data-course-id="' + courseid + '"]');

                                let data = JSON.parse(element.links);
                                $(obj).find('.course-action-menu').html(data);

                                data_events = JSON.parse(element.events);
                                data_activities = JSON.parse(element.activities);
                                data_noactivity = JSON.parse(element.noactivity);

                                if (data_events.length != 0) {
                                    $(obj).find('.myoverview-events').html(data_events);
                                } else {
                                    if (data_activities != 0) {
                                        $(obj).find('.myoverview-events').html(data_activities);
                                    } else {
                                        $(obj).find('.myoverview-events').html(data_noactivity);
                                    }
                                }

                                data = JSON.parse(element.grademe);
                                if (data.text.length > 0) {
                                    $(obj).find('.myoverview-events').append(data.text);
                                }

                                data = JSON.parse(element.quickaccesslink);
                                $(obj).find('.quickaccesslink').html(data.text);

                                if (element.isteacher != 1) {
                                    $(obj).find('.progress-bar-student').show();
                                } else {
                                    $(obj).find('.progress-bar-student').hide();
                                }
                            })
                        },
                        fail: Notification.exception
                    }]);
                }, (i + 1) * time);


            });
        };

        return {
            main: main,
            inview: inview,
            ajax: ajax
        };
    });
