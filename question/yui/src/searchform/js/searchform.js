
    var SELECTORS = {
            OPTIONS: '.searchoptions'
        },
        NS;

    M.question = M.question || {};
    NS = M.question.searchform = {};

    NS.init = function() {
        $('body').on('select2:select', SELECTORS.OPTIONS, function(e) {
            $(e.target).parents('form').submit();
        });
    };

    NS.option_changed = function(e) {
            e.target.getDOMNode().form.submit();
    };

