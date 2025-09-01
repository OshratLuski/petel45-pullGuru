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
 * Some UI stuff for table.
 *
 * @package
 * @copyright  2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Devlion Moodle Development <service@devlion.co>
 */

define([
    "jquery",
    "jqueryui",
    "core/modal_factory",
    "core/modal_events",
    "core/ajax",
    "core/str",
    "community_social/loadingSpinner",
], function($, jqui, ModalFactory, ModalEvents, Ajax, Str, loading) {

    let actualusers = '';

    return {
        load: function (quizid, cmid, courseid, lastaccess, _actualusers) {
            actualusers = _actualusers;
            Ajax.call([
                {
                    methodname: "quiz_competencyoverview_get_init_params",
                    args: {
                        'quizid': quizid,
                        'cmid': cmid,
                        'courseid': courseid,
                        'lastaccess': lastaccess,
                    },
                    done: params => {

                        params = JSON.parse(params.params);

                        let hlfilterranges = params.hlfilterranges;
                        let hlfilterrangescolor = params.hlfilterrangescolor;
                        let assign = params.assign;
                        let users = params.users;
                        let currentcourseid = params.courseid;
                        let topskills = params.topskills;
                        let _quizid = params.quizid;
                        let _cmid = params.cmid;
                        let complist = params.complist;
                        let _lastaccess = params.lastaccess;

                        this.init(
                            hlfilterranges, hlfilterrangescolor, assign, users, currentcourseid,
                            topskills, _quizid, _cmid, complist, _lastaccess
                        );
                    },
                    fail: {}
                }
            ]);
        },

        init: function(
            hlfilterranges, hlfilterrangescolor, assign, users, currentcourseid,
            topskills, quizid, cmid, complist, lastaccess
        ) {

            let langStrings = [];
            let strings = [
                {
                    key: 'task_from_course',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'recommended_task_from_shared_repository',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'message_to_students_place',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'only_to_stud',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'more',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'sending',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'title_aa',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'selection',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'course_name',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'activity',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'recommend_activity_list',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'recommend_activity_view',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'target_section',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'message_to_students_place_edit',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'message_to_students_place_view',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'ok',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'finish',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'submit',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'back',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'stud_list',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'default_section_name',
                    component: 'quiz_competencyoverview'
                },
                {
                    key: 'selectall',
                    component: 'core'
                },
                {
                    key: 'deselectall',
                    component: 'core'
                },
                {
                    key: 'competency_list_title',
                    component: 'quiz_competencyoverview'
                }
            ];

            $('#user-action').prop('disabled', true);

            Str.get_strings(strings).then(function(results) {
                langStrings = results;
            });

            // HL coloring.
            $(".skill-grade-cell").each(function() {
                let skillgrade = $(this).data("skill-grade");
                let cell = $(this);
                $.each(hlfilterrangescolor, function(index, value) {
                    if (skillgrade >= value[0] && skillgrade <= value[1]) {
                        cell.closest("td").addClass(value[3], 200);
                    }
                });
            });

            // Selected users. Assign activities (AA).

            $(document).on("click", ".selected-users", function() {
                actionStatus();
                setskills();
            });

            $(document).on("click", "#user-action", function(e) {
                e.preventDefault();
                useraction();
            });

            let selectedusers = [];
            let currentstate;
            let selectedsource;
            let selectedcourse;
            let selectedactivity;
            let selecteditem;
            let selectedtargetsection;
            let messagetostudents;
            let modalbody;
            let aastates;
            let flow = [];

            let submitted = false;
            let sentusers = [];

            let selectedskills = [];

            let skillname = '';

            let selectall = true;
            let rows = [];
            let ranges = [];

            /**
             *
             */
            function redrawMain() {
                let mainbody = "";
                switch (currentstate) {
                    case "selection":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(0).addClass('active');
                        mainbody = `
                            <div class='d-flex mt-100'>
                            <button id='aa_source_course' type="button" class='aa_h select_source btn-lg btn-block mr-20'>
                            ` + langStrings[0] + `</button>
                            <button id='aa_source_repository' type="button" class='aa_h select_source btn-lg btn-block'>
                            ` + langStrings[1] + `</button>
                            </div>
                            `;
                        break;
                    case "course":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(1).addClass('active');
                        mainbody = "";
                        getCourses();
                        break;
                    case "activity":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(2).addClass('active');
                        mainbody = "";
                        getActivities(selectedcourse);
                        break;
                    case "repository":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(1).addClass('active');
                        mainbody = "";
                        getItems();
                        break;
                    case "item":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(1).addClass('active');
                        mainbody = "";
                        getItem();
                        break;
                    case "targetsection":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(2).addClass('active');
                        mainbody = "";
                        getTargetsections();
                        break;
                    case "message":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(3).addClass('active');
                        mainbody = `
                            <div class="input-group">
                            <textarea id="messagetostudents" class="form-control" rows="5" aria-label=""
                            placeholder="` + langStrings[2] + `"></textarea>
                            </div>
                            `;
                        break;
                    case "finish":
                        $('.progress-bar-item').removeClass('active');
                        $('.progress-bar-item').eq(3).addClass('active');
                        $('#aa_st_list').css("visibility", "visible");
                        mainbody =
                            `
                            <div id="aa_finish" class="">
                            <div class="alert alert-info" role="alert">
                            <p>` + langStrings[3] + `</p>
                            </div>
                            <div class="">
                            <div class="text-muted muted">` +
                            messagetostudents +
                            `</div>
                            </div>
                            </div>
                            `;
                        break;
                    default:
                        break;
                }
                $("#aa_main").html(mainbody);
                $("#aa_main").css("visibility", "visible");

                $("#backbtn").off("click");
                $("#backbtn").on("click", () => {
                    switchState(flow.pop(), true);
                });

                if (aastates[currentstate].nextButton) {
                    $("#nextbtn").addClass("d-none");
                    if (aastates[currentstate].nextButtonEnabled) {
                        $("#nextbtn").removeClass("d-none");
                    }
                    $("#nextbtn").text(aastates[currentstate].nextButtonText);
                } else {
                    $("#nextbtn").addClass("d-none");
                }

                $("#nextbtn").off("click");
                $("#nextbtn").on("click", () => {
                    if (!submitted) {
                        switchState(aastates[currentstate].nextAction);
                    }
                    if ($("#nextbtn").data('finished') == 'true') {
                        submitted = false;
                        mainmodal.hide();
                    }

                });

                $(".select_source").off("click");
                $(".select_source").on("click", e => {
                    switch (e.target.id) {
                        case "aa_source_course":
                            selectedsource = "course";
                            switchState("course");
                            break;
                        case "aa_source_repository":
                            selectedsource = "repository";
                            switchState("repository");
                            break;
                        default:
                            break;
                    }
                });

                $("#aa_bk").html(aastates[currentstate].title);
                if (aastates[currentstate].nextButton) {
                    $("#nextbtn").value = aastates[currentstate].nextButtonText;
                }
                if (aastates[currentstate].backButton) {
                    $("#backbtn").css("visibility", "visible");
                } else {
                    $("#backbtn").css("visibility", "hidden");
                }
                $("#aa_main_col").fadeIn("slow");

                // Hlsentdusers();

            }

            /**
             * @param state
             * @param back
             */
            function switchState(state, back = false) {
                if (state == "submit") {
                    aaSubmit();
                    return;
                }
                if (!back) {
                    flow.push(currentstate);
                }
                if (currentstate == "message") {
                    messagetostudents = $("#messagetostudents").val();
                }
                $("#aa_main_col").hide();
                currentstate = state;
                redrawMain();
            }

            /**
             *
             */
            function hlsentdusers() {
                $("#mod-quiz-report-competencyoverview-report-table tbody tr")
                    .not(".emptyrow")
                    .not(".gradedattempt")
                    .each(function() {
                        if ($.inArray($(this)[0].cells[0].children[0].dataset.selectedUserid, sentusers) != -1) {
                            this.firstElementChild.classList.add('sent');
                        }
                    });
            }

            /**
             *
             */
            function aaSubmit() {

                submitted = true;

                $("#backbtn").css("visibility", "hidden");

                $('.progress-bar-item').removeClass('active');
                $('.progress-bar-item').eq(4).addClass('active');
                $('#aa_st_list').css("visibility", "hidden");
                $("#aa_main").html(`
                <div class = "bravo-img-wrapper mr-auto">
                  <h2>` + langStrings[5] + `</h2>
                  <div class="img-background"
                    style="background-image: url('${M.cfg.wwwroot}/mod/quiz/report/competencyoverview/pix/bravo.png')"
                  </div>
                </div>
                `);

                let selectedusersjoin = selectedusers.join();

                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_submit_assignment",
                        args: {
                            'selectedusersjoin': selectedusersjoin,
                            'selectedsource': selectedsource,
                            'selectedcourse': selectedcourse ? selectedcourse : 0,
                            'selectedactivity': selectedactivity ? selectedactivity : 0,
                            'selecteditem': selecteditem ? selecteditem : 0,
                            'selectedtargetsection': selectedtargetsection,
                            'messagetostudents': messagetostudents,
                            'currentcourseid': currentcourseid,
                        },
                        done: () => {
                            selectedusers.forEach(element => {
                                sentusers.push(element.toString());
                            });
                            hlsentdusers();
                            $("#nextbtn").data('finished', 'true');
                            return;
                        },
                        fail: {}
                    }
                ]);
            }

            /**
             *
             */
            function getCourses() {
                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_courses",
                        args: {},
                        done: courses => {
                            let courseslist = JSON.parse(courses.courses);
                            let list =
                                '<div class="aa_scroll"><div id="aa_course_list" class="list-group list-group-flush">';
                            for (let key in courseslist) {
                                list +=
                                    `<a href="#" class="aa_course list-group-item"
                                    data-selectedcourseid=` + courseslist[key].id +
                                    ">" +
                                    courseslist[key].shortname;
                                list +=
                                    '&nbsp<span class="text-muted aa_modname">' +
                                    courseslist[key].fullname +
                                    "</span>";
                                list += "</a>";
                            }
                            list += "</div></div>";
                            $("#aa_main").html(list);

                            // TODO Refactor.
                            $("#aa_course_list a").on("click", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_course_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedcourse = $(this).data("selectedcourseid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");
                            });

                            $("#aa_course_list a").on("dblclick", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_course_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedcourse = $(this).data("selectedcourseid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");

                                switchState(aastates[currentstate].nextAction);
                            });
                        },
                        fail: {}
                    }
                ]);
            }

            /**
             * @param courseid
             */
            function getActivities(courseid) {
                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_activities",
                        args: {courseid: courseid},
                        done: activities => {
                            let activitieslist = JSON.parse(activities.activities);
                            let list =
                                '<div class="aa_scroll"><div id="aa_activity_list" class="list-group list-group-flush">';
                            for (let key in activitieslist) {
                                list +=
                                    `<a href="#" class="aa_activity list-group-item list-group-item-secondary"
                                    data-selectedactivityid=` + activitieslist[key].id +
                                    ">" +
                                    activitieslist[key].shortname;
                                list +=
                                    '&nbsp<span class="text-muted aa_modname">' +
                                    activitieslist[key].modname +
                                    "</span>";
                                list += "</a>";
                            }
                            list += "</div></div>";
                            $("#aa_main").html(list);

                            // TODO Refactor.
                            $("#aa_activity_list a").on("click", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_activity_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedactivity = $(this).data("selectedactivityid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");
                            });
                            $("#aa_activity_list a").on("dblclick", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_activity_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedactivity = $(this).data("selectedactivityid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");

                                switchState(aastates[currentstate].nextAction);
                            });
                        },
                        fail: {}
                    }
                ]);
            }

            /**
             *
             */
            function getItems() {
                loading.show();
                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_items",
                        args: {
                            'skills': selectedskills.join(),
                        },
                        done: items => {
                            loading.remove();
                            let itemslist = JSON.parse(items.items);
                            let list =
                                `<div class="aa_scroll">
                                    <div id="aa_item_list" class="list-group list-group-flush px-2">`;
                            for (let key in itemslist) {

                                list += '<div class="js--cardgroup_title cardgroup_title"><span class="carret-down"></span>'
                                    + itemslist[key][0] + '</div><div class="card_wrapper">';
                                for (let key2 in itemslist[key][1]) {
                                    list += itemslist[key][1][key2].item;
                                }
                                list = list.replaceAll('mx-3', '');
                                list = list.replaceAll('flex-xl-nowrap', '');
                                list += "</div>";
                            }
                            list += "</div></div>";
                            $("#aa_main").html(list);
                            $('.js--cardgroup_title').click(function() {
                                $(this).toggleClass('active');
                                $(this).next().slideToggle();
                            });
                            $('.quiz_competencyoverview-select-button').off('click');
                            $('.quiz_competencyoverview-select-button').on('click', e => {
                                selecteditem = $(e.target).data('activity_id');
                                selectedcourse = $(e.target).data('course_id');
                                switchState(aastates[currentstate].nextAction);
                            });
                        },
                        fail: {}
                    }
                ]);
            }

            /**
             *
             */
            function getItem() {
                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_item",
                        args: {'itemid': selecteditem},
                        done: item => {
                            let itemone = JSON.parse(item.item);
                            let list =
                                '<div class="aa_scroll"><div id="aa_item_list" class="list-group list-group-flush">';
                            list += itemone;
                            list += "</div></div>";
                            $("#aa_main").html(list);
                            $('#nextbtn').removeClass('d-none');
                            $("#nextbtn").css("visibility", "visible");
                        },
                        fail: {}
                    }
                ]);
            }

            /**
             *
             */
            function getTargetsections() {
                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_targetsections",
                        args: {currentcourseid},
                        done: targetsections => {
                            let targetsectionslist = JSON.parse(targetsections.sections);
                            let imgurl = targetsections.imgurl;
                            let coursename = targetsections.coursename;
                            let list =
                                `<div class="coursename-wrapper">
                                <div class="coursename-image">
                                <img src="${imgurl}" width="auto" height="auto"/>
                                </div>
                                <h3 class="coursename">${coursename}</h3>
                                </div>
                                <div class="aa_scroll"><div id="aa_targetsection_list"
                                class="list-group list-group-flush">`;
                            for (let key in targetsectionslist) {
                                let sectionline =
                                    targetsectionslist[key].name ? targetsectionslist[key].name :
                                        langStrings[20] + ' ' + targetsectionslist[key].section;
                                list +=
                                    `<a href="#" class="aa_targetsection list-group-item list-group-item-secondary"
                                    data-selectedtargetsectionid=` +
                                    targetsectionslist[key].id +
                                    ">" +
                                    sectionline;
                                list += "</a>";
                            }
                            list += "</div></div>";
                            $("#aa_main").html(list);
                            if (imgurl === null) {
                                $('.coursename-image').remove();
                            }

                            // TODO Refactor.
                            $("#aa_targetsection_list a").on("click", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_targetsection_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedtargetsection = $(this).data("selectedtargetsectionid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");
                            });

                            $("#aa_targetsection_list a").on("dblclick", function(e) {
                                e.preventDefault();
                                Array.prototype.forEach.call($("#aa_targetsection_list a"), a => {
                                    $(a).removeClass("active");
                                });
                                selectedtargetsection = $(this).data("selectedtargetsectionid");
                                $("#nextbtn").removeClass("d-none");
                                $("#nextbtn").css("visibility", "visible");
                                $(this).toggleClass("active");

                                switchState(aastates[currentstate].nextAction);
                            });

                        },
                        fail: {}
                    }
                ]);
            }

            let mainmodal;

            /**
             *
             */
            function useraction() {
                // Popup (AA) states.
                initAa();
                ModalFactory.create({
                    title:
                        langStrings[6],
                    body: modalbody,
                    large: true
                }).then(function(aamodal) {
                    mainmodal = aamodal;
                    let aaroot = aamodal.getRoot();
                    aaroot.on(ModalEvents.hidden, function() {
                        aamodal.destroy();
                        return;
                    });
                    aamodal.getRoot().addClass('competencies_wizard');
                    aamodal.show();
                    redrawMain();
                        return;
                    }).catch();
            }

            /**
             *
             */
            function initAa() {
                aastates = {
                    selection: {
                        backButton: false,
                        nextButton: false,
                        nextButtonEnabled: false,
                        nextButtonText: false,
                        scroll: false,
                        parent: false,
                        nextAction: false,
                        title: langStrings[7]
                    },
                    course: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: false,
                        nextButtonText: langStrings[15],
                        scroll: true,
                        parent: "selection",
                        nextAction: "activity",
                        title: langStrings[8]
                    },
                    activity: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: false,
                        nextButtonText: langStrings[15],
                        scroll: true,
                        parent: "course",
                        nextAction: "targetsection",
                        title: langStrings[9]
                    },
                    repository: {
                        backButton: true,
                        nextButton: false,
                        nextButtonEnabled: false,
                        nextButtonText: false,
                        scroll: true,
                        parent: "selection",
                        nextAction: "item",
                        title: langStrings[10]
                    },
                    item: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: true,
                        nextButtonText: langStrings[15],
                        scroll: false,
                        parent: "repository",
                        nextAction: "targetsection",
                        title: langStrings[11]
                    },
                    targetsection: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: false,
                        nextButtonText: langStrings[15],
                        scroll: true,
                        parent: "selection",
                        nextAction: "message",
                        title: langStrings[12]
                    },
                    message: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: true,
                        nextButtonText: langStrings[15],
                        scroll: false,
                        parent: "selection",
                        nextAction: "finish",
                        title: langStrings[13]
                    },
                    finish: {
                        backButton: true,
                        nextButton: true,
                        nextButtonEnabled: true,
                        nextButtonText: langStrings[16],
                        scroll: false,
                        parent: "selection",
                        nextAction: "submit",
                        title: langStrings[14]
                    }
                };
                currentstate = "selection";
                messagetostudents = "";
                let userslist = "";
                selectedusers.forEach(element => {
                    userslist += "<li class='p-2'>" + users[element].firstname
                        + ' ' + users[element].lastname + '</li>';
                });

                setskills();

                let selcomplist = [];
                selectedskills.forEach(element => {
                    selcomplist.push(complist[element]);
                });

                modalbody =
                    `
                    <div class='custom_container container'>
                    <div id='assign_activity_body' class='row'>

                    <div id='aa_main_col' class='col-8 d-flex flex-column justify-content-start'>

                    <div class="progress-bar-wrapper">
                    <div class="progress-bar">
                    </div>
                    <div class="progress-bar-item active">1</div>
                    <div class="progress-bar-item">2</div>
                    <div class="progress-bar-item">3</div>
                    <div class="progress-bar-item">4</div>
                    <div class="progress-bar-item">5</div>

                    </div>

                    <div id='aa_bk' class=''>

                    </div>
                    <div id='aa_main' class='d-flex justify-content-center flex-grow-1 overflow-hidden
                        flex-wrap' style='visibility: hidden;'>

                    </div>
                    <div id='aa_actions' style='visibility: hidden;' class='d-flex mt-5'>
                    <button id='backbtn' class="btn btn-secondary competencies_wizard-button 
                    competencies_wizard-button-outline mr-20">`
                        + langStrings[18] + `</button>
                    <button id='nextbtn' class="btn btn-primary competencies_wizard-button d-none">` + langStrings[16] + `</button>
                    </div>
                    </div>

                    <div id='aa_st_list' class='col-4'>
                    <div class="competency-popup-subtitle">
                    <h3>` + langStrings[23] + `</h3>
                    <p class="competency-list">` + selcomplist.join(' | ') + `</p>
                    </div>
                    <h3 class=''>` + langStrings[19] + `</h3>
                    <ul class="aa_scroll_students m-l-0 list-group list-group-flush">
                    ` +
                    userslist +
                    `
                    </ul>
                    </div>

                    </div>
                    </div>
                    `;
            }

            /**
             *
             */
            function actionStatus() {
                selectedusers = [];
                $(".selected-users").each(function() {
                    if ($(this).closest("input")[0].checked) {
                        selectedusers.push(
                            $(this)
                                .closest("input")
                                .data("selected-userid")
                        );
                    }
                });
                if (selectedusers.length != 0 && selectedskills.length != 0) {
                    $("#user-action").html(
                        assign + " | " + selectedusers.length
                    );
                    $('#user-action').prop('disabled', false);
                } else {
                    $("#user-action").html(
                        assign
                    );
                    $('#user-action').prop('disabled', true);
                }
            }

            // Select all.
            $("#selectallstudents").on("click", (e) => {
                e.preventDefault();
                resetfilters();
                setskills();
                selectallstudents();
                actionStatus();
            });

            // Select skills.
            $(".selected-comp").on("click", () => {
                setskills();
                actionStatus();
            });

            /**
             *
             */
            function selectallstudents() {
                $(".selected-users").each(function() {
                    $(this).closest("input")[0].checked = selectall;
                });
                let selecttitle = selectall ? langStrings[22] : langStrings[21];
                $('#selectallstudents').html(selecttitle);
                selectall = !selectall;
            }

            /**
             *
             */
            function setskills() {
                selectedskills = [];
                $(".selected-comp").each(function() {
                    if ($(this)[0].checked) {
                        if ($(this)[0].dataset.compid !== undefined) {
                            selectedskills.push($(this)[0].dataset.compid);
                        }
                    }
                });
            }

            // Filters.
            /**
             * @param all
             */
            function resetfilters(all = true) {

                rows = [];
                ranges = [];

                $("#mod-quiz-report-competencyoverview-report-table tbody tr")
                    .not(".emptyrow")
                    .not(".gradedattempt")
                    .each(function() {
                        $(this).show(200); // Reset table view - show all rows.
                    });
            }

            $(document).on("click", "#resetfilters", function(e) {
                e.preventDefault();
                resetfilters(true);
                selectall = false;
                selectallstudents();
                actionStatus();
            });

            ranges = [];
            rows = [];

            $(document).on("click", ".selected-ranges", function(e) {
                e.preventDefault();

                resetfilters(true);

                let r = $(this).data('range');
                skillname = $(this).closest("div")[0].dataset.skill;
                ranges.push({
                    skillname: skillname,
                    range: r,
                });

                if (ranges.length != 0) {
                    $("#resetfilters").fadeIn("slow");
                    ranges.forEach(range => {
                        $("#mod-quiz-report-competencyoverview-report-table tbody tr")
                            .not(".emptyrow")
                            .not(".gradedattempt")
                            .each(function() {
                                let description = $(this)
                                    .find(`[data-skill-name='${range.skillname}']`)
                                    .data("description");
                                let skillgrade = $(this)
                                    .find(`[data-skill-name='${range.skillname}']`)
                                    .data("skill-grade");
                                if (
                                    skillgrade >= hlfilterranges[range.range][0] &&
                                    skillgrade <= hlfilterranges[range.range][1]
                                ) {

                                    if (rows[description] === undefined) {
                                        rows[description] = skillgrade;
                                    }
                                }
                            });
                    });
                    selectall = false;
                    selectallstudents();
                    setskills();

                    $(".selected-users").each(function() {

                        $("#mod-quiz-report-competencyoverview-report-table tbody tr")
                            .not(".emptyrow")
                            .not(".gradedattempt")
                            .each(function() {
                                let _name = $(this).find(`[data-skill-name='${skillname}']`).data("description");
                                if (
                                    rows.hasOwnProperty(
                                        _name
                                    )
                                ) {
                                    $(this)[0].cells[0].firstChild.checked = true;
                                }
                            });

                    });
                    actionStatus();

                } else {
                    skillname = '';
                    resetfilters(true);
                    selectall = false;
                    selectallstudents();
                    setskills();
                    actionStatus();
                }

            });

            $(document).on("click", ".questionsbycompetency", function(e) {
                e.preventDefault();

                let compid = $(this).data("compid");
                let qset = $(this).data("qset");
                let comptitle = $(this).data("comptitle");

                loading.show();

                getQuestionsByCompetencyTable(compid, cmid, quizid, currentcourseid, qset, comptitle);

            });

            /**
             * @param compid
             * @param cmid
             * @param quizid
             * @param courseid
             * @param qset
             * @param comptitle
             */
            function getQuestionsByCompetencyTable(compid, cmid, quizid, courseid, qset, comptitle) {

                Ajax.call([
                    {
                        methodname: "quiz_competencyoverview_get_questions_by_competency_table",
                        args: {
                            'compid': compid,
                            'cmid': cmid,
                            'quizid': quizid,
                            'courseid': courseid,
                            'qset': qset,
                            'lastaccess': lastaccess,
                            'actualusers': actualusers,
                        },
                        done: items => {
                            loading.remove();
                            items = JSON.parse(items.questionstable);

                            $('#modalquestions').html(items);
                            $('#questionsbycompetencymodalTitle').html(comptitle);
                            $('#questionsbycompetencymodal').modal();

                        },
                        fail: {}
                    }
                ]);
            }

            let order = false;

            $(document).on("click", "a.sortcol", function(e) {
                e.preventDefault();
                let colid = $(this).data("colid");

                sortTable(colid, order);
                order = !order;
            });

            /**
             * @param colid
             * @param order
             */
            function sortTable(colid, order) {
                let rows = $("#mod-quiz-report-competencyoverview-report-table tbody tr")
                    .not(".emptyrow")
                    .not(".gradedattempt")
                    .get();
                let A, B;
                rows.sort(function(a, b) {
                    if (colid == 0) {
                        if (order) {
                            A = $(a)
                                .children("td")
                                .eq(colid)
                                .text()
                                .toUpperCase();
                            B = $(b)
                                .children("td")
                                .eq(colid)
                                .text()
                                .toUpperCase();
                        } else {
                            A = $(b)
                                .children("td")
                                .eq(colid)
                                .text()
                                .toUpperCase();
                            B = $(a)
                                .children("td")
                                .eq(colid)
                                .text()
                                .toUpperCase();
                        }
                    } else {
                        if (order) {
                            A = $(a)
                                .children("td")
                                .eq(colid)
                                .children("div")
                                .data("skill-grade");
                            B = $(b)
                                .children("td")
                                .eq(colid)
                                .children("div")
                                .data("skill-grade");
                        } else {
                            A = $(b)
                                .children("td")
                                .eq(colid)
                                .children("div")
                                .data("skill-grade");
                            B = $(a)
                                .children("td")
                                .eq(colid)
                                .children("div")
                                .data("skill-grade");
                        }
                    }

                    if (A < B) {
                        return -1;
                    }
                    if (A > B) {
                        return 1;
                    }
                    return 0;
                });

                $.each(rows, function(index, row) {
                    $("#mod-quiz-report-competencyoverview-report-table")
                        .children("tbody")
                        .append(row);
                });
            }
            $('label.title').on('click', function() {
                $(this).closest('.header-inner').toggleClass('checked');
            });

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'top',
                selector: 'div.skill-grade-cell',
                trigger: 'focus',
                offset: '20',
                delay: {"show": 500, "hide": 100}
            });
        }
    };
});
