/* eslint-disable promise/always-return */
/* eslint-disable promise/catch-or-return */
/* eslint-disable no-constant-condition */
/* eslint-disable camelcase */
/* eslint-disable require-jsdoc */
// eslint-disable-next-line require-jsdoc

define([
    'jquery',
    'core/str',
    'core/templates',
    'core/ajax',
    'core/notification',
    'community_sharesequence/inview',
    'community_sharesequence/jquery-ui',
], function ($, Str, Templates, Ajax, Notification, inView) {

    return {
        PARENT: null,
        UNIQUEID: null,
        TRANSLATED_STRINGS: {},
        SELECTED_ACTIVITIES: {},
        CREATED_SEQUENCES: {},
        SELECTORS: {

        },
        selectUnselectUnitChecbox: function (el) {
            let target = $(el);
            let targetName = target.data('checkbox-name');
            /*  Let parent = $(el).closest(`.${targetName}-item-wrapper`).parent(); */


            // Select/unselect all children checkboxes
            if (targetName === 'section' || targetName === 'subsection') {
                let collapseCheckboxes = $(`[data-checbox-parent=${target.attr('id')}]`);
                collapseCheckboxes.find("input:checkbox").prop('checked', target.prop("checked"));
            }

            // Select/unselect parent checkboxes
            function checkSelectedParent(target, targetName) {

                let checkboxWrapper = target.closest('.multicollapse');
                let parentName = target.closest('.multicollapse').data('checbox-parent');
                let parentCheckbox = $(`#${parentName}`);
                let siblings;
                let siblingCheckedCheckboxes;
                if (targetName === 'activity') {
                    siblings = checkboxWrapper.find(`[data-checkbox-name=${targetName}]`);
                    siblingCheckedCheckboxes = siblings.filter(':checked');
                } else if (targetName === 'subsection') {
                    siblings = checkboxWrapper.find('input:checkbox');
                    siblingCheckedCheckboxes = siblings.filter(':checked');
                }

                if (siblings.length === siblingCheckedCheckboxes.length) {
                    parentCheckbox.prop('checked', true);
                } else {
                    parentCheckbox.prop('checked', false);
                }
                if (targetName === 'activity') {
                    target = parentCheckbox;
                    targetName = 'subsection';
                    return checkSelectedParent(target, targetName);
                }
            }

            if (targetName === 'activity' || targetName === 'subsection') {
                checkSelectedParent(target, targetName);
            }
            this.updateSequences();
        },

        updateSequences: function () {
            const self = this;

            // Check select checboxes
            let selectedActivities = $(this.PARENT).find('[data-checkbox-name="activity"]').filter(':checked');
            this.SELECTED_ACTIVITIES = {};

            // If no activities is checked - remove all sequences
            if (selectedActivities.length === 0) {
                let createdSequences = $(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST).find('li');
                createdSequences.each((index, el) => {
                    $(el).remove();
                });
            } else {
                selectedActivities.each((index, el) => {
                    let elId = el.id;
                    if (!this.SELECTED_ACTIVITIES.hasOwnProperty(elId)) {
                        this.SELECTED_ACTIVITIES[elId] = $(el).data('name');
                    }
                });
                // Check is there already created sequences units
                let createdSequences = $(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST).find('li');
                this.CREATED_SEQUENCES = {};
                createdSequences.each((index, el) => {
                    let elId = $(el).data('activity_id');
                    if (!this.CREATED_SEQUENCES.hasOwnProperty(elId)) {
                        this.CREATED_SEQUENCES[elId] = $(el).data('name');
                    }
                });

                // Add checked units to sequence.
                for (let key in this.SELECTED_ACTIVITIES) {
                    if (!this.CREATED_SEQUENCES.hasOwnProperty(key)) {
                        let icon = $(`[for="${key}"]`).find('.activity-item-icon').html();
                        let dataCmid = key.replace("sequence_cm_", "");
                        const sequenceTemplate = `
                        <li class="sequence-item d-flex align-items-center justify-content-start border mb-3" data-name="${this.SELECTED_ACTIVITIES[key]}" data-activity_id="${key}">
                            <span class="sequence-item-sort">
                                <i class="fal fa-arrows"></i>
                            </span>
                            <span class="sequence-item-icon activity-icon d-block">${icon}</span>
                            <span class="sequence-item-name mr-auto" title="${this.SELECTED_ACTIVITIES[key]}">${this.SELECTED_ACTIVITIES[key]}</span>
                            <input class="sequence-item-text mr-auto" name="sequence-item-text" type="hidden" value="${this.SELECTED_ACTIVITIES[key]}" data-cmid="${dataCmid}">
                            <button class="sequence-item-rename" type="button" aria-label="${this.TRANSLATED_STRINGS.rename_sequence_unit}">
                                <i class="fal fa-pencil"></i>
                            </button>
                            <label class="sequence-item-delete ml-auto" role="button" aria-label="${this.TRANSLATED_STRINGS.delete_unit_from_sequence}" for="${key}">
                                <i class="fal fa-times"></i>
                            </label>
                        </li>`;

                        // Add new sequence to the list.
                        $(sequenceTemplate).appendTo($(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST));
                    }
                }

                // Remove unchecked units from sequence
                for (let key in this.CREATED_SEQUENCES) {
                    if (!this.SELECTED_ACTIVITIES.hasOwnProperty(key)) {
                        let sequenceItem = $(`[data-activity_id=${key}]`);

                        // Remove sequence from the list.
                        sequenceItem.remove();
                    }
                }

            }

            // Make sequence list items sortable
            $(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST).sortable({
                change: function (event, ui) {
                    self.setIndexes($(self.PARENT).find(self.SELECTORS.SEQUENCE_LIST));

                },
                create:
                    function (event, ui) {
                        self.setIndexes($(self.PARENT).find(self.SELECTORS.SEQUENCE_LIST));
                    },
            });
            $(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST).disableSelection();

            this.setIndexes($(this.PARENT).find(this.SELECTORS.SEQUENCE_LIST));
        },
        setIndexes: function (target) {

            let items = $(target).find('li.sequence-item');
            items.each((index, el) => {
                $(el).find('input').data('index', index);
            });

        },
        renameSequence: function (target) {

            let nameInput = target.find('input');
            let unitName = target.find(this.SELECTORS.SEQUENCE_ITEM_NAME);
            let editBtn = target.find(this.SELECTORS.RENAME_BTN);

            editBtn.hide();
            unitName.hide();
            nameInput.show();
            nameInput.attr('type', 'text');
            nameInput.focus();



            nameInput.on('change keypress focusout', function (e) {
                if (e.which === 13 || e.type === 'change' || e.type === 'focusout') {
                    let value = $(e.target).val();
                    target.data('name', value);
                    unitName.text(value);
                    unitName.show();
                    unitName.attr('title', value);
                    nameInput.hide();
                    editBtn.attr('style', '');
                }
            });
        },
        updateSelectors: function (uniqueid) {
            // Set selectors.
            this.SELECTORS.UNITS_BLOCK = '.units-block';
            this.SELECTORS.SEQUENCE_BLOCK = '.sequence-block';
            this.SELECTORS.SEQUENCE_LIST = '#sequence-item-list-' + uniqueid;
            this.SELECTORS.COLLAPSE_UNIT_BTNS = '.check-section-label';
            this.SELECTORS.COLLAPSE_TOPIC_BTNS = '.collapse-subsection-btn';
            this.SELECTORS.SECTION_CHEKBOX = '.check-section-checkbox';
            this.SELECTORS.SUBSECTION_CHEKBOX = '.check-subsection-checkbox';
            this.SELECTORS.ACTIVITY_CHEKBOX = '.check-activity-checkbox';
            this.SELECTORS.SEQUENCE_ITEM_NAME = '.sequence-item-name';
            this.SELECTORS.RENAME_BTN = '.sequence-item-rename';
        },
        openInviewSection: function (name) {
            inView(name).on('enter', function (e) {
                let parent = $(e);
                if (!parent.hasClass('inview-done')) {
                    parent.addClass('inview-done');

                    let sectionid = parent.data('sectionid');

                    Ajax.call([{
                        methodname: 'community_sharesequence_get_data_for_section',
                        args: {
                            sectionid: sectionid
                        },
                        done: function (response) {
                            let data = JSON.parse(response.data);

                            Templates.render('community_sharesequence/section_data', data)
                                .done(function (html, js) {
                                    Templates.appendNodeContents(parent, html, js);
                                })
                                .fail(Notification.exception);
                        },
                        fail: Notification.exception
                    }]);
                }

            }).on('exit', el => { });
        },
        selectActivities: function () {
            let self = this;

            self.updateSelectors(self.UNIQUEID);

            // Listen changes (select/unselect) on unit checkboxes and mark/unmark parent checbox.
            $(self.PARENT).find(self.SELECTORS.SECTION_CHEKBOX).on('change', (e) => {
                $('.sequence-item-list-alert').hide();
                self.selectUnselectUnitChecbox(e.target);
            });
            $(self.PARENT).find(self.SELECTORS.SUBSECTION_CHEKBOX).on('change', (e) => {
                $('.sequence-item-list-alert').hide();
                self.selectUnselectUnitChecbox(e.target);
            });
            $(self.PARENT).find(self.SELECTORS.ACTIVITY_CHEKBOX).on('change', (e) => {
                $('.sequence-item-list-alert').hide();
                this.selectUnselectUnitChecbox(e.target);
            });

        },
        init: function (parent, uniqid) {
            const self = this;
            this.PARENT = $(parent);
            this.UNIQUEID = uniqid;

            // Get translated string for sequence unit.
            const strings = [
                {
                    key: 'rename_sequence_unit',
                    component: 'community_sharesequence'
                },
                {
                    key: 'delete_unit_from_sequence',
                    component: 'community_sharesequence',
                }
            ];

            Str.get_strings(strings).then(function (results) {
                self.TRANSLATED_STRINGS.rename_sequence_unit = results[0];
                self.TRANSLATED_STRINGS.delete_unit_from_sequence = results[1];
            });

            self.updateSelectors(self.UNIQUEID);

            // Toggle active state of unit
            $(self.PARENT).find(self.SELECTORS.COLLAPSE_UNIT_BTNS).on('click', (e) => {
                $(e.target).closest('.section-item').toggleClass('active');
            });

            // Add rename function.
            $(self.PARENT).on('click', self.SELECTORS.RENAME_BTN, (e) => {

                let target = $(e.target).closest('li.sequence-item');
                e.preventDefault();
                self.renameSequence(target);
            });

            $("form").submit(function(e){
                e.preventDefault();
                return false;
            });
        }
    };
});
