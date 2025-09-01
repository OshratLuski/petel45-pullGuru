/* eslint-disable no-debugger */
/* eslint-disable no-console */
/* eslint-disable no-trailing-spaces */
/* eslint-disable max-len */
/* eslint-disable no-unused-vars */
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
 * @module     local_petel/action_participants_custom
 * @copyright  2023 devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as DynamicTable from 'core_table/dynamic';
import * as Str from 'core/str';
import CheckboxToggleAll from 'core/checkbox-toggleall';
import CustomEvents from 'core/custom_interaction_events';
import DynamicTableSelectors from 'core_table/local/dynamic/selectors';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import Pending from 'core/pending';
import jQuery from 'jquery';
import { showAddNote, showSendMessage } from 'core_user/local/participants/bulkactions';
import 'core/inplace_editable';

import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import * as Toast from 'core/toast';

import * as Ajax from 'core/ajax';

const Selectors = {
    bulkActionSelect: "#formactionid",
    bulkUserSelectedCheckBoxes: "input[data-togglegroup='participants-table'][data-toggle='slave']:checked",
    checkCountButton: "#checkall",
    showCountText: '[data-region="participant-count"]',
    showCountToggle: '[data-action="showcount"]',
    stateHelpIcon: '[data-region="state-help-icon"]',
    tableForm: uniqueId => `form[data-table-unique-id="${uniqueId}"]`,
};

export const init = ({
    uniqueid,
    noteStateNames = {},
    currentcourseid,
    currentuserid,
    defaults }) => {

    const root = document.querySelector(Selectors.tableForm(uniqueid));
    const getTableFromUniqueId = uniqueId => root.querySelector(DynamicTableSelectors.main.fromRegionId(uniqueId));

    /**
     * Private method.
     *
     * @method registerEventListeners
     * @private
     */
    const registerEventListeners = () => {
        CustomEvents.define(Selectors.bulkActionSelect, [CustomEvents.events.accessibleChange]);

        Str.get_strings([
            { key: 'buttoncreatecourse', component: 'local_petel' },
            { key: 'buttonaddsystemgroups', component: 'local_petel' }
        ]).done(function (strings) {

            jQuery("#formactionid option").each(function (index) {
                if (index === 2) {
                    let name = strings[0];
                    var newOption = '<option id="participants-createcourse" value="#createcourse">' + name + '</option>';
                    jQuery(newOption).insertAfter(jQuery(this));

                    name = strings[1];
                    newOption = '<option id="participants-addsystemgroups" value="#addsystemgroups">' + name + '</option>';
                    jQuery(newOption).insertAfter(jQuery(this));
                }
            });

        });
        jQuery(Selectors.bulkActionSelect).on(CustomEvents.events.accessibleChange, e => {


            console.log(uniqueid, 'uniqueid');
            const bulkActionSelect = e.target.closest('select');
            const action = bulkActionSelect.value;
            const tableRoot = getTableFromUniqueId(uniqueid);
            const checkboxes = tableRoot.querySelectorAll(Selectors.bulkUserSelectedCheckBoxes);
            const pendingPromise = new Pending('core_user/participants:bulkActionSelect');
            if (action.indexOf('#') !== -1) {
                e.preventDefault();

                const ids = [];
                checkboxes.forEach(checkbox => {
                    ids.push(checkbox.getAttribute('name').replace('user', ''));
                });

                let bulkAction;
                if (action === '#messageselect') {
                    bulkAction = showSendMessage(ids);
                } else if (action === '#addgroupnote') {
                    bulkAction = showAddNote(
                        root.dataset.courseId,
                        ids,
                        noteStateNames,
                        root.querySelector(Selectors.stateHelpIcon)
                    );
                }
                if (action === '#addsystemgroups') {
                    bulkAction = openPopupAddSystemGroups(currentcourseid, currentuserid, defaults);

                }
                if (action === '#createcourse') {
                    bulkAction = openPopupCourseCreate(currentcourseid, currentuserid, defaults);

                }

                if (bulkAction) {
                    const pendingBulkAction = new Pending('core_user/participants:bulkActionSelected');
                    bulkAction
                        .then(modal => {
                            modal.getRoot().on(ModalEvents.hidden, () => {
                                // Focus on the action select when the dialog is closed.
                                bulkActionSelect.focus();
                            });

                            pendingBulkAction.resolve();
                            return modal;
                        })
                        .catch(Notification.exception);
                }
            } else if (action !== '' && checkboxes.length) {
                bulkActionSelect.form.submit();
            }

            resetBulkAction(bulkActionSelect);
            pendingPromise.resolve();
        });

        root.addEventListener('click', e => {
            // Handle clicking of the "Select all" actions.
            const checkCountButton = root.querySelector(Selectors.checkCountButton);
            const checkCountButtonClicked = checkCountButton && checkCountButton.contains(e.target);

            if (checkCountButtonClicked) {
                e.preventDefault();

                const tableRoot = getTableFromUniqueId(uniqueid);

                DynamicTable.setPageSize(tableRoot, checkCountButton.dataset.targetPageSize)
                    .then(tableRoot => {
                        // Update the toggle state.
                        CheckboxToggleAll.setGroupState(root, 'participants-table', true);

                        return tableRoot;
                    })
                    .catch(Notification.exception);
            }
        });

        // When the content is refreshed, update the row counts in various places.
        root.addEventListener(DynamicTable.Events.tableContentRefreshed, e => {
            const checkCountButton = root.querySelector(Selectors.checkCountButton);
            const tableRoot = e.target;
            const defaultPageSize = parseInt(tableRoot.dataset.tableDefaultPerPage, 10);
            const currentPageSize = parseInt(tableRoot.dataset.tablePageSize, 10);
            const totalRowCount = parseInt(tableRoot.dataset.tableTotalRows, 10);

            CheckboxToggleAll.updateSlavesFromMasterState(root, 'participants-table');

            const pageCountStrings = [
                {
                    key: 'countparticipantsfound',
                    component: 'core_user',
                    param: totalRowCount,
                },
            ];

            if (totalRowCount <= defaultPageSize) {
                if (checkCountButton) {
                    checkCountButton.classList.add('hidden');
                }
            } else if (totalRowCount <= currentPageSize) {
                // The are fewer than the current page size.
                pageCountStrings.push({
                    key: 'selectalluserswithcount',
                    component: 'core',
                    param: defaultPageSize,
                });

                if (checkCountButton) {
                    // The 'Check all [x]' button is only visible when there are values to set.
                    checkCountButton.classList.add('hidden');
                }
            } else {
                pageCountStrings.push({
                    key: 'selectalluserswithcount',
                    component: 'core',
                    param: totalRowCount,
                });

                if (checkCountButton) {
                    checkCountButton.classList.remove('hidden');
                }
            }

            Str.get_strings(pageCountStrings)
                .then(([showingParticipantCountString, selectCountString]) => {
                    const showingParticipantCount = root.querySelector(Selectors.showCountText);
                    showingParticipantCount.innerHTML = showingParticipantCountString;

                    if (selectCountString && checkCountButton) {
                        checkCountButton.value = selectCountString;
                    }

                    return;
                })
                .catch(Notification.exception);
        });
    };

    const resetBulkAction = bulkActionSelect => {
        bulkActionSelect.value = '';
    };

    registerEventListeners();


    const openPopupAddSystemGroups = function (currentcourseid, currentuserid, defaults) {

        // Set new uniqueid.
        const openPopupAddSystemGroupsUniqueid = Date.now();

        // Get selected users.
        var users = [];
        jQuery('#participants tbody tr input:checked').each(function (index) {
            users.push(jQuery(this).attr("name").replace('user', ''));
        });

        let titlePromise = null;
        if (users.length > 1) {
            titlePromise = Str.get_string('titleaddsystemgroups', 'local_petel', users.length);
        } else {
            titlePromise = Str.get_string('titleaddsystemgroups1', 'local_petel');
        }

        Str.get_strings([
            { key: 'selectgroups', component: 'local_petel' }
        ]).done(function (strings) {

            const systemgroupsac = {
                title: strings[0],
                inputid: 'system_groups_ac' + openPopupAddSystemGroupsUniqueid,
                methodname: 'get_system_groups_ac',
            };

            const context = {

                autocompletemultiplefields: [
                    systemgroupsac
                ],
            };

            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                body: Templates.render('local_petel/popup_action_participants', context),
                title: titlePromise,
                buttons: {
                    save: titlePromise,
                },
                removeOnClose: true,
            })
                .then(modal => {
                    modal.getRoot().on(ModalEvents.save, () => submitPopupAddSystemGroups(currentcourseid, currentuserid, users, openPopupAddSystemGroupsUniqueid));
                    modal.show();
                    return modal;
                });
        });
    };

    const submitPopupAddSystemGroups = function (currentcourseid, currentuserid, users, openPopupAddSystemGroupsUniqueid) {

        var Selector = {
            GROUPS_SELECT: '.modal.show #system_groups_ac' + openPopupAddSystemGroupsUniqueid,
        };

        // Check data.
        jQuery(Selector.GROUPS_SELECT).parent().find('.error-block').hide();

        let groupids = jQuery(Selector.GROUPS_SELECT).val();

        if (groupids.length !== 0) {
            var data = {
                groupids: JSON.stringify(groupids),
                currentuserid: currentuserid,
                users: JSON.stringify(users)
            };

            Ajax.call([{
                methodname: 'local_petel_create_system_groups_for_teachers',
                args: data,
                done: function (response) {

                    if (response.result) {
                        Str.get_strings([
                            { key: 'coursescreated', component: 'local_petel' }
                        ]).done(function (strings) {
                            debugger;
                            Toast.add(strings[0]);
                        });
                    }
                },
                fail: Notification.exception
            }]);
        } else {
            if (groupids.length === 0) {
                jQuery(Selector.GROUPS_SELECT).parent().find('.error-block').show();
            }
            return false;
        }
    };

    const openPopupCourseCreate = function (currentcourseid, currentuserid, defaults) {
       
        // Set new uniqueid.
        const popupCourseCreateUniqueid = Date.now();

        // Get selected users.
        var users = [];
        jQuery('#participants tbody tr input:checked').each(function (index) {
            users.push(jQuery(this).attr("name").replace('user', ''));
        });

        let titlePromise = null;
        if (users.length > 1) {
            titlePromise = Str.get_string('titlecreatecourse', 'local_petel', users.length);
        } else {
            titlePromise = Str.get_string('titlecreatecourse1', 'local_petel');
        }

        Str.get_strings([
            { key: 'selectmaincategory', component: 'local_petel' },
            { key: 'selecttemplatecourse', component: 'local_petel' },
            { key: 'selectrole', component: 'local_petel' },
            { key: 'selectgroups', component: 'local_petel' },
            { key: 'keynull', component: 'local_petel' },
        ]).done(function (strings) {

            const categoriesac = {
                title: strings[0],
                placeholder: strings[0],
                inputid: 'categories_ac' + popupCourseCreateUniqueid,
                methodname: 'get_categories_ac',
                paramname: 'main-category',
                paramid: 'main-category' + popupCourseCreateUniqueid,
                paramvalue: defaults.categories_ac.value,
                paramdesc: defaults.categories_ac.name,
            };
            const coursesac = {
                title: strings[1],
                placeholder: strings[1],
                inputid: 'courses_ac' + popupCourseCreateUniqueid,
                methodname: 'get_courses_ac',
                paramname: 'template-course',
                paramid: 'template-course' + popupCourseCreateUniqueid,
                paramvalue: defaults.courses_ac.value,
                paramdesc: defaults.courses_ac.name,
            };
            const rolesac = {
                title: strings[2],
                placeholder: strings[2],
                inputid: 'roles_ac' + popupCourseCreateUniqueid,
                methodname: 'get_roles_ac',
                paramname: 'template-role',
                paramid: 'template-role' + popupCourseCreateUniqueid,
                paramvalue: defaults.roles_ac.value,
                paramdesc: defaults.roles_ac.name,
            };
            const groupsac = {
                title: strings[3],
                inputid: 'system_groups_ac' + popupCourseCreateUniqueid,
                methodname: 'get_system_groups_ac',
            };
            const keynull = {
                title: strings[4],
                paramname: 'template-keynull',
                inputid: 'keynull' + popupCourseCreateUniqueid,
                paramvalue: defaults.roles_ac.value,
            };

            const context = {
                autocompletefields: [
                    categoriesac,
                    coursesac,
                    rolesac
                ],

                autocompletemultiplefields: [
                    groupsac
                ],

                // checkbox: [
                //     keynull
                // ],
            };

            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                body: Templates.render('local_petel/popup_action_participants', context),
                title: titlePromise,
                buttons: {
                    save: titlePromise,
                },
                removeOnClose: true,
            })
                .then(modal => {
                    modal.getRoot().on(ModalEvents.save, () => submitPopupCourseCreate(currentcourseid, currentuserid, users, popupCourseCreateUniqueid));
                    modal.show();
                    return modal;
                });
        });
    };

    const submitPopupCourseCreate = function (currentcourseid, currentuserid, users, popupCourseCreateUniqueid) {

        var Selector = {
            CATEGORY_SELECT: '.modal.show #main-category' + popupCourseCreateUniqueid,
            COURSE_SELECT: '.modal.show #template-course' + popupCourseCreateUniqueid,
            ROLE_SELECT: '.modal.show #template-role' + popupCourseCreateUniqueid,
            GROUPS_SELECT: '.modal.show #system_groups_ac' + popupCourseCreateUniqueid,
            NULL_CHECKBOX: '.modal.show #keynull' + popupCourseCreateUniqueid,
        };

        // Check data.
        jQuery(Selector.CATEGORY_SELECT).parent().find('.error-block').hide();
        jQuery(Selector.COURSE_SELECT).parent().find('.error-block').hide();
        jQuery(Selector.ROLE_SELECT).parent().find('.error-block').hide();
        jQuery(Selector.GROUPS_SELECT).parent().find('.error-block').hide();
        jQuery(Selector.NULL_CHECKBOX).parent().find('.error-block').hide();

        let categoryid = jQuery(Selector.CATEGORY_SELECT).val();
        let courseid = jQuery(Selector.COURSE_SELECT).val();
        let roleid = jQuery(Selector.ROLE_SELECT).val();
        let groups = jQuery(Selector.GROUPS_SELECT).val();

        //let nullcheck = jQuery(Selector.NULL_CHECKBOX).is(':checked');
        let nullcheck = true;

        if (categoryid.length !== 0 && courseid.length !== 0 && roleid.length !== 0) {
            var data = {
                categoryid: categoryid,
                courseid: courseid,
                roleid: roleid,
                groups: JSON.stringify(groups),
                nullcheck: nullcheck,
                currentuserid: currentuserid,
                users: JSON.stringify(users)
            };

            Ajax.call([{
                methodname: 'local_petel_create_courses_for_teachers',
                args: data,
                done: function (response) {

                    if (response.result) {
                        Str.get_strings([
                            { key: 'coursescreated', component: 'local_petel' }
                        ]).done(function (strings) {
                            debugger;
                            Toast.add(strings[0]);
                        });
                    }
                },
                fail: Notification.exception
            }]);
        } else {
            if (categoryid.length === 0) {
                jQuery(Selector.CATEGORY_SELECT).parent().find('.error-block').show();
            }

            if (courseid.length === 0) {
                jQuery(Selector.COURSE_SELECT).parent().find('.error-block').show();
            }

            if (roleid.length === 0) {
                jQuery(Selector.ROLE_SELECT).parent().find('.error-block').show();
            }

            // If (groups.length === 0) {
            //     jQuery(Selector.GROUPS_SELECT).parent().find('.error-block').show();
            // }
            //
            // if (!nullcheck) {
            //     jQuery(Selector.NULL_CHECKBOX).parent().find('.error-block').show();
            // }

            return false;
        }
    };

};
