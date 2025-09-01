define(['jquery'], function ($) {
    'use strict';

    const main = {
        init: (checkid, generalFeedback) => {
            let check = document.getElementById(checkid);
            check.addEventListener('click', function () {
                if (!document.getElementById('custom_general_feedback_' + checkid)) {
                    let feedback = '<div class="outcome clearfix" id="custom_general_feedback_' + checkid + '">' + generalFeedback + '</div>';
                    this.closest('.formulation').insertAdjacentHTML('afterend', feedback);
                }
            });
        }
    }

    return main;
});
