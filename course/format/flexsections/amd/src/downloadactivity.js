import { call as fetchMany } from 'core/ajax';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import { get_string as getString } from 'core/str';

const Selectors = {
    actions: {
        showDownloadModalBtn: '[data-action="show_download_modal"]',
    },
    targets: {
        activityInstance: '.activity-instance',
    }
};

const activityDownloadAjax = (
    cmid,
    courseid,
) => fetchMany([{
    methodname: 'format_flexsections_version_download',
    args: {
        cmid,
        courseid,
    },
}])[0].then(response => {
    return response;
}).catch(error => {
    window.console.error('API call failed:', error);
});

const getInfoAjax = (
    cmid,
    courseid,
) => fetchMany([{
    methodname: 'format_flexsections_get_info',
    args: {
        cmid,
        courseid,
    },
}])[0].then(response => {
    return response; // Should log 'everything is ok, mate!'
}).catch(error => {
    window.console.error('API call failed:', error);
});

const TEMPOBJ = {};

const clearTempObj = () => {
    for (const prop in TEMPOBJ) {
        if (TEMPOBJ.hasOwnProperty(prop)) {
            delete TEMPOBJ[prop];
        }
    }
};

const setCancelButtonText = async (target) => {
    const closeText = await getString('close', 'format_flexsections');
    target.innerHTML = closeText;
};

export const showModal = async () => {
    const info = await getInfoAjax(TEMPOBJ.cmid, TEMPOBJ.courseid);
    const modal = await ModalFactory.create({
        title: info.title,
        body: info.body,
        type: ModalFactory.types.SAVE_CANCEL,
    });

    modal.setSaveButtonText(getString('downloadtheversionofthisactivity', 'format_flexsections'));
    modal.getRoot().on(ModalEvents.save, function (e) {
        e.preventDefault();

        activityDownload(modal.getBody()[0]);
        modal.getFooter()[0].querySelector('[data-action="save"]').disabled = true;

        setCancelButtonText(modal.getFooter()[0].querySelector('[data-action="cancel"]'));
        clearTempObj();
    });

    modal.getRoot().on(ModalEvents.cancel, function () {
        clearTempObj();
        modal.hide();
    });
    modal.show();
};

const activityDownload = async (target) => {
    const response = await activityDownloadAjax(+TEMPOBJ.cmid, +TEMPOBJ.courseid);
    target.innerHTML = response;
    return response;
};

const getCmIdAndCourseId = (e) => {
    const activityInstance = e.target.closest(Selectors.targets.activityInstance);
    const activitytitle = activityInstance.querySelector('.activitytitle');
    if (activitytitle.nextElementSibling.dataset.cmid.length > 0) {
        TEMPOBJ.cmid = activitytitle.nextElementSibling.dataset.cmid;
    }
    if (activitytitle.nextElementSibling.dataset.courseid.length > 0) {
        TEMPOBJ.courseid = activitytitle.nextElementSibling.dataset.courseid;
    }
};

export const init = () => {
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.showDownloadModalBtn)) {
            e.stopPropagation();
            getCmIdAndCourseId(e);
            showModal(e);
        }
    });
};
