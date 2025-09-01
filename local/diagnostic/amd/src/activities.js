define([
    'jquery',
    'core/templates',
    'core/str',
    'core/ajax',
    'core/notification',
], function ($, Templates, Str, Ajax, Notification) {
    return class Activities {
        constructor(p_, translateObj, shapesItems, CircleAnimation, courseId) {
            this.p_ = {...p_};
            this.translateObj = translateObj;
            this.modal;
            this.currentCluster;
            this.data = {};
            this.shapesItems = shapesItems;
            this.CircleAnimation = CircleAnimation;
            this.courseId = courseId;
            this.clustersIds = [];
            this.init();
        }

        init() {
            let self = this;
            for (let i = 1; i < this.shapesItems.length; i++) {
                let obj = this.shapesItems[i];
                if (obj && obj.data && obj.data.id) {
                    self.clustersIds.push(obj.data.id);
                }
            }

            this.actions();
        }

        getData() {
            console.log('data ', this.data);
            return this.data;
        }

        isDataEmpty() {
            let data = this.data;
            let result = true;
            if (Object.keys(data).length !== 0) {
                for (let key in data) {
                    if (data[key]['cmidsRepo'].length !== 0) {
                        result = false;
                    }
                    if (data[key]['cmidsCourse'].length !== 0) {
                        result = false;
                    }
                    if (data[key]['cmidsRecom'].length !== 0) {
                        result = false;
                    }
                }
            } else {
                result = true;
            }

            return result;
        }

        checkParrentCheckbox() {
            let allParrentCheckbox = $('div.local-diagnostic-base-block #local_diagnostic_activities_modal ul input.parent[type="checkbox"]');
            allParrentCheckbox.each(function () {
                const parentUl = $(this).closest('ul');

                let allChecked = true;
                parentUl.find('input.child').each(function () {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                    }
                });

                if (allChecked) {
                    parentUl.find('input.parent').prop('checked', true);
                } else {
                    parentUl.find('input.parent').prop('checked', false);
                }
            });
        }

        async circleSelectAll() {
            for (let i = 0; i < this.clustersIds.length; i++) {
                let key = this.clustersIds[i];
                let cmidsArr = this.data[key]['cmidsCourse'];
                let repoArr = this.data[key]['cmidsRepo'];
                let recomArr = this.data[key]['cmidsRecom'];
                let description = this.data[key]['description'];

                let translateCmids = await Str.get_string('selected_from_my_courses', 'local_diagnostic', cmidsArr.length);
                let translateRecom = await Str.get_string('selected_from_recom', 'local_diagnostic', cmidsArr.length);
                let translateRepo = await Str.get_string('selected_from_repository', 'local_diagnostic', repoArr.length);

                let borderColor = "#FFFFFF";
                if (cmidsArr.length > 0 || repoArr.length > 0) {
                    borderColor = "#4B7F3E";
                    $(`.popup-local-diagnostic g.claster-${key} rect`).remove();
                }

                $(`.popup-local-diagnostic g.claster-${key} circle`).css("stroke", borderColor);

                if (cmidsArr.length > 0) {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster`).css('display', 'block');
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster`).html(translateCmids);
                } else {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster`).css('display', 'none');
                }

                if (repoArr.length > 0) {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).css('display', 'block');
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).html(translateRepo);
                } else {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).css('display', 'none');
                }

                if (recomArr.length > 0) {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).css('display', 'block');
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).html(translateRecom);
                } else {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .text-under-claster-repo`).css('display', 'none');
                }

                if (description || description === '') {
                    $(`.popup-local-diagnostic g.claster-${key} .bottom-block .description-cluster`).html(description);
                }

                if (this.shapesItems[key].p_) {
                    this.shapesItems[key].p_.backBorderColor = borderColor;

                    if (cmidsArr.length > 0) {
                        this.shapesItems[key].p_.activities = translateCmids;
                    }
                    if (repoArr.length > 0) {
                        this.shapesItems[key].p_.activitiesRepo = translateRepo;
                    }
                    if (recomArr.length > 0) {
                        this.shapesItems[key].p_.activitiesRecom = translateRecom;
                    }
                    if (description || description === '') {
                        this.shapesItems[key].p_.description = description;
                    }
                }
            }
        }

        async circleSelect(activitiesArr, repoArr, recomArr, description) {
            let translateCourse, translateRepo, translateRecom;
            if (activitiesArr.length > 0) {
                translateCourse = await Str.get_string('selected_from_my_courses', 'local_diagnostic', activitiesArr.length);
            } else {
                translateCourse = '';
            }
            if (repoArr.length > 0) {
                translateRepo = await Str.get_string('selected_from_repository', 'local_diagnostic', repoArr.length);
            } else {
                translateRepo = '';
            }
            if (recomArr.length > 0) {
                translateRecom = await Str.get_string('selected_from_recom', 'local_diagnostic', recomArr.length);
            } else {
                translateRecom = '';
            }
            let borderColor = "#FFFFFF";
            if (activitiesArr.length > 0 || repoArr.length > 0 || recomArr.length > 0) {
                borderColor = "#4B7F3E";
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} rect`).remove();
            }
            $(`.popup-local-diagnostic g.claster-${this.currentCluster} circle`).css("stroke", borderColor);

            if (activitiesArr.length > 0) {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster`).css('display', 'block');
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster`).html(translateCourse);
            } else {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster`).css('display', 'none');
            }

            if (repoArr.length > 0) {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-repo`).css('display', 'block');
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-repo`).html(translateRepo);
            } else {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-repo`).css('display', 'none');
            }

            if (recomArr.length > 0) {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-recom`).css('display', 'block');
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-recom`).html(translateRecom);
            } else {
                $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .text-under-claster-recom`).css('display', 'none');
            }

            $(`.popup-local-diagnostic g.claster-${this.currentCluster} .bottom-block .description-cluster`).html(description);

            let shapesItem = null;
            this.shapesItems.forEach(function(objshape) {
                if (objshape.clusternum === self.currentCluster) {
                    shapesItem = objshape;
                }
            });


            if (shapesItem.p_) {
                shapesItem.p_.backBorderColor = borderColor;
                shapesItem.p_.activities = translateCourse;
                shapesItem.p_.activitiesRepo = translateRepo;
                shapesItem.p_.activitiesRecom = translateRecom;
                shapesItem.p_.description = description;
            }
        }

        async clearData() {
            let self = this;

            self.data = {};

            for (let i = 0; i < self.shapesItems.length; i++) {
                self.currentCluster = i;
                await self.circleSelect([], [], '');
            }
        }

        changeDataAll(cmidsCourse = false, cmidsRepo = false, cmidsRecom = false, description = false, recommend = false) {
            let self = this;

            for (let i = 0; i < self.clustersIds.length; i++) {
                let key = self.clustersIds[i];

                if (key in self.data) {
                    if (cmidsCourse) {
                        self.data[key]['cmidsCourse'] = cmidsCourse;
                    }
                    if (cmidsRepo) {
                        self.data[key]['cmidsRepo'] = cmidsRepo;
                    }
                    if (cmidsRecom) {
                        self.data[key]['cmidsRecom'] = cmidsRecom;
                    }
                    if (description || description === '') {
                        self.data[key]['description'] = description;
                    }
                    self.data[key]['recommend'] = recommend;
                } else {
                    self.data[key] = {
                        cmidsCourse: cmidsCourse ? cmidsCourse : [],
                        cmidsRepo: cmidsRepo ? cmidsRepo : [],
                        cmidsRecom: cmidsRecom ? cmidsRecom : [],
                        description: description ? description : '',
                        recommend: recommend,
                    }
                }
            }

            self.circleSelectAll();

            if (self.isDataEmpty()) {
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'none');
            } else {
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'flex');
            }
        }

        changeData(cmidsCourse = false, cmidsRepo = false, cmidsRecom = false, description = false, recommend = false) {
            let self = this;

            if (self.currentCluster in self.data) {
                if (cmidsCourse) {
                    self.data[self.currentCluster]['cmidsCourse'] = cmidsCourse;
                }
                if (cmidsRepo) {
                    self.data[self.currentCluster]['cmidsRepo'] = cmidsRepo;
                }
                if (cmidsRecom) {
                    self.data[self.currentCluster]['cmidsRecom'] = cmidsRecom;
                }
                if (description === '' || description) {
                    self.data[self.currentCluster]['description'] = description;
                }
                self.data[self.currentCluster]['recommend'] = recommend;
            } else {
                self.data[self.currentCluster] = {
                    cmidsCourse: cmidsCourse ? cmidsCourse : [],
                    cmidsRepo: cmidsRepo ? cmidsRepo : [],
                    cmidsRecom: cmidsRecom ? cmidsRecom : [],
                    description: description ? description : '',
                    recommend: recommend,
                };
            }

            let cmidsReroArr = self.data[self.currentCluster]['cmidsRepo'];
            let cmidsCourseArr = self.data[self.currentCluster]['cmidsCourse'];
            let cmidsRecomArr = self.data[self.currentCluster]['cmidsRecom'];

            self.circleSelect(
                cmidsCourseArr,
                cmidsReroArr,
                cmidsRecomArr,
                self.data[self.currentCluster]['description']
            );

            if (self.isDataEmpty()) {
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'none');
            } else {
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'flex');
            }
        }

        async actions() {
            const self = this;
            const p_ = self.p_;

            // get checkboxes text
            var promise = Ajax.call([{
                methodname: 'local_diagnostic_get_all_course_quizzes',
                args: {
                    courseid: self.courseId
                }
            }]);

            const checkboxesText = await promise[0].done(function (data) {
                return data;
            }).fail(function (ex) {
                console.log(ex);
                return false;
            });
            ;

            // open popup
            const openModalEl = $(`${p_.htmlRootElement} .svgСharts-tooltip-select div.activity-course`);
            openModalEl.off("click");
            openModalEl.on("click", async function () {
                let parentEl = $(this).closest('.svgСharts-tooltip-select')
                self.currentCluster = parentEl.attr('data-claster');

                if (self.currentCluster === 'all') {
                    actionActivityCourseAll();
                    return;
                }

                let clusterName = '';
                let clusterInfo = '';
                self.shapesItems.forEach(function(objshape) {
                    if (objshape.clusternum === self.currentCluster) {
                        clusterName = objshape['data']['clustername'];
                        clusterInfo = objshape['data']['info'];
                    }
                });

                Templates.render('local_diagnostic/activities-body', {
                    title: self.translateObj['share_activities_course_title'],
                    data: checkboxesText ? checkboxesText : '',
                    clusterName,
                })
                    .done(function (html, js) {
                        Templates.replaceNodeContents('#local_diagnostic_activities_modal .modal-body', html, js);

                        var zindex = $(".popup-local-diagnostic-main-modal").css("z-index");
                        zindex = zindex + 1;

                        $("#local_diagnostic_activities_modal").css("z-index", zindex);

                        $('#local_diagnostic_activities_modal').modal('show');
                    })
                    .fail(Notification.exception);

                // clear description
                $('#local_diagnostic_description_modal textarea').val('');
                $('#local_diagnostic_description_modal div.description-info').html(clusterInfo);
                $('#local_diagnostic_description_modal div.modal-body-description h3 b').html(clusterName);
                // checkboxes check
                if (!self.data || !self.data[self.currentCluster] || !self.data[self.currentCluster]['cmidsCourse']) {
                    return;
                }

                self.data[self.currentCluster]['cmidsCourse'].forEach((el) => {
                    $(`div.local-diagnostic-base-block #local_diagnostic_activities_modal ul input[data-id=${el}]`).prop('checked', true);
                });

                self.checkParrentCheckbox();

                // restore description
                if (self.data[self.currentCluster]['description']) {
                    $('#local_diagnostic_description_modal textarea').val(self.data[self.currentCluster]['description']);
                }

                if (self.data[self.currentCluster]['recommend']) {
                    $('#local_diagnostic_description_modal .modal-body-recommend input[type="checkbox"]').val(self.data[self.currentCluster]['recommend']);
                }
            });

            /* checkbox logic */
            $('body').off('click', 'div.popup-local-diagnostic .activities-diagnostic .parent');
            $('body').on('click', 'div.popup-local-diagnostic .activities-diagnostic .parent', function () {
                const parentUl = $(this).closest('ul');
                if ($(this).is(':checked')) {
                    parentUl.find('input').prop('checked', true);
                } else {
                    parentUl.find('input').prop('checked', false);
                }
            });

            $('body').off('click', 'div.popup-local-diagnostic .activities-diagnostic input.child');
            $('body').on('click', 'div.popup-local-diagnostic .activities-diagnostic input.child', function () {
                const parentUl = $(this).closest('ul');

                let allChecked = true;
                parentUl.find('input.child').each(function () {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                    }
                });

                if (allChecked) {
                    parentUl.find('input.parent').prop('checked', true);
                } else {
                    parentUl.find('input.parent').prop('checked', false);
                }
            });
            /* checkbox logic end */

            // submit activities, show description popup
            $('body').off('click', '#local_diagnostic_activities_modal #submit');
            $('body').on('click', '#local_diagnostic_activities_modal #submit', async function () {

                // Calculate zindex.
                var zindex = $("#local_diagnostic_activities_modal").css("z-index");
                zindex = zindex + 1;
                $("#local_diagnostic_description_modal").css("z-index", zindex);

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'hidden');
                $('#local_diagnostic_description_modal').modal('show');
            });

            // submit description
            $('body').off('click', '#local_diagnostic_description_modal #description_submit');
            $('body').on('click', '#local_diagnostic_description_modal #description_submit', function () {
                const checkboxes = $('div.popup-local-diagnostic .activities-diagnostic ul input.child[type="checkbox"]');

                let cmidsArr = [];
                checkboxes.each(function () {
                    let this_ = $(this);
                    let id = this_.attr('data-id');
                    if (this_.is(':checked')) {
                        cmidsArr.push(id);
                    }
                });

                const description = $('#local_diagnostic_description_modal textarea').val();
                const recommend = $('#local_diagnostic_description_modal input.recommend').is(':checked');

                if (self.currentCluster === 'all') {
                    self.changeDataAll(cmidsArr, false, false, description, recommend);
                } else {
                    self.changeData(cmidsArr, false, false, description, recommend);
                }

                $('#local_diagnostic_description_modal').modal('hide');
                $('#local_diagnostic_activities_modal').modal('hide');

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            $('body').off('click', '#local_diagnostic_description_modal .description-close');
            $('body').on('click', '#local_diagnostic_description_modal .description-close', function () {
                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            const openRecomModalTraverse = $(`${p_.htmlRootElement} .svgСharts-tooltip-select div.activity-recom`);
            openRecomModalTraverse.off("click");
            openRecomModalTraverse.on("click", async function () {
                let parentEl = $(this).closest('.svgСharts-tooltip-select');
                self.currentCluster = parentEl.attr('data-claster');
                if (self.currentCluster === 'all') {
                    actionActivityCourseAll();
                    return;
                }

                let clusterName = '';
                if (self.shapesItems[self.currentCluster] &&
                    self.shapesItems[self.currentCluster]['data'] &&
                    self.shapesItems[self.currentCluster]['data']['clustername']) {
                    clusterName = self.shapesItems[self.currentCluster]['data']['clustername'];
                }

                if (self.shapesItems[self.currentCluster] &&
                    self.shapesItems[self.currentCluster]['data'] &&
                    self.shapesItems[self.currentCluster]['data']['clusternum']) {
                    clusterName = self.shapesItems[self.currentCluster]['data']['clusternum'];
                }

                Templates.render('local_diagnostic/recommend-body', {
                    title: self.translateObj['share_activities_recom_title'],
                    titletext: self.translateObj['share_activities_recom_titletext'],
                    data: self.p_.data[self.currentCluster].recommend ? self.p_.data[self.currentCluster].recommend : '',
                    clusterName,
                })
                    .done(function (html, js) {
                        Templates.replaceNodeContents('#local_diagnostic_recommend_modal .modal-body', html, js);

                        var zindex = $(".popup-local-diagnostic-main-modal").css("z-index");
                        zindex = zindex + 1;

                        $("#local_diagnostic_recommend_modal").css("z-index", zindex);

                        $('#local_diagnostic_recommend_modal').modal('show');
                    })
                    .fail(Notification.exception);

                // clear description
                $('#local_diagnostic_description_recom_modal textarea').val('');
                $('#local_diagnostic_description_recom_modal div.description-info').html(self.shapesItems[self.currentCluster]['data']['info']);
                $('#local_diagnostic_description_recom_modal div.modal-body-description-recom h3 b').html(clusterName);

                // checkboxes check
                if (!self.data || !self.data[self.currentCluster] || !self.data[self.currentCluster]['cmidsRecom']) {
                    return;
                }

                self.data[self.currentCluster]['cmidsRecom'].forEach((el) => {
                    $(`div.local-diagnostic-base-block #local_diagnostic_recommend_modal ul input[data-id=${el}]`).prop('checked', true);
                });

                self.checkParrentCheckbox();

                // restore description
                if (self.data[self.currentCluster]['description']) {
                    $('#local_diagnostic_description_recom_modal textarea').val(self.data[self.currentCluster]['description']);
                }

                if (self.data[self.currentCluster]['recommend']) {
                    $('#local_diagnostic_description_recom_modal .modal-body-recommend input[type="checkbox"]').val(self.data[self.currentCluster]['recommend']);
                }
            });

            /* checkbox logic */
            $('body').off('click', 'div.popup-local-diagnostic .recommend-diagnostic .selectall');
            $('body').on('click', 'div.popup-local-diagnostic .recommend-diagnostic .selectall', function () {
                const parentContainer = $(this).closest('.recommend-diagnostic');
                if ($(this).is(':checked')) {
                    parentContainer.find('input').prop('checked', true);
                } else {
                    parentContainer.find('input').prop('checked', false);
                }
            });

            $('body').off('click', 'div.popup-local-diagnostic .recommend-diagnostic input.child');
            $('body').on('click', 'div.popup-local-diagnostic .recommend-diagnostic input.child', function () {
                const parentContainer = $(this).closest('.recommend-diagnostic');

                let allChecked = true;
                parentContainer.find('input.child').each(function () {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                    }
                });

                if (allChecked) {
                    parentContainer.find('input.parent').prop('checked', true);
                } else {
                    parentContainer.find('input.parent').prop('checked', false);
                }
            });

            $('body').off('click', 'div.popup-local-diagnostic .recommend-diagnostic .toggle-descr');
            $('body').off('click', 'div.popup-local-diagnostic .recommend-diagnostic .recom-body-outer');
            $('body').on('click', 'div.popup-local-diagnostic .recommend-diagnostic .recom-body-outer', function () {
                $(this).find('.toggle-descr').toggleClass('fa-caret-down');
                $(this).find('.toggle-descr').toggleClass('fa-caret-up');
                let outer = $(this).closest('.recom-body-outer');
                outer.find('.recom-body-descr-inner').toggleClass('hidden');
                outer.toggleClass('opened');
            });
            /* checkbox logic end */

            // submit recomendations, show description popup
            $('body').off('click', '#local_diagnostic_recommend_modal #submit');
            $('body').on('click', '#local_diagnostic_recommend_modal #submit', async function () {

                // Calculate zindex.
                var zindex = $("#local_diagnostic_recommend_modal").css("z-index");
                zindex = zindex + 1;
                $("#local_diagnostic_description_recom_modal").css("z-index", zindex);

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'hidden');
                $('#local_diagnostic_description_recom_modal').modal('show');
            });

            // submit description
            $('body').off('click', '#local_diagnostic_description_recom_modal #description_submit');
            $('body').on('click', '#local_diagnostic_description_recom_modal #description_submit', function () {
                const checkboxes = $('div.popup-local-diagnostic .recommend-diagnostic input.child[type="checkbox"]');

                let cmidsRecom = [];
                checkboxes.each(function () {
                    let this_ = $(this);
                    let id = this_.attr('data-id');
                    if (this_.is(':checked')) {
                        cmidsRecom.push(id);
                    }
                });

                const description = $('#local_diagnostic_description_recom_modal textarea').val();
                const recommend = $('#local_diagnostic_description_recom_modal input.recommend').is(':checked');

                if (self.currentCluster === 'all') {
                    self.changeDataAll( false, false, cmidsRecom, description, recommend);
                } else {
                    self.changeData(false, false, cmidsRecom, description, recommend);
                }

                $('#local_diagnostic_description_recom_modal').modal('hide');
                $('#local_diagnostic_recommend_modal').modal('hide');

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            $('body').off('click', '#local_diagnostic_description_recom_modal .description-close');
            $('body').on('click', '#local_diagnostic_description_recom_modal .description-close', function () {
                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            // activities repo click
            const activityRepoModalEl = $(`${p_.htmlRootElement} .svgСharts-tooltip-select div.activity-repo`);
            activityRepoModalEl.off("click");
            activityRepoModalEl.on("click", async function () {
                // set clusterName
                let parentEl = $(this).closest('.svgСharts-tooltip-select')
                self.currentCluster = parentEl.attr('data-claster');

                let clusterName = '';
                let clusterInfo = '';
                if (self.currentCluster === 'all'){
                    clusterName = self.translateObj['all_clusters'];
                } else {
                    self.shapesItems.forEach(function(objshape) {
                        if (objshape.clusternum === self.currentCluster) {
                            clusterName = objshape['data']['clustername'];
                            clusterInfo = objshape['data']['info'];
                        }
                    });
                }

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'hidden');

                let url = `${self.p_.wwwRoot}/local/community/plugins/oer/iframe.php?plugin=activity`;

                $('#local_diagnostic_repository_modal .activities-diagnostic-title b').html(clusterName);
                $('#local_diagnostic_description_repo_modal div.description-info').html(clusterInfo);
                $('#local_diagnostic_description_repo_modal div.modal-body-description-repo h3 b').html(clusterName);
                $('#local_diagnostic_repository_modal iframe').attr('src', url);

                var zindex = $(".popup-local-diagnostic-main-modal").css("z-index");
                zindex = zindex + 1;

                $("#local_diagnostic_repository_modal").css("z-index", zindex);
                $('#local_diagnostic_repository_modal').modal('show');
            });

            // activity-repo hidden action
            $('#local_diagnostic_repository_modal').on('hidden.bs.modal', function () {
                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            // activity repo description open
            $('#local_diagnostic_repository_modal #repository_submit').off('click');
            $('#local_diagnostic_repository_modal #repository_submit').on('click', function () {

                let clusterName = '';
                let clusterInfo = '';
                let clusterDescription = '';
                let recommend = 0;
                self.shapesItems.forEach(function(objshape) {
                    if (objshape.clusternum == self.currentCluster) {
                        clusterName = objshape['data']['clustername'];
                        clusterInfo = objshape['data']['info'];
                        clusterDescription = objshape['data']['description'];
                        recommend = objshape['data']['recommend'];
                    }
                });

                // clear description
                $('#local_diagnostic_description_repo_modal textarea').val('');
                $('#local_diagnostic_description_modal div.repo-description-info').html(clusterInfo);
                // restore description
                if (clusterDescription){
                    $('#local_diagnostic_description_repo_modal textarea').val(clusterDescription);
                }

                if (recommend){
                    $('#local_diagnostic_description_repo_modal .modal-body-recommend input[type="checkbox"]').val(recommend);
                }

                // Calculate zindex.
                var zindex = $("#local_diagnostic_repository_modal").css("z-index");
                zindex = zindex + 1;
                $("#local_diagnostic_description_repo_modal").css("z-index", zindex);

                $('div.popup-local-diagnostic-main-modal').css('visibility', 'hidden');
                $('#local_diagnostic_description_repo_modal').modal('show');
            });

            // activity repo description submit
            $('#local_diagnostic_description_repo_modal #description_submit_repo').off('click');
            $('#local_diagnostic_description_repo_modal #description_submit_repo').on('click', function(){
                let iframeEl = $('#local_diagnostic_repository_modal #repository_iframe');

                let checkedEl = $('#oer_activity_items_selected', iframeEl.contents());

                let repActivitiesArr = [];
                try {
                    repActivitiesArr = JSON.parse(checkedEl.val());
                } catch (e) {
                }

                const description = $('#local_diagnostic_description_repo_modal textarea').val();
                const recommend = $('#local_diagnostic_description_repo_modal input.recommend').is(':checked');

                if (self.currentCluster === 'all'){
                    self.changeDataAll(false, repActivitiesArr, false, description, recommend);
                } else {
                    self.changeData(false, repActivitiesArr, false, description, recommend);
                }

                $('#local_diagnostic_repository_modal').modal('hide');
                $('#local_diagnostic_description_repo_modal').modal('hide');
                $('div.popup-local-diagnostic-main-modal').css('visibility', 'visible');
            });

            async function actionActivityCourseAll() {

                Templates.render('local_diagnostic/activities-body', {
                    title: self.translateObj['share_activities_course_title_all'],
                    data: checkboxesText ? checkboxesText : '',
                    clusterName: self.translateObj['all_clusters'],
                })
                    .done(function (html, js) {
                        Templates.replaceNodeContents('#local_diagnostic_activities_modal .modal-body', html, js);

                        var zindex = $(".popup-local-diagnostic-main-modal").css("z-index");
                        zindex = zindex + 1;

                        $("#local_diagnostic_activities_modal").css("z-index", zindex);

                        $('#local_diagnostic_activities_modal').modal('show');
                    })
                    .fail(Notification.exception);


                // clear description
                $('#local_diagnostic_description_modal textarea').val('');

                // checkboxes check
                for (let i = 0; i < self.clustersIds.length - 1; i++) {
                    if (!self.data || !self.data[self.clustersIds[i]] || !self.data[self.clustersIds[i]]['cmidsCourse']) {
                        return;
                    }
                }

                // same values for all clusters
                let commonArr = [];
                let description = self.data[self.clustersIds[0]]['description'];
                for (let i = 0; i < self.clustersIds.length - 1; i++) {
                    if (i === 0) {
                        commonArr = self.data[self.clustersIds[i]]['cmidsCourse'];
                    }
                    let arrTwo = self.data[self.clustersIds[i + 1]]['cmidsCourse'];

                    commonArr = commonArr.filter(x => arrTwo.indexOf(x) !== -1);

                    let descriptionOne = self.data[self.clustersIds[i]]['description'];
                    let descriptionTwo = self.data[self.clustersIds[i + 1]]['description'];

                    if (descriptionOne !== descriptionTwo) {
                        description = false;
                    }
                }

                commonArr.forEach((el) => {
                    $(`div.local-diagnostic-base-block #local_diagnostic_activities_modal ul input[data-id=${el}]`).prop('checked', true);
                });

                self.checkParrentCheckbox();

                // restore description
                if (description) {
                    $('#local_diagnostic_description_modal textarea').val(description);
                }
            }
        }
    }
});