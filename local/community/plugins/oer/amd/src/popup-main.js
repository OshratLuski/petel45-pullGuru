define([
    'jquery',
    'core/str',
    'core/notification',
    'community_oer/activity',
    'community_oer/popup-question',
    'community_oer/sequence',
    'community_oer/course'

], function ($, Str, Notification, Activity, Question, Sequence, Course) {
    `use strict`;

    let instance_data = {};
    let preset_data = {};
    let current_dashboard = null;
    let current_aside = {};
    let prev_search = [];
    let url_params = {};

    return {
        init: function (dashboard, type, value) {
            let self = this;

            // Get url params.
            let res = window.location.href.split("?");
            if(res[1] !== undefined){
                let str = '&' + res[1];
                str.replace(/[?&]+([^=&]+)=([^&]*)/gi,
                    function(m,key,value) {
                        url_params[key] = value;
                    });
            }

            current_aside.render_aside = true;
            current_aside.type = type;
            current_aside.value = value;
            current_aside.url_params = url_params;

            // Hide header block.
            $('#page-header').addClass('d-none');

            // Default dashboard start.
            this.buildDasboard(dashboard);

            // Enter on search input.
            $('#search_form').find('input').val('');
            $('#search_form').submit(function(e){
                e.preventDefault();

                let target = $('#search_form').find('input');

                if(target.val().trim().length > 0){

                    $(target).data("value", target.val().trim());
                    $('#search_form').find('input').val('');

                    switch (current_dashboard) {
                        case 'activity':
                            Activity.actionOnClick(target);
                            preset_data.activity = Activity.returnPreset();
                            break;

                        case 'question':
                            Question.actionOnClick(target);
                            preset_data.question = Question.returnPreset();
                            break;

                        case 'sequence':
                            Sequence.actionOnClick(target);
                            preset_data.sequence = Sequence.returnPreset();
                            break;

                        case 'course':
                            Course.actionOnClick(target);
                            preset_data.course = Course.returnPreset();
                            break;
                    }
                }
            });

            // Click on main-area.
            $( ".main-area" ).on( "click", function(e) {
                let target = $(e.target).data();

                // Side menu.
                switch (target.area) {
                    case 'sidemenu':
                        current_aside.render_aside = true;
                        current_aside.type = target.action;
                        current_aside.value = target.value;
                        current_aside.url_params = url_params;
                        break;
                }

                switch (target.plugin) {
                    case 'main':
                        self.actionOnClick(e.target);
                        preset_data.main = self.returnPreset();
                        break;

                    case 'activity':
                        Activity.actionOnClick(e.target);
                        preset_data.activity = Activity.returnPreset();
                        break;

                    case 'question':
                        Question.actionOnClick(e.target);
                        preset_data.question = Question.returnPreset();
                        break;

                    case 'sequence':
                        Sequence.actionOnClick(e.target);
                        preset_data.sequence = Sequence.returnPreset();
                        break;

                    case 'course':
                        Course.actionOnClick(e.target);
                        preset_data.course = Course.returnPreset();
                        break;
                }
            });

        },

        buildDasboard: function (dashboard) {
            let self = this;

            // Dashboard start.
            switch (dashboard) {
                case 'activity':
                    current_dashboard = 'activity';
                    Activity.init(function(res){
                        instance_data.activity = JSON.parse(res);

                        // Render instance.
                        Activity.renderInstance(instance_data.activity, current_aside, prev_search, preset_data.activity, function (){
                            preset_data.activity = Activity.returnPreset();
                        });
                    });
                    break;

                case 'question':
                    current_dashboard = 'question';
                    Question.init(function(res){
                        instance_data.question = JSON.parse(res);

                        // Render instance.
                        Question.renderInstance(instance_data.question, current_aside, prev_search, preset_data.question, function (){
                            preset_data.question = Question.returnPreset();
                        });
                    });
                    break;

                case 'sequence':
                    current_dashboard = 'sequence';
                    Sequence.init(function(res){
                        instance_data.sequence = JSON.parse(res);

                        // Render instance.
                        Sequence.renderInstance(instance_data.sequence, current_aside, prev_search, preset_data.sequence, function (){
                            preset_data.sequence = Sequence.returnPreset();
                        });
                    });
                    break;

                case 'course':
                    current_dashboard = 'course';
                    Course.init(function(res){
                        instance_data.course = JSON.parse(res);

                        // Render instance.
                        Course.renderInstance(instance_data.course, current_aside, prev_search, preset_data.course, function (){
                            preset_data.course = Course.returnPreset();
                        });
                    });
                    break;
            }
        },

        actionOnClick: function (object) {
            let data = $(object).data();

            if(current_dashboard !== data.value) {

                if (data.area !== 'mainsearch') {
                    $('.main-filter-nav').find('a').removeClass('active');

                    if($(object).hasClass('main-total-elements-question') || $(object).hasClass('main-total-elements-activity') || $(object).hasClass('main-total-elements-sequence') || $(object).hasClass('main-total-elements-course')){
                        $(object).parent().addClass('active');
                    }else{
                        $(object).addClass('active');
                    }
                }

                // Get current search data.
                prev_search = [];
                let elementssearch = $("*").filter(function () {
                    return ($(this).data("area") === 'pillsearch');
                });

                $(elementssearch).each(function () {
                    prev_search.push($(this).data("value"));
                });

                this.buildDasboard(data.value);
            }
        },

        returnPreset: function () {
            return {};
        },

        changeTitleBreadcrumbs: function (title) {
            $('.main-title').html(title);
        },

        reRerenderMain: function () {

            let obj = $('.main-filter-nav').find('.active');

            switch (obj.data("value")) {
                case 'activity':
                    Activity.renderBlocks();
                    break;

                case 'question':
                    Question.renderBlocks();
                    break;

                case 'sequence':
                    Sequence.renderBlocks();
                    break;

                case 'course':
                    Course.renderBlocks();
                    break;
            }

            return true;
        },

    }
});
