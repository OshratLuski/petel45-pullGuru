define([
    'jquery',
    'core/str',
    'local_diagnostic/d3',
    'local_diagnostic/circle',
    'local_diagnostic/rectangle',
    'local_diagnostic/common-block',
    'local_diagnostic/question-list',
    'local_diagnostic/bottom-block',
    'local_diagnostic/activities',
    'core/ajax',
    'local_diagnostic/toast',
], function($, Str, d3, CircleAnimation, Rectangle, CommonBlock, QuestionList, BottomBlock, ActivitiesClass, Ajax, Toast) {
    class BuubleAnimation {
        constructor(d3, htmlRootElement, data, translateObj, popupElement, adParams, courseId, attempt, pmid, pcmid) {
            this.adParams = adParams;
            this.attempt = attempt;
            this.pmid = pmid;
            this.pcmid = pcmid;
            this.iconsColors = [
                '#707070',
                '#FA885C',
                '#FBB75D',
                '#9370DB',
                '#36B0E6',
                '#C81E4A',
                'blue'
            ];
            this.htmlRootElement = htmlRootElement;
            this.popupElement = popupElement;
            this.currentLang;
            this.courseId = courseId;
            // Ltr rtl
            if ($("html").attr("dir") === "ltr") {
                this.currentLang = false;
            } else {
                this.currentLang = true;
            }

            this.sizeCircle = 20;
            this.marginCircle = 20;
            this.backBorderColor = this.adParams[1].light;
            this.backgroundColor = this.adParams[1].light;

            this.activitiesItem;
            this.activitiesRepoItem;

            this.d3 = d3;
            this.svg;
            this.classTagG = '';
            this.startDiameter = 100;

            this.svgHeight = 0;
            this.clastersIndentStep = 40;
            this.clastersIndent = 0;
            this.clasterTopStep = 30;
            this.clasterTopStart = 206;
            this.clasterTop = this.clasterTopStart;
            this.clasterExpandStep = 4.2;

            this.isDown = false;
            this.startX = 0;
            this.startY = 0;

            this.currentId = 0;
            this.data = data.clusters;
            this.datamapper = {};
            for (const [simpleindex, clusterdata] of Object.entries(data.clusters)) {
                let clusterdataid = clusterdata.id;
                this.datamapper[clusterdataid] = simpleindex;
            }

            this.dataStart = data;
            this.totalData = data.total;
            this.totalData.cmids = data.cmids;
            this.totalData.type = data.type;
            this.totalData.questionid = data.questionid;
            this.clasterFrom = false;
            this.clasterTo = false;
            this.clasterFromSimple = false;
            this.clasterToSimple = false;

            this.clasterMaxLength = 50;

            this.tooltip;
            this.tooltipSelect;
            this.clastersSelected = {};
            this.commonBlockItem;
            this.bottomBlockItem;

            this.shapeCoord = [];
            this.shapes = [];

            this.currentUser;

            this.pointCircleX = 0;
            this.pointCircleXZero = false;

            this.documentWith = $(`${this.popupElement} .svgСharts`).width();
            this.topBlockWith = 250;
            this.topBlockRightSpace = 0;

            this.translateObj = translateObj;

            this.addIconToData(data);

            this.topBlockObj;

            this.questionListItem;

            this.mainActions();
        }

        mainActions() {
            let self = this;

            // Actions main popup
            // cancel btn
            $('body').off('click', '.popup-local-diagnostic-main-p #main_cancel');
            $('body').on('click', '.popup-local-diagnostic-main-p #main_cancel', function() {
                self.activitiesItem.clearData();
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'none');
            });

            // Submit btn
            $('body').off('click', '.popup-local-diagnostic-main-p #main_submit');
            $('body').on('click', '.popup-local-diagnostic-main-p #main_submit', async function() {
                let result = {};

                result.courseid = self.courseId;
                result.attempt = self.attempt;
                result.mid = self.pmid;
                result.sourcecmid = self.pcmid;
                result.data = [];

                let cmidsCourseHasData = false;
                let cmidsRecomHasData = false;
                let cmidsRepoHasData = false;
                let type = self.totalData.type;
                let question = self.totalData.questionid;
                const cmidsAndDesc = self.activitiesItem.getData();
                for (let key in cmidsAndDesc) {

                    let users = [];
                    self.data.forEach(function(objcluster) {
                        if (objcluster.clusternum == key) {
                            objcluster.users.forEach(function(obj) {
                                users.push(obj.id.replace('UID', ''));
                            });
                        }
                    });

                    result.data.push({
                        'cmidsCourse': cmidsAndDesc[key].cmidsCourse,
                        'cmidsRecom': cmidsAndDesc[key].cmidsRecom,
                        'cmidsRepo': cmidsAndDesc[key].cmidsRepo,
                        'description': cmidsAndDesc[key].description,
                        'recommend': cmidsAndDesc[key].recommend,
                        'clusternum': key,
                        'userids': users,
                        'type': type,
                        'question': question,
                    });

                    console.log(result.data);

                    if (cmidsAndDesc[key].cmidsCourse.length > 0) {
                        cmidsCourseHasData = true;
                    }
                    if (cmidsAndDesc[key].cmidsRecom.length > 0) {
                        cmidsRecomHasData = true;
                    }
                    if (cmidsAndDesc[key].cmidsRepo.length > 0) {
                        cmidsRepoHasData = true;
                    }
                }

                // Console.log('result ! ', JSON.stringify(result, null, 2));
                // return;

                // disable btn submit
                $('.popup-local-diagnostic-main-modal #main_submit').prop('disabled', true);

                let resultRequestCourse = true;
                let resultRequestRecom = true;
                let resultRequestRepo = true;

                if (cmidsRecomHasData) {
                    let data = $.extend(true, {}, result);
                    data.data.forEach(function(el) {
                        el.cmids = [...el.cmidsRecom];
                        delete el.cmidsCourse;
                        delete el.cmidsRepo;
                        delete el.cmidsRecom;
                    });

                    data.source = 'recommend';

                    resultRequestRecom = await self.saveActivityRequest(data, 'local_diagnostic_set_sharewith_clusters');
                    if (resultRequestCourse) {
                        self.toastMainModal(true);
                    } else {
                        self.toastMainModal(false);
                    }
                }

                if (cmidsCourseHasData) {
                    let data = $.extend(true, {}, result);
                    data.data.forEach(function(el) {
                        el.cmids = [...el.cmidsCourse];
                        delete el.cmidsCourse;
                        delete el.cmidsRepo;
                        delete el.cmidsRecom;
                    });

                    data.source = 'mycourses';

                    resultRequestCourse = await self.saveActivityRequest(data, 'local_diagnostic_set_local_clusters');
                    if (resultRequestCourse) {
                        self.toastMainModal(true);
                    } else {
                        self.toastMainModal(false);
                    }
                }

                if (cmidsRepoHasData) {
                    let data = $.extend(true, {}, result);
                    data.data.forEach(function(el) {
                        el.cmids = [...el.cmidsRepo];
                        delete el.cmidsCourse;
                        delete el.cmidsRepo;
                        delete el.cmidsRecom;
                    });

                    data.source = 'repository';

                    resultRequestRepo = await self.saveActivityRequest(data, 'local_diagnostic_set_sharewith_clusters');
                    if (resultRequestRepo) {
                        self.toastMainModal(true);
                    } else {
                        self.toastMainModal(false);
                    }
                }

                if (resultRequestCourse && resultRequestRepo && resultRequestRecom) {
                    $('.popup-local-diagnostic-main-modal button.close').trigger("click");
                }
            });

            // Close modal
            $('body').off('click', '.popup-local-diagnostic-main-p .close');
            $('body').on('click', '.popup-local-diagnostic-main-p .close', function() {
                self.activitiesItem.clearData();
                $('div.popup-local-diagnostic-main-modal .modal-footer').css('display', 'none');
            });
        }

        async saveActivityRequest(data, webServer) {
            let promise = Ajax.call([{
                methodname: webServer,
                args: data
            }]);

            let promiseResult;
            try {
                promiseResult = await promise[0];
            } catch (ex) {
                console.log(ex);
                promiseResult = false;
            }

            return promiseResult;
        }

        toastMainModal(state) {
            let toastItem = new Toast();
            if (state) {
                toastItem.toastShow('success');
            } else {
                toastItem.toastShow('error');
            }

            toastItem.toastHandle({
                method: 'hidden.bs.toast',
                fn: function() {
                    $('.popup-local-diagnostic-main-modal #main_submit').removeAttr('disabled');
                }
            });
        }

        addIconToData() {
            let data = this.data;
            for (let i = 0; i < data.length; ++i) {
                let dataUsers = data[i].users;
                if (dataUsers.length > 0) {
                    for (let ins = 0; ins < dataUsers.length; ++ins) {
                        dataUsers[ins].icon = i;
                    }
                }
            }
        }

        classes(root) {
            let classes = [];
            let sizeCircle = this.sizeCircle;
            let item = 0;

            /**
             * @param node
             */
            function recurse(node) {
                if (node.users) {
 node.users.forEach(function(child) {
                    recurse(child);
                });
} else {
 classes.push({
                    value: sizeCircle,
                    icon: node.icon,
                    id: node.id,
                    fullname: node.fullname,
                    item: item++
                });
}
            }

            recurse(root);
            return {children: classes};
        }

        nullify() {
            this.d3.select("svg").remove();
            this.d3.select(".svgСharts").html("");
            this.svgHeight = 0;
            this.clastersIndent = 0;
            this.clasterTop = this.clasterTopStart;
        }

        async start() {
            this.nullify();
            this.createSvg();
            this.createTooltip();

            let paramShape = {
                currentLang: this.currentLang,
                iconsColors: this.iconsColors,
                svg: this.svg,
                d3: this.d3,
                tooltip: this.tooltip,
                classes: this.classes,
                sizeCircle: this.sizeCircle,
                htmlRootElement: this.htmlRootElement,
                backgroundColor: this.backgroundColor,
                backBorderColor: this.backBorderColor,
                marginCircle: this.marginCircle,
                documentWith: this.documentWith,
                topBlockWith: this.topBlockWith,
                topBlockRightSpace: this.topBlockRightSpace,
                clastersIndent: this.clastersIndent,
                clasterTop: this.clasterTop,
            };

            for (let i = 0; i < this.data.length; ++i) {
                if (i === 0) {
                    let rectItem = new Rectangle(paramShape, this.data[i], this.translateObj);
                    rectItem.addRectangle();
                    this.shapes.push(rectItem);
                } else {

                    paramShape.diameter = this.getDiameter(this.data[i]);
                    this.setSvgHeight(paramShape.diameter);

                    paramShape.lcchosen = '';

                    if (this.data[i].lcchosen) {
                        paramShape.lcchosen = await Str.get_string('selected_previously', 'local_diagnostic', this.data[i].lcchosen);
                    }

                    paramShape.lcdescription = this.data[i].lcdescription;

                    let circlItem = new CircleAnimation(paramShape, this.data[i], this.translateObj);
                    circlItem.addCircle();
                    this.shapes.push(circlItem);

                    this.setCircleTop();
                    this.indentBetweenCircle(paramShape.diameter);

                    paramShape.clastersIndent = this.clastersIndent;
                    paramShape.clasterTop = this.clasterTop;
                }
            }

            this.createTopBlock();
            this.circlesCenter();

            this.createTooltipSelect();

            this.tooltipHandle();
            this.tooltipSelectHandle();
            this.addBottomBlock();

            this.drag();

            this.questionListItem = QuestionList(this.dataStart, this.popupElement, this.adParams);
            this.questionListItem.addAction(this);

            this.initActivitiesDialog();
            // This.initActivitiesRepo();
        }

        initActivitiesDialog() {
            const params = {
                htmlRootElement: this.htmlRootElement,
                currentLang: this.currentLang,
                wwwRoot: this.adParams[0],
                data: this.data
            };

            this.activitiesItem = new ActivitiesClass(
                params,
                this.translateObj,
                this.shapes,
                CircleAnimation,
                this.courseId
            );
        }

        createTopBlock() {
            let params = {
                svg: this.svg,
                d3: this.d3,
                currentLang: this.currentLang,
                tooltip: this.tooltip,
                backgroundColor: this.backgroundColor,
                backBorderColor: 'grey',
                documentWith: this.documentWith,
                topBlockWith: this.topBlockWith,
                topBlockRightSpace: this.topBlockRightSpace,
            };

            let block = new CommonBlock(params, this.totalData, this.translateObj);
            this.commonBlockItem = block;

            let resolution = this.resolutionCheck();

            if (resolution === 1) {
                block.addBlock(this.documentWith - this.topBlockWith - this.topBlockRightSpace, 54);
            } else if (resolution === 3) {
                block.addBlock(this.documentWith - this.topBlockWith - this.topBlockRightSpace - 300, 0);
            } else {
                block.addBlock();
            }

            this.topBlockObj = block;
        }

        circlesCenter() {
            let documentWith = this.documentWith;
            if (documentWith &&
                this.clastersIndent &&
                documentWith > this.clastersIndent) {
                let pointCircleX = documentWith / 2 - this.clastersIndent / 2;

                for (let i = 0; i < this.shapes.length; i++) {
                    if (this.shapes[i] instanceof CircleAnimation) {
                        if (this.pointCircleX === 0) {
                            this.pointCircleX = pointCircleX;
                        }
                        let clasterLeft = this.shapes[i].p_.clastersIndent + pointCircleX;
                        let clasterTop = this.shapes[i].p_.clasterTop;
                        this.shapes[i].p_.clastersIndent = clasterLeft;

                        let claster = this.d3.select(`g.claster-${this.shapes[i].data.id}`);

                        claster
                            .attr("transform", "translate(" + clasterLeft + "," + clasterTop + ")");
                    }
                }

            } else {
                this.pointCircleX = 0;
            }
        }

        drag() {
            let self = this;
            let allSvg = this.svg.selectAll("foreignObject.image");

            allSvg.on("mousedown", function(d) {
                let currentClaster = self.d3.select(this.parentNode.parentNode).attr("data-id");

                let currentUser = {...d.data, claster: currentClaster};
                self.currentUser = currentUser;
                allSvg.attr("data-select", "");

                self.d3.select(this).attr("data-select", "select");
            });

            allSvg.call(this.d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended)
            );

            /**
             * @param d
             */
            function dragstarted(d) {
                self.d3.event.sourceEvent.stopPropagation();
                let hmParentNode = this.parentNode;
                self.isDown = true;

                self.d3.select(hmParentNode).raise().classed("active", true);
                self.d3.select(hmParentNode.parentNode).raise().classed("active", true);

                let current = self.d3.select(this);
                this.deltaX = current.attr("x") - self.d3.event.x;
                this.deltaY = current.attr("y") - self.d3.event.y;

                self.startX = current.attr("x");
                self.startY = current.attr("y");
            }

            /**
             * @param d
             */
            function dragged(d) {
                self.d3.select(this)
                    .attr("x", self.d3.event.x + this.deltaX)
                    .attr("y", self.d3.event.y + this.deltaY);

                self.tooltip.style("display", "none");
            }

            /**
             * @param d
             */
            async function dragended(d) {
                let thisElement = self.d3.select(this);
                self.isDown = false;
                let hmParentNode = this.parentNode;

                self.d3.select(hmParentNode).classed("active", false);
                self.d3.select(hmParentNode.parentNode).classed("active", false);

                self.clasterFrom = thisElement.attr("data-claster");
                self.clasterFromSimple = self.datamapper[self.clasterFrom];
                self.currentId = thisElement.attr("data-id");

                thisElement.attr("style", "display: none");

                let overEl = self.d3.select(document.elementFromPoint(self.d3.event.sourceEvent.clientX, self.d3.event.sourceEvent.clientY));
                self.clasterTo = overEl.attr("data-claster");
                self.clasterToSimple = self.datamapper[self.clasterTo];
                self.d3.select(this).attr("style", "display: initial;color:" + self.iconsColors[d.data.icon]);

                if (self.clasterFrom &&
                    self.clasterTo &&
                    self.clasterFrom !== self.clasterTo &&
                    self.data[self.clasterToSimple].users.length < self.clasterMaxLength) {

                    // Get current user
                    const userObj =
                        self.data[self.clasterFromSimple].users.filter(obj => {
                            return obj.id.toString() === self.currentId.toString();
                        })[0];

                    // Delete user from old claster
                    self.data[self.clasterFromSimple].users =
                        self.data[self.clasterFromSimple].users.filter(obj => {
                            return obj.id.toString() !== self.currentId.toString();
                        });

                    let userId = userObj.id;

                    // Delete user id
                    // delete userObj.id

                    // create new user id
                    // userObj.id = (new Date().getTime()).toString();

                    // add user to new claster
                    self.data[self.clasterToSimple].users.push(userObj);

                    // Redraw
                    self.redraw();

                    // Ajax request
                    let promise = Ajax.call([{
                        methodname: 'local_diagnostic_user_dragdrop',
                        args: {
                            userid: userId.replace('UID', ''),
                            cmid: self.pcmid,
                            clusternumfrom: self.clasterFrom,
                            clusternumto: self.clasterTo
                        }
                    }]);

                    try {
                        let result = await promise[0];
                        console.log('result 11 ', result);
                    } catch (ex) {
                        console.log(ex);
                    }

                } else {
                    self.tooltip.style("display", "initial");

                    // Set old coordinates
                    self.d3.select(this).attr('x', self.startX);
                    self.d3.select(this).attr('y', self.startY);
                }
            }
        }

        getDiameter(data) {
            let diameter;
            if (data.users.length < 5) {
                diameter = this.startDiameter;
            } else {
                diameter = data.users.length * this.clasterExpandStep + this.startDiameter;
            }

            return diameter;
        }

        setSvgHeight(diameter) {
            if (diameter + 100 + this.clasterTop > this.svgHeight) {
                this.svgHeight = diameter + 100 + this.clasterTop;
                this.svg.attr("height", this.svgHeight);
            }
        }

        setCircleTop() {
            if (this.clasterTop > this.clasterTopStart) {
                this.clasterTop = this.clasterTop - this.clasterTopStep;
            } else {
                this.clasterTop = this.clasterTop + this.clasterTopStep;
            }
        }

        indentBetweenCircle(diameter) {
            if (this.resolutionCheck() === 2) {
                this.clastersIndent = this.clastersIndent + diameter + 20;
            } else {
                this.clastersIndent = this.clastersIndent + diameter + this.clastersIndentStep;
            }
        }

        createSvg() {
            let self = this;
            this.svg = this.d3.select(this.htmlRootElement)
                .append("svg")
                .attr("transform", this.currentLang ? "scale(-1, 1)" : "")
                .attr("width", "100%")
                .on("mousedown", function(d) {
                    let allSvg = self.svg.selectAll("foreignObject.image");
                    allSvg.attr("data-select", "");
                });
        }

        createTooltip() {
            this.tooltip = this.d3.select(this.htmlRootElement)
                .append("div")
                .attr("class", "svgСharts-tooltip")
                .text("");
        }

        createTooltipSelect() {

            this.tooltipSelect = this.d3.select(this.htmlRootElement)
                .append("div")
                .attr("class", "svgСharts-tooltip-select")
                .attr("style", "background-color:" + this.adParams[1].primary + "; border: 1px solid " + this.adParams[1].secondary + ";")
                .html(`
                        <div class="activity-course btn btn-primary" style="display:block;" data-id="0">${this.translateObj.shareactivitycourse}</div>
                        <div class="activity-repo btn btn-primary" style="display:block;" data-id="1">${this.translateObj.shareactivityrepo}</div>
                        <div class="activity-recom btn btn-primary hidden" style="display:block;" data-id="2">${this.translateObj.shareactivityrecom}</div>
                `);
        }

        redraw() {
            let shapeFrom = this.shapes[this.clasterFromSimple];
            let shapeTo = this.shapes[this.clasterToSimple];

            this.d3.select(`g.claster-${this.clasterTo}`).html('');
            this.d3.select(`g.claster-${this.clasterFrom}`).html('');

            this.changeDiameter(shapeFrom);
            this.changeDiameter(shapeTo);

            shapeTo.recreate();
            shapeFrom.recreate();

            this.tooltipHandle();
            this.tooltipSelectHandle();
            this.drag();

            this.changePositionAllCircles();

            this.bottomBlockItem.changePosition(this.commonBlockItem.p_.x, this.svgHeight - 50);
            document.getElementsByClassName('svgСharts-tooltip')[0].style.display = 'initial';
            document.getElementsByClassName('svgСharts-tooltip')[0].style.visibility = 'hidden';
        }

        changeDiameter(obj) {
            if (obj instanceof CircleAnimation) {
                let circle = this.d3.select(`circle.circle-${obj.data.id}`);
                let diameter = this.getDiameter(obj.data);

                circle
                    .attr("r", diameter / 2 - 2)
                    .attr("cx", diameter / 2)
                    .attr("cy", diameter / 2);

                obj.p_.diameter = diameter;

                this.setSvgHeight(diameter);
            }
        }

        changePositionCirclesZero(zero) {
            this.pointCircleXZero = zero ? true : false;
        }

        changePositionAllCircles() {
            if (this.pointCircleXZero) {
                this.clastersIndent = 10;
            } else {
                this.clastersIndent = this.pointCircleX;
            }
            this.clasterTop = this.clasterTopStart;
            for (let i = 0; i < this.shapes.length; i++) {
                if (this.shapes[i] instanceof CircleAnimation) {
                    this.shapes[i].clastersIndent = this.clastersIndent;

                    let claster = this.d3.select(`g.claster-${this.shapes[i].data.id}`);
                    claster
                        .attr("transform", "translate(" + this.clastersIndent + "," + this.clasterTop + ")");

                    this.setCircleTop();
                    this.indentBetweenCircle(this.shapes[i].p_.diameter);
                }
            }
        }

        tooltipHandle() {
            let self = this;
            this.d3.selectAll('.svgСharts .bottom-block .tooltip-block')
                .on("mouseover", function() {
                    let selectThis = self.d3.select(this);
                    let x = selectThis.attr("data-x");
                    let y = selectThis.attr("data-y");
                    let text = selectThis.attr("data-text");
                    let dataClass = selectThis.attr("data-class");
                    let dataPosition = selectThis.attr("data-position");

                    self.tooltip.style("visibility", "visible");
                    self.tooltip.html(text);
                    self.tooltip.attr("class", `svgСharts-tooltip ${dataClass}`);
                    let tooltipParams = self.tooltip.node().getBoundingClientRect();
                    self.tooltip.style("left", (event.pageX - $(self.htmlRootElement).offset().left - tooltipParams.width / 2) + "px");

                    if (dataPosition) {
                        self.tooltip.style("top", (event.pageY - $(self.htmlRootElement).offset().top + 25) + "px");
                    } else {
                        self.tooltip.style("top", (event.pageY - $(self.htmlRootElement).offset().top - tooltipParams.height - 25) + "px");
                    }
                })
                .on("mouseout", function() {
                    self.tooltip.style("visibility", "hidden");
                    self.tooltip.attr("class", "svgСharts-tooltip");
                });
        }

        tooltipSelectHandle() {
            let self = this;
            this.d3.selectAll('.svgСharts .bottom-block .circle-first-button')
                .on("click", function() {
                    let thisEl = $(this);
                    // Console.log(thisEl.offset().left, $(self.popupElement).offset().left);
                    let claster = thisEl.attr("data-claster");
                    let activityRecomBtn = self.tooltipSelect.select('div.activity-recom');
                    if (self.data[claster] && self.data[claster].recommend) {
                        activityRecomBtn.style("display", "block");
                    } else {
                        activityRecomBtn.style("display", "none");
                    }
                    self.tooltipSelect.style("top", (thisEl.offset().top - $(self.popupElement).offset().top - 80) + "px");
                    self.tooltipSelect.style("left", (thisEl.offset().left - $(self.popupElement).offset().left - 20) + "px");
                    self.tooltipSelect.attr("data-claster", claster);
                    self.tooltipSelect.style("visibility", "visible");
                });
            $(self.popupElement).off("click");
            $(self.popupElement).on("click", function(event) {
                if (!$(event.target).attr('class') || !$(event.target).attr('class').includes("cfb-activity")) {
                    self.tooltipSelect.style("visibility", "hidden");
                    self.tooltipSelect.attr("data-claster", "");
                }
            });
        }

        addBottomBlock() {
            this.setSvgHeight(this.svgHeight - this.clasterTop);

            let positionCommonBlock = this.commonBlockItem.getPosition();

            let params = {
                left: positionCommonBlock.x,
                svgHeight: this.svgHeight,
                topBlockWidth: this.topBlockWith,
                currentLang: this.currentLang,
                svg: this.svg
            };
            this.bottomBlockItem = new BottomBlock(params, this.translateObj);
            this.bottomBlockItem.addBlock();
        }

        resolutionCheck() {
            let windowWidth = $(window).width();
            // Console.log(windowWidth, "===========<======");

            if (windowWidth >= 1000 && windowWidth < 1200) {
                return 1;
            }
            if (windowWidth >= 1200 && windowWidth < 1400) {
                return 2;
            }
            if (windowWidth >= 1400) {
                return 3;
            }

            return 0;
        }

    }

    return function(htmlRootElement, data, popupElement, adParams, courseId, attempt, pmid, pcmid) {
        Str.get_strings([
            {
                key: 'commontitle',
                component: 'local_diagnostic'
            }, {
                key: 'rectangletitle',
                component: 'local_diagnostic'
            }, {
                key: 'userlisttitle',
                component: 'local_diagnostic'
            }, {
                key: 'share',
                component: 'local_diagnostic'
            },
            {
                key: 'shareactivitycourse',
                component: 'local_diagnostic'
            },
            {
                key: 'shareactivityrepo',
                component: 'local_diagnostic'
            },
            {
                key: 'export_excel',
                component: 'local_diagnostic'
            },
            {
                key: 'circle_empty',
                component: 'local_diagnostic'
            },
            {
                key: 'share_activities_course_title',
                component: 'local_diagnostic'
            },
            {
                key: 'share_activities_course_title_all',
                component: 'local_diagnostic'
            },
            {
                key: 'all_clusters',
                component: 'local_diagnostic'
            },
            {
                key: 'shareactivityrecom',
                component: 'local_diagnostic'
            },
        ]).then(function(arr) {
            let translate = {
                commontitle: arr[0],
                rectangletitle: arr[1],
                userlisttitle: arr[2],
                share: arr[3],
                shareactivitycourse: arr[4],
                shareactivityrepo: arr[5],
                export_excel: arr[6],
                circle_empty: arr[7],
                share_activities_course_title: arr[8],
                share_activities_course_title_all: arr[9],
                all_clusters: arr[10],
                shareactivityrecom: arr[11],
            };
            let buubleItem = new BuubleAnimation(d3, htmlRootElement, data, translate, popupElement, adParams, courseId, attempt, pmid, pcmid);
            buubleItem.start();
        });
    };
});
