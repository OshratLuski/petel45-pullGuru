/* eslint-disable camelcase */
/* eslint-disable require-jsdoc */
// eslint-disable-next-line require-jsdoc

define([
    'jquery',
    'core/ajax',
    'core/templates',
    'core/str',
    'core/notification',
], function($, Ajax, Templates, str, Notification,) {

    var competencies = {};
    var maxNumberOfSection = 1;

    function clearDropdown(el) {
        el.html('');

    }

    function disableDropdownBtn(el) {
        if (!el.hasClass('disabled')) {
            el.addClass('disabled');
        }
    }

    function enableDropdownBtn(el) {

        if (el.hasClass('disabled')) {
            el.removeClass('disabled');
        }
    }

    function getCompetencies(data) {
        var unique = Math.floor(Date.now() / 1000);
        data.categories.forEach(cat => {
            cat.courses.forEach(course => {
                if (course.competencies.length > 0) {
                    competencies['course_id-' + course.course_id] = course;
                    competencies['course_id-' + course.course_id].unique = unique;
                }
                if (course.sections.length > 0) {
                    course.sections.forEach(sections => {
                        if (sections.section_competency.length > 0) {
                            sections.section_competency.forEach(el => {
                                competencies['course_id-' + course.course_id] = course;
                                competencies['course_id-' + course.course_id].unique = unique;
                            });
                        }
                    });
                }
            });
        });
    }

    function showCompetenciesBtn(target, courseid, sectionid) {

        var prop = 'course_id-' + courseid;
        if (competencies.hasOwnProperty(prop)) {
            $('.select-competencies-dropdown-wrapper').removeClass('hidden').attr('data-section_id', sectionid);
            var dropdownMenu = target.find('.select-competency-dropdown-menu');
            let context;
            if (competencies[prop].competencies.length > 0) {
                context = competencies[prop];
            } else if (competencies[prop].sections.length > 0) {
                context = {};
                competencies[prop].sections.forEach(function(el) {
                    if (+el.section_id === sectionid) {
                        context.competencies = el.section_competency;
                    }
                });

            }
            Templates.render('community_sharequestion/elements/dropdown-competency-selector', context)
                .done(function(html, js) {
                    Templates.replaceNodeContents(dropdownMenu, html, js);

                    $(document).on('click', '.category-dropdown-item', (e) => {
                        e.stopPropagation();
                    });
                })
                .fail(Notification.exception);
        } else {
            target.closest('.section-competency-block').addClass('selected');

            if (maxNumberOfSection > 1 && $('.section-competency-block').length < maxNumberOfSection) {
                target.closest('.section-competency-block').find('.add-section-competency-block').show();
            }

        }
    }

    function showOercatalogHierarchy(data, dropdownMenu) {
        Templates.render('community_sharequestion/elements/dropdown-category-selector', data)
            .done(function(html, js) {
                Templates.replaceNodeContents(dropdownMenu, html, js);

                $(document).on('click', 'a.category-dropdown-item, a.course-dropdown-item', (e) => {
                    e.stopPropagation();
                });
            })
            .fail(Notification.exception);
        competencies = {};
        getCompetencies(data);
    }

    function getOercatalogHierarchy(json, dropdownMenu) {
        Ajax.call([{
            methodname: 'community_sharequestion_get_oercatalog_hierarchy',
            args: {
                selected: json
            },
            done: function(response) {
                let data = JSON.parse(response.hierarchy);
                showOercatalogHierarchy(data, dropdownMenu);
            },
            fail: Notification.exception
        }]);
    }

    function addSelectedSection(data, parent) {
        var root = parent.closest('.section-competency-block');
        var newBlockId = root.find('.selected-sections-area-wrapper .selected-section-block').length;
        var newBlock = `
        <div class="selected-section-block border border-primary rounded-pill align-items-center py-1 px-2 mb-2 mr-2"
            id="selected-section-block-${newBlockId}" data-cat_id="${data.cat_id}" data-course_id="${data.course_id}" 
            data-section_id="${data.section_id}">
            <h6 class="text-bold mr-2 mb-0 d-inline-flex">
                <span class="selected-category-name px-1" id="${data.cat_id}">${data.cat_name}</span>|<span
                    class="selected-course-name px-1" id="${data.course_id}">${data.course_name}</span>|<span
                    class="selected-section-name px-1" id="${data.section_id}">${data.section_name}</span>
            </h6>
            <button type="button px-2" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        `;
        $(newBlock).insertAfter(root.find('button.select-section-dropdown-btn'));
        disableDropdownBtn(parent);
        if (!$.isEmptyObject(competencies)) {
            var target = parent.closest('.section-competency-block').find('.select-competencies-dropdown-wrapper');
            showCompetenciesBtn(target, data.course_id, data.section_id);
        } else if (maxNumberOfSection > 1 && $('.section-competency-block').length < maxNumberOfSection) {
            root.addClass('selected');
            root.find('.add-section-competency-block').show();
        } else {
            root.addClass('selected');
        }
    }

    function addSelectedCompetency(data, parent) {

        var selectedPill = `
            <div class="selected-competency-block border border-primary rounded-pill align-items-center py-1 px-2 mb-2 mr-2"
                id="selected-competency-block-${data.comp_id}" data-comp_id="${data.comp_id}" data-section_id="${data.section_id}">
                <h6 class="text-bold mr-2 mb-0 d-inline-flex">
                    <span class="selected-competency-name px-1">${data.comp_name}</span>
                </h6>
                <button type="button px-2" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        parent.find(".selected-competencies-area-wrapper").append(selectedPill);
        parent.closest('.section-competency-block').addClass('selected');
        if (maxNumberOfSection > 1 && $('.section-competency-block').length < maxNumberOfSection) {

            parent.siblings('.add-section-competency-block').show();
        }

    }

    function removeSelectedCompetencies(target) {
        target.find('.selected-competency-block').remove();
        target.find('.select-competency-dropdown-menu').empty();
        target.addClass('hidden');
        target.siblings('.add-section-competency-block').hide();
    }

    function removeSelectedCompetencyBlock(el) {
        var comp_id = el.closest('.selected-competency-block').data('comp_id');
        if (el.closest('.section-competency-block').find('.selected-competency-block').length === 0) {
            el.closest('.section-competency-block').find('.add-section-competency-block').hide();
        }
        el.closest('.select-competencies-dropdown-wrapper')
            .find('.competency-dropdown-item[data-comp_id="' + comp_id + '"] input').trigger('click');
    }

    function removeSelectedSectionBlock(el) {
        var btn = el.siblings('.dropdown-toggle');
        var target = el.closest('.section-competency-block').find('.select-competencies-dropdown-wrapper');
        if (el.closest('.section-competency-block').siblings('.section-competency-block').length > 0) {
            var prevBlock = el.closest('.section-competency-block').prev();
            var nextBlock = el.closest('.section-competency-block').next();
            if (prevBlock.length > 0 && prevBlock.hasClass('selected')) {

                prevBlock.find('.add-section-competency-block').show();
            } else if (nextBlock.length > 0 && nextBlock.hasClass('selected')) {
                nextBlock.find('.add-section-competency-block').show();
            }
            el.closest('.section-competency-block').remove();
        } else {
            el.remove();
        }
        removeSelectedCompetencies(target);
        enableDropdownBtn(btn);
        btn.closest('.section-competency-block').find('.add-section-competency-block').hide();
    }

    function showAllCompetencies(parent) {
        parent.find('.competency-dropdown-item').show();
    }

    function competencyAutocomplete(symbol, parent) {
        parent.find('.competency-dropdown-item').each(function() {
            var el = $(this);
            var string = el.find('label').text();
            var text = symbol.toLowerCase();
            if (string.toLowerCase().indexOf(text) === -1) {
                el.hide();
            } else {
                el.show();
            }
        });
    }

    function checkCompetenciesAreaHeight(target) {
        var btnHeight = +target.find('button.dropdown-toggle').outerHeight() + 10;
        var pillsHeight = +target.find('.selected-competencies-area-wrapper').outerHeight();
        var menu = target.find('.select-competency-dropdown-menu');
        if (pillsHeight > btnHeight) {
            menu.css('margin-top', pillsHeight - btnHeight + 10);
        } else {
            menu.css('margin-top', 0);
        }
    }

    function addSectionCompetencyBlock(prevElement) {
        var data = [];
        Templates.render('community_sharequestion/elements/section-competency-block', data)
            .done(function(html, js) {
                Templates.appendNodeContents(prevElement, html, js);
            })
            .fail(Notification.exception);
    }

    return {

        init: function(uniqueid, number_sections) {

            maxNumberOfSection = +number_sections;
            var form = $('#sharing_activities_form_' + uniqueid);

            form.on('show.bs.dropdown', '.select-section-dropdown-wrapper', function(e) {
                var target = $(e.target).find('.dropdown-menu');
                clearDropdown(target);
                let selected_sections = [];
                $('.selected-section-block').each(function() {
                    if (!$(this).hasClass('hidden')) {
                        selected_sections.push($(this).data("section_id"));
                    }
                });
                let data = JSON.stringify(selected_sections);
                getOercatalogHierarchy(data, target);
            });

            form.on('click', '.selected-section-block .close', function() {
                let parent = $(this).closest('.selected-section-block');
                removeSelectedSectionBlock(parent);
            });

            form.on('click', '.selected-competency-block .close', function() {
                removeSelectedCompetencyBlock($(this));
            });

            form.on('click', '.section-dropdown-item', function(e) {
                var btn = $(e.target).closest('.dropdown-menu').siblings('.dropdown-toggle');
                const data = {
                    cat_id: $(this).data('cat_id'),
                    cat_name: $(this).data('cat_name'),
                    course_id: $(this).data('course_id'),
                    course_name: $(this).data('course_name'),
                    section_id: $(this).data('section_id'),
                    section_name: $(this).data('section_name')
                };
                addSelectedSection(data, btn);
            });
            form.on('click', '.competency-dropdown-item input', function(e) {
                var compId = $(e.target).closest('.competency-dropdown-item').data('comp_id');
                var target = $(e.target).closest('.section-competency-block').find('.select-competencies-dropdown-wrapper');
                if (target.find('#selected-competency-block-' + compId).length > 0) {
                    target.find('#selected-competency-block-' + compId).remove();
                    if (target.find('.selected-competency-block').length === 0) {
                        target.siblings('.add-section-competency-block').hide();
                    }
                } else {
                    var parent = $(e.target).closest('.select-competencies-dropdown-wrapper');
                    const data = {
                        comp_id: $(this).closest('.competency-dropdown-item').data('comp_id'),
                        comp_name: $(this).closest('.competency-dropdown-item').data('comp_name'),
                        section_id: parent.data('section_id'),
                    };
                    addSelectedCompetency(data, parent);
                }
                checkCompetenciesAreaHeight(target);
            });

            form.on('shown.bs.dropdown', '.select-competencies-dropdown-wrapper', function(e) {
                var target = $(e.target).closest('.section-competency-block');
                setTimeout(function() {
                    checkCompetenciesAreaHeight(target);
                }, 10);
            });

            form.on('click', '.select-competency-dropdown-menu', function(e) {
                e.stopPropagation();
            });

            form.on('click', '.no_copyright', function(e) {
                var parent = $(e.target).closest('.form-group');
                parent.find('.question-activity-url-wrapper').slideUp();
            });
            form.on('click', '.based_on_another_activity', function(e) {
                var parent = $(e.target).closest('.form-group');
                parent.find('.question-activity-url-wrapper').slideDown();
            });

            form.on('keyup', '#competency-auotocmplete', function(e) {
                var timer;
                if (timer) {
                    clearTimeout(timer);
                }

                var parent = $(e.target).closest('.select-competency-dropdown-menu');
                var symbol = $(e.target).val();

                timer = setTimeout(function() {
                    if (symbol === '') {
                        showAllCompetencies(parent);
                    } else {
                        competencyAutocomplete(symbol, parent);
                    }
                }, 500);
            });
            form.on('click', '.add-section-competency-btn', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var prevElement = $(e.target).closest('.section-competency-block-wrapper');
                addSectionCompetencyBlock(prevElement);
                $(e.target).closest('.add-section-competency-block').hide();
            });
        },
    };
});
