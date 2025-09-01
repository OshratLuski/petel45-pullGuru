import $ from 'jquery';
import * as Tables from 'quiz_advancedoverview/tables';
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import * as Charts from 'quiz_advancedoverview/charts';
import {hideLoadingIcon} from './tables';

export let anonymousmodestate = 0;
let PILLS = {};
export let CONFIG = {
    participants: new Proxy({}, {
        set(target, key, value) {
            target[key] = value;
            regenerateTable();
            return true;
        }
    }),
    anonymous_mode: 0
};

export const changeConfig = (newConfig) => {
    const participantsProxy = new Proxy(newConfig.participants, {
        set(target, key, value) {
            target[key] = value;
            regenerateTable();
            return true;
        }
    });
    newConfig.participants = participantsProxy;
    CONFIG = newConfig;
};
export const showLoadingIcon = () => {
    document.body.classList.add('petel-loading');
};

export const regenerateTable = function(callback = null) {
    const cmid = $('#datacmid').data('cmid');
    const groupid = $('#datagroupid').data('groupid');

    Ajax.call([{
        methodname: 'quiz_advancedoverview_render_dynamic_block',
        args: {
            cmid: cmid,
            groupid: groupid,
            config: JSON.stringify(CONFIG),
        },
        done: function(response) {
            const data = JSON.parse(response);
            const dataTableAccordingStudents = data.data_table_according_students;
            const dataTableStudentsSummary = data.data_table_students_summary;
            const anon = CONFIG.anonymous_mode;

            Tables.default.initstudentstable(dataTableAccordingStudents, dataTableStudentsSummary, anon);
            Charts.initcharts(data.charts);
            if (callback) {
                callback();
            }
            hideLoadingIcon();

        },
        fail: Notification.exception
    }]);
};

export const setStatesFromConfig = function() {
    const state = CONFIG.anonymous_mode;
    if (!document.getElementById('anonymousStripe')) {
        createAnonymousStripe();
    }
    if (+state == 1) {
        $('#anonymousStripe').removeClass('hidden');
        $('#anonymousStripe').attr('style', 'top:' + $('nav.navbar-petel').outerHeight() + 'px');
        let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('texton');
        $('.advancedoverview_report-toggle.anonymousmode').each(function(index) {
            $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
        });
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', true);
    } else {
        $('#anonymousStripe').addClass('hidden');
        let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('textoff');
        $('.advancedoverview_report-toggle.anonymousmode').each(function(index) {
            $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
        });
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', false);
    }
    if (CONFIG.participants.states) {
        $('#studentstableNavFilter').find(`[data-name="${CONFIG.participants.states[0]}"]`)
        .addClass('active').attr('aria-pressed', 'true');
    } else {
        $('#studentstableNavFilter').find('.nav.nav-tabs .nav-link').first().addClass('active').attr('aria-pressed', 'true');
    }

    // Set Extended view toggle.
    const fullViewState = (CONFIG.participants.full_view === 0) ? false : true;

    $('#extendedViewToggler')[0].checked = fullViewState;

};

const createAnonymousStripe = () => {
    if (!document.getElementById('anonymousStripe')) {
        const stripeContent = `
            <div class="d-flex align-items-center advancedoverview_report-toggle anonymousmode">
                <input type="checkbox" id="anonymousmodeToggler3" class="toggle-btn d-none">
                <label for="anonymousmodeToggler3" class="mb-0 anonymousmodeToggler d-none"> </label>
                <span class="link-btn-text ml-3"></span>
            </div>
            <i class="fas fa-user-secret ml-4"></i>
            `;

        const navbar = document.querySelector('nav.navbar');
        const stripeElement = document.createElement('div');
        stripeElement.classList.add('sticky-stripe', 'align-items-center', 'justify-content-center', 'yellow', 'hidden');
        stripeElement.id = 'anonymousStripe';
        stripeElement.innerHTML = stripeContent;
        navbar.insertAdjacentElement('afterbegin', stripeElement);
    }
};


const addPill = (data, target) => {
    const template = document.createElement('a');
    template.innerHTML = data.text;
    template.classList.add('pill', 'badge', 'badge-light', 'm-1', 'p-2');
    template.setAttribute('id', 'pill' + data.id);
    template.setAttribute('data-id', data.id);
    template.setAttribute('role', 'button');
    template.setAttribute('data-pilltype', data.type);

    const closeIcon = document.createElement('i');
    closeIcon.classList.add('fa', 'fa-times', 'pl-2');
    closeIcon.setAttribute('data-id', data.id);
    closeIcon.setAttribute('aria-hidden', 'true');
    template.appendChild(closeIcon);

    target[0].appendChild(template);
    $('#clearPillsArea').show();
};

const removePillByClick = (target) => {
    if (target.classList.contains('pill') || target.closest('.pill.badge')) {
        let id = target.dataset.id || target.closest('.pill.badge').dataset.id;
        let pilltype = target.dataset.pilltype || target.closest('.pill.badge').dataset.pilltype;
        removePill(id, pilltype);
    }
};

const removePill = (id, pilltype) => {
    const element = document.querySelector(`[data-id="${id}"]`);

    $('#studentsActionsCollapse input[type="checkbox"]').each((i) => {
        if ($('#studentsActionsCollapse input[type="checkbox"]')[i].id === id) {

            if (pilltype !== undefined) {
                $('#studentsActionsCollapse input[type="checkbox"]')[i].checked = true;
                $('#studentsActionsCollapse input[type="checkbox"]').eq(i).trigger('click');
            } else {
                $('#studentsActionsCollapse input[type="checkbox"]')[i].checked = false;
                element.remove();
                delete PILLS[id];
            }
            CONFIG.pills = PILLS;
        }
    });

    // Remove pill helper.
    if (pilltype === 'helper_cells') {

        element.remove();
        delete PILLS[id];

        if (Object.keys(PILLS).length > 0) {
            $('#clearPillsArea').show();
        } else {
            $('#clearPillsArea').hide();
        }

        showLoadingIcon();

        CONFIG.pills = PILLS;
        CONFIG.participants.helper_cells = [];
    }
};

const removeAllPills = (e) => {
    e.preventDefault();
    for (const key in PILLS) {
        delete PILLS[key];
    }
    $('#studentsActionsCollapse input[type="checkbox"]').each((i) => {
        $('#studentsActionsCollapse input[type="checkbox"]')[i].checked = false;
    });
    $('#pillsAreaInner').empty();
    $('#clearPillsArea').hide();


    // Clear config.
    let oldConfig = CONFIG;

    let currentConfig = {
        'states': oldConfig.participants.states,
        'full_view': oldConfig.participants.full_view,
        'show_score': oldConfig.participants.show_score
    };
    CONFIG = {
        participants: new Proxy(currentConfig, {
            set(target, key, value) {
                target[key] = value;
                regenerateTable();
                return true;
            }
        }),
        anonymous_mode: oldConfig.anonymous_mode
    };

    CONFIG.pills = PILLS;

    showLoadingIcon();
    regenerateTable();
};

export const showPills = (confObj) => {
    const pillsObj = confObj.pills;
    if (pillsObj && Object.keys(pillsObj).length > 0) {
        PILLS = confObj.pills;
        let pillsId = [];

        for (const pill in pillsObj) {
            const pillData = pillsObj[pill].pilldata;
            pillsId.push(pillData.id);
            addPill(pillData, $('#pillsAreaInner'));
        }
        if (Object.keys(PILLS).length > 0) {
            $('#clearPillsArea').show();
        } else {
            $('#clearPillsArea').hide();
        }
        pillsId.forEach(id => {
            document.querySelectorAll('#studentsActionsCollapse input[type="checkbox"]').forEach((el) => {
                if (el.id === id) {
                    el.setAttribute('checked', true);
                    el.checked = true;
                }
            });
        });

        $('#studentsActionsCollapse').collapse('show');
    }
};


$(document).on('change', '#studentsActionsCollapse input[type="checkbox"]', function(e) {

    const data = {
        text: e.target.dataset.label,
        id: e.target.id,
        type: e.target.dataset.type
    };

    if (PILLS[data.id]) {
        removePill(data.id);
    } else {
        PILLS[data.id] = {};
        PILLS[data.id].type = data.type;
        PILLS[data.id].text = data.text;
        PILLS[data.id].pilldata = {
            text: e.target.dataset.label,
            id: e.target.id,
            type: e.target.dataset.type
        };
        addPill(data, $('#pillsAreaInner'));
    }
    if (Object.keys(PILLS).length > 0) {
        $('#clearPillsArea').show();
    } else {
        $('#clearPillsArea').hide();
    }
    CONFIG.pills = PILLS;

});

$(document).on('click', '#pillsAreaInner', function(e) {
    removePillByClick(e.target);
});

$(document).on('click', '#clearPillsArea', function(e) {
    removeAllPills(e);
});

export const init = function() {
    // Add anonymous mode sticky stripe.
    createAnonymousStripe();

    // Change partisipiants filter.
    $(document).on('click', '#studentstableNavFilter .nav-link', function(e) {
        let target = $(e.currentTarget);
        let name = target.data('name');
        CONFIG.participants.states = [name];
        showLoadingIcon();
    });

    // Change scores range.
    $(document).on('change', '#studentsActionsCollapse .custom-control-input[data-type="score_ranges"]', function(e) {
        let target = $(e.currentTarget).closest('.col');
        let score_ranges = [];
        let checkboxes = target.find('input:checked');
        checkboxes.each((i) => score_ranges.push(checkboxes[i].value));
        CONFIG.participants.score_ranges = score_ranges;
        showLoadingIcon();
    });

    // Change attempts range.
    $(document).on('change', '#studentsActionsCollapse .custom-control-input[data-type="attempts_range"]', function(e) {
        let target = $(e.currentTarget).closest('.col');
        let attempts_range = [];
        let checkboxes = target.find('input:checked');
        checkboxes.each((i) => attempts_range.push(checkboxes[i].value));
        CONFIG.participants.attempts_range = attempts_range;
        showLoadingIcon();
    });

    // Click helper cells.
    setTimeout(function() {

        $(document).on('click', '#questions-table [data-action="helper-cells"]', function(e) {

            // Clear config.
            let oldConfig = CONFIG;

            let currentConfig = {
                'states': oldConfig.participants.states,
                'full_view': oldConfig.participants.full_view,
                'show_score': oldConfig.participants.show_score
            };
            CONFIG = {
                participants: new Proxy(currentConfig, {
                    set(target, key, value) {
                        target[key] = value;
                        regenerateTable();
                        return true;
                    }
                }),
                anonymous_mode: oldConfig.anonymous_mode
            };

            // Clear pills.
            for (const key in PILLS) {
                delete PILLS[key];
            }

            CONFIG.pills = PILLS;

            // Get data.
            let type = $(e.currentTarget).data('type');
            let questionid = $(e.currentTarget).data('questionid');
            let title = $(e.currentTarget).data('title');

            let helper_cells = [];
            helper_cells.push({'type': type, 'questionid': questionid});

            CONFIG.participants.helper_cells = helper_cells;
            showLoadingIcon();

            // Remove selected.
            $('#studentsActionsCollapse input[type="checkbox"]').each((i) => {
                $('#studentsActionsCollapse input[type="checkbox"]')[i].checked = false;
            });

            $('#pillsAreaInner').empty();
            $('#clearPillsArea').hide();

            // Create pill.
            const data = {
                text: title,
                id: 'helper_cells_1',
                type: 'helper_cells'
            };

            if (PILLS[data.id]) {
                removePill(data.id);
            } else {
                PILLS[data.id] = {};
                PILLS[data.id].type = data.type;
                PILLS[data.id].text = data.text;
                PILLS[data.id].pilldata = {
                    text: data.text,
                    id: data.id,
                    type: data.type
                };
                addPill(data, $('#pillsAreaInner'));
            }

            if (Object.keys(PILLS).length > 0) {
                $('#clearPillsArea').show();
            } else {
                $('#clearPillsArea').hide();
            }

            setTimeout(function() {
                document.getElementById("pillsArea").scrollIntoView();
            }, 500);

        });

    }, 3000);

    // Search.
    $(document).on('keyup', '#searchinput', function() {
        let searchValue = $(this).val().trim();
        if (searchValue.length >= 1 || searchValue === '') {
            CONFIG.participants.search = searchValue;
            showLoadingIcon();
        }
    });
    // Anonymous mode.
    $(document).on('change', '#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3', function() {
        const state = $(this).prop('checked');
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', state);
        CONFIG.anonymous_mode = state ? 1 : 0;
        showLoadingIcon();
        regenerateTable();
        setStatesFromConfig();

        //111localstorage
        localStorage.setItem('anonymousState', state ? '1' : '0');

    });

    window.addEventListener('storage', (event) => {
        if (event.key === 'anonymousState') {
            const isAnonymous = event.newValue === '1';
            updateUIForAnonymousState(isAnonymous);
        }
    });

    const updateUIForAnonymousState = (isAnonymous) => {
        $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', isAnonymous);

        CONFIG.anonymous_mode = isAnonymous ? 1 : 0;

        showLoadingIcon();
        regenerateTable();

        if (!document.getElementById('anonymousStripe')) {
            createAnonymousStripe();
        }
        if (isAnonymous == 1) {
            $('#anonymousStripe').removeClass('hidden');
            $('#anonymousStripe').attr('style', 'top:' + $('nav.navbar-petel').outerHeight() + 'px');
            let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('texton');
            $('.advancedoverview_report-toggle.anonymousmode').each(function(index) {
                $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
            });
            $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', true);
        } else {
            $('#anonymousStripe').addClass('hidden');
            let stateText = $('#anonymousmodeToggler1').closest('.advancedoverview_report-toggle').data('textoff');
            $('.advancedoverview_report-toggle.anonymousmode').each(function(index) {
                $('.advancedoverview_report-toggle.anonymousmode').eq(index).find('.link-btn-text').html(stateText);
            });
            $("#anonymousmodeToggler1, #anonymousmodeToggler2, #anonymousmodeToggler3").prop('checked', false);
        }

    };
    setStatesFromConfig();
};