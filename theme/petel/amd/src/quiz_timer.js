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
 * Javascript controller for the aside blocks.
 *
 * @module     theme_petel/quiz_timer
 * @package
 * @copyright  2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.7
 */

import {exception as displayException} from 'core/notification';
import Ajax from 'core/ajax';
import * as Str from 'core/str';
import * as Templates from 'core/templates';
import * as timeHumanizer from 'theme_petel/humanize_duration';


const SELECTORS = {};
const STRINGS = {};
const TIMER = {
    endtime: 0, // Timestamp at which time runs out, according to the student's computer's clock.
    timeoutid: null, // This records the id of the timeout that updates the clock periodically, so we can cancel.
    timeLimitTotal: 0,
    start: null,
    timerEnabled: false
};

export const REMINDER = {
    type: 'withoutWarnings',
    isVisible: false,
};

const initSelectors = () => {
    SELECTORS.alltimeInfo = document.getElementById('alltime_info');
    SELECTORS.displayStopwatch = document.getElementById('displayStopwatch');
    SELECTORS.form = document.getElementById('responseform');
    SELECTORS.quizProgressLabel = document.getElementById('quiz_progress_label');
    SELECTORS.quizTimeBlock = document.getElementById('quiz-time-block');
    SELECTORS.quizTimeBlockBody = document.querySelector('.quiz-time-block-body');
    SELECTORS.quizTimeBlockWrapper = document.querySelector('.quiz-time-block-wrapper');
    SELECTORS.quizTimecounterLabel = document.getElementById('quiz_timecounter_label');
    SELECTORS.quizTimecounterLabelWrapper = document.querySelector('.quiz_timecounter_label-wrapper');
    SELECTORS.quizTotalLabel = document.getElementById('quiz_total_label');
    SELECTORS.reminderInfo = document.querySelector('.reminder-info');
    SELECTORS.reminderInfoWrapper = document.querySelector('.reminder-info-wrapper');
    SELECTORS.reminderTypeInfo = document.getElementById('reminder_type_info');
    SELECTORS.setAlertTimer = document.querySelectorAll('[name="setAlertTimer"]');
    SELECTORS.timeLeftInfo = document.getElementById('time_left_info');
    SELECTORS.timelineProgress = document.getElementById('timeline_progress');
    SELECTORS.timelineProgressInner = document.getElementById('timeline_progress_inner');
    SELECTORS.timeupInput = document.querySelector('input[name=timeup]');
    SELECTORS.textWrapper = document.querySelector('.text-wrapper');
    SELECTORS.turnOffAlerts = document.getElementById('turnoffalerts');

};

const stop = () => {
    if (TIMER.timeoutid) {
        clearTimeout(TIMER.timeoutid);
    }
};

const twoDigit = (num) => {
    if (num < 10) {
        return '0' + num;
    } else {
        return num;
    }
};

const checkTimer = () => {
if (+TIMER.timelimit === 0) {
    SELECTORS.quizTimecounterLabel.innerText = '';
}
};
const updateTimer = () => {
    let secondslefttotal = Math.floor((TIMER.endtime - new Date().getTime()) / 1000);


    if (secondslefttotal < 0 || secondslefttotal === 0) {
        stop();

        SELECTORS.timeupInput.value = 1;
        let form = SELECTORS.timeupInput.closest('form');
        if (form.querySelector('input[name=finishattempt]')) {
            form.querySelector('input[name=finishattempt]')[0].value = 0;
        }
        SELECTORS.quizTimecounterLabel.innerText = STRINGS.timeisup;
        // M.core_formchangechecker.set_form_submitted();
        form.submit();
        return;
    }

    let secondsleft = secondslefttotal;
    // Update the time display.
    let hours = Math.floor(secondsleft / 3600);
    secondsleft -= hours * 3600;
    let minutes = Math.floor(secondsleft / 60);
    secondsleft -= minutes * 60;

    let left = hours + ':' + twoDigit(minutes);
    let minutesBeenPassed = Math.floor((TIMER.timeLimitTotal - secondslefttotal) / 60);

    if (SELECTORS.quizTimecounterLabel) {
        if (secondslefttotal > 0) {
            SELECTORS.quizTimecounterLabel.innerText = STRINGS.timeStr(secondslefttotal * 1000) + ' ' + STRINGS.minutesleft;
        } else {
            SELECTORS.quizTimecounterLabel.innerText = STRINGS.timeisup;
        }
    }

    switch (REMINDER.type) {
        case 'withoutWarnings':
            break;
        case 'every30Min':
            if (Math.floor(minutesBeenPassed / 30) !== 0) {
                showReminder(hours + ':' + +twoDigit(minutes) + 1);
            }
            break;
        case 'thirtyMinBeforeEnd':
            if (Math.floor(secondslefttotal / 60) === 30) {
                showReminder(30);
            }
            break;
        case 'fifteenMinBeforeEnd':
            if (Math.floor(secondslefttotal / 60) === 15) {
                showReminder(15);
            }
            break;
        case 'fiveMinBeforeEnd':
            if (Math.floor(secondslefttotal / 60) === 5) {
                showReminder(5);
            }
            break;
        default:

    }


    let leftPerc = Math.floor(secondslefttotal / TIMER.timeLimitTotal * 100);

    if (SELECTORS.timelineProgress) {

        SELECTORS.timelineProgress.setAttribute('title', left);
    }
    if (SELECTORS.timelineProgressInner) {

        SELECTORS.timelineProgressInner.style.width = leftPerc + '%';
    }
    if (SELECTORS.timeLeftInfo) {
        SELECTORS.timeLeftInfo.innerText = STRINGS.timeStr(secondslefttotal * 1000);
    }
    // Arrange for this method to be called again soon.
    TIMER.timeoutid = setTimeout(updateTimer, 30000);
};


export const showReminder = (timeleft, target = document.querySelector('[role="main"]')) => {

    const strings = [
        {
            key: 'thirteenminutesleftuntiltheend',
            component: 'theme_petel'
        },
        {
            key: 'minutesleftuntiltheend',
            component: 'theme_petel',
            param: {
                timeleft: timeleft
            }
        },
        {
            key: 'every30minutes',
            component: 'theme_petel'
        },
        {
            key: 'thirthyminutesbeforetheend',
            component: 'theme_petel'
        },
        {
            key: 'withoutwarnings',
            component: 'theme_petel'
        },
    ];

    Str.get_strings(strings)
        .then(function(results) {
            STRINGS.thirteenminutesleftuntiltheend = results[0];
            STRINGS.minutesleftuntiltheend = results[1];
            STRINGS.every30Min = results[2];
            STRINGS.thirtyMinBeforeEnd = results[3];
            STRINGS.withoutWarnings = results[4];
            STRINGS.alert = results[5];

            const context = {
                text: STRINGS.minutesleftuntiltheend,
            };

            return Templates.renderForPromise('theme_petel/quiz_timer_alert', context)
                .then(({html, js}) => {
                    return Templates.appendNodeContents(target, html, js);
                })
                .catch(ex => displayException(ex));

        })
        .catch(ex => displayException(ex));

};

export const setReminder = (reminderType, isVisible, cmid) => {
    Ajax.call([{
        methodname: 'theme_petel_quiz_set_timer_preferences',
        args: {
            isvisible: isVisible,
            cmid: +cmid,
            remindertype: reminderType,
        },
        done: response => {
            if (response) {
                REMINDER.type = response.remindertype;
                REMINDER.isVisible = response.isvisible;
            }
        },
        fail: error => displayException(error)
    }]);
};

export const getReminder = (cmid) => {
    Ajax.call([{
        methodname: 'theme_petel_quiz_get_timer_preferences',
        args: {
            cmid: cmid,
        },
        done: response => {
            REMINDER.type = response.remindertype;
            REMINDER.isVisible = response.isvisible;
            updateTimer();
            checkState();
            return response;
        },
        fail: error => displayException(error)
    }]);
};

export const checkState = () => {

    SELECTORS.displayStopwatch.checked = REMINDER.isVisible;

    if (REMINDER.isVisible === 'false' || REMINDER.isVisible === false) {
        SELECTORS.quizTimeBlockBody.classList.add('hidden');
        SELECTORS.reminderInfoWrapper.classList.add('hidden');
        SELECTORS.reminderInfoWrapper.classList.remove('d-flex');
        SELECTORS.displayStopwatch.parentElement.classList.remove('active');
        SELECTORS.quizTimecounterLabelWrapper.classList.add('hidden');

        SELECTORS.setAlertTimer.forEach((el) => {
            el.setAttribute('disabled', 'true');
        });

    } else {
        SELECTORS.quizTimecounterLabelWrapper.classList.remove('hidden');
        SELECTORS.quizTimeBlockBody.classList.remove('hidden');
        SELECTORS.reminderInfoWrapper.classList.remove('hidden');
        SELECTORS.reminderInfoWrapper.classList.add('d-flex');

        SELECTORS.setAlertTimer.forEach((el) => {
            el.removeAttribute('disabled');
        });

        SELECTORS.displayStopwatch.parentElement.classList.add('active');

    }


    let text;
    for (const [key, value] of Object.entries(STRINGS)) {
        if (key === REMINDER.type) {
            text = value;
        }
    }

    SELECTORS.reminderTypeInfo.innerText = STRINGS.alert + text;

    SELECTORS.setAlertTimer.forEach((el) => {
        if (el.id === REMINDER.type) {
            el.checked = true;
        } else {
            el.checked = false;
        }
    });

    SELECTORS.reminderInfo.classList.remove('hidden');
};

export const init = function(start, timeleft, timelimit,
    ispreview, progress, attemptid, cmid, userid, answered, totalquestions, timerEnabled, active) {

    TIMER.timerEnabled = timerEnabled;

    let lang = document.documentElement.lang;

    if (lang === 'he-kids') {
        lang = 'he';
    }

    TIMER.lang = lang || 'en';

    const strings = [
        {
            key: 'every30minutes',
            component: 'theme_petel'
        },
        {
            key: 'thirthyminutesbeforetheend',
            component: 'theme_petel'
        },
        {
            key: 'withoutwarnings',
            component: 'theme_petel'
        },
        {
            key: 'fifteenminutesbeforebnd',
            component: 'theme_petel'
        },
        {
            key: 'fiveminutesbeforetheend',
            component: 'theme_petel'
        },
        {
            key: 'alert',
            component: 'theme_petel'
        },
        {
            key: 'timeisup',
            component: 'theme_petel'
        },
        {
            key: 'minutesleft',
            component: 'theme_petel'
        },
    ];

    Str.get_strings(strings)
        .then(function(results) {
            STRINGS.every30Min = results[0];
            STRINGS.thirtyMinBeforeEnd = results[1];
            STRINGS.withoutWarnings = results[2];
            STRINGS.fifteenMinBeforeEnd = results[3];
            STRINGS.fiveMinBeforeEnd = results[4];
            STRINGS.alert = results[5];
            STRINGS.timeisup = results[6];
            STRINGS.minutesleft = results[7];

            STRINGS.timeStr = timeHumanizer.humanizer({
                language: TIMER.lang,
                units: ["h", "m"],
                maxDecimalPoints: 0
            });
            TIMER.timelimit = +timelimit;
            initSelectors();
            checkTimer();
            // If quiz has timelimit in preferencess.
            if (TIMER.timerEnabled) {
                TIMER.start = +start;
                TIMER.timeLimitTotal = +timelimit;
                REMINDER.cmid = +cmid;
                let hours = Math.floor(timelimit / 3600);
                let minutes = Math.floor(timelimit / 60) % 60;
                TIMER.endtime = new Date().getTime() + start * 1000;

                getReminder(REMINDER.cmid);

                // SELECTORS.alltimeInfo.innerText = hours + ':' + minutes;
                SELECTORS.alltimeInfo.innerText = STRINGS.timeStr(TIMER.timeLimitTotal * 1000);


                SELECTORS.setAlertTimer.forEach((el) => {
                    el.onchange = (e) => {
                        REMINDER.type = e.target.value;
                        checkState();
                        setReminder(REMINDER.type, REMINDER.isVisible, REMINDER.cmid);
                    };
                });

                SELECTORS.displayStopwatch.onchange = (e) => {
                    REMINDER.isVisible = e.target.checked;
                    checkState();
                    setReminder(REMINDER.type, REMINDER.isVisible, REMINDER.cmid);
                };
                updateTimer();

            }

            SELECTORS.quizProgressLabel.innerText = answered;
            SELECTORS.quizTotalLabel.innerText = totalquestions;
            SELECTORS.textWrapper.classList.remove('d-none');

            if (active) {
                SELECTORS.form.onsubmit = () => {
                    stop();
                };
            }
            return;

        })
        .catch(ex => displayException(ex));
};

