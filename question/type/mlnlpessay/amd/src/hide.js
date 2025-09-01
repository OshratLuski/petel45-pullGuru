import $ from "jquery";

var sel;
var questionid;
const hidebutton = () => {
    let place = $('#' + sel + '-' + questionid);
    place.parent().parent().parent().parent().find('.mod_quiz-redo_question_button').hide();
};
export const init = (qid, qaid, selector) => {
    sel = selector;
    questionid = qid;
    hidebutton();
};
