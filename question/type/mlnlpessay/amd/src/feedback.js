/* eslint-disable no-console */
import ajax from "core/ajax";
import $ from "jquery";

const params = {};

const getData = (qaid) => {
    let newParams = params[qaid];
    ajax.call([{
        methodname: "qtype_mlnlpessay_get_feedback", args: {
            questionid: newParams.qid,
            questionattemptid: newParams.qaid
        }
    }])[0].done(function(response) {
        if (response['status']) {
            let data = response['response'];
            data = JSON.parse(data);
            draw(data, newParams);
            clearInterval(newParams.int);
        }
    }).fail(Notification.exception);
};

const draw = (data, params) => {
    let place = $('.' + params.selector + '-' + params.qid);
    place.html(data);
};

const timer = (qaid) => {
    params[qaid].int = setInterval(function() {
        getData(qaid);
    }, 5000);
};

export const init = (qid, qaid, selector) => {
    let newObj = {
        qid: qid,
        qaid: qaid,
        selector: selector,
        int: null,

    };
    params[qaid] = newObj;
    getData(newObj.qaid);
    timer(newObj.qaid);
};
