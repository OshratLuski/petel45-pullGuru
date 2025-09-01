/**
 * Javascript to initialise the myoverview block.
 *
 * @package    block_sharedwithme
 * @copyright  2018 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/ajax',
        'core/notification',
        'local_petel/inview'
    ],
    function ($, Ajax, Notification, inView) {
        `use strict`;

        var selector = {
            wrapper: '#block-sharedwithme-selectSort',
            mainBlock: '.block-sharedwithme-content-courses',
            selectItems: '#block-sharedwithme-selectSort .dropdown-item',
            spinner: '#block-sharedwithme-selectSort .spinner-border',
            button: '.sharedwithme-btn',
            page: '#block-sharedwithme-page',
        };

        const toggleSpinner = (status) => {
            var spinner = $(selector.spinner);
            var borderColor = status ? '' : 'transparent';
            spinner.css('border-color', borderColor);
        };

        const renderBlock = (perpage) => {

            $(selector.page).val(perpage);
            toggleSpinner(true);

            Ajax.call([{
                methodname: 'block_sharedwithme_render_courses_block',
                args: {
                    perpage: perpage
                },
                done: function (response) {
                    toggleSpinner(false);
                    if (response.status) {
                        $(selector.mainBlock).html(response.content);
                    }
                },
                fail: Notification.exception
            }]);
        };

        return {

            init: function () {

                var root = $(selector.wrapper);

                // First run.
                inView('.block-social-newcourses-inview')
                    .on('enter', function (e){
                        if(!$(e).hasClass('inview-done')){
                            $(e).addClass('inview-done');

                            var perpage = $('#block-sharedwithme-selectSort .dropdown-menu .active').data('value');
                            renderBlock(perpage);
                        }
                    })
                    .on('exit', el => {
                    });

                root.on('click', function (e) {
                    var target = $(e.target);
                    while ($.contains(root[0], target[0])) {
                        if (target.data('handler') === 'setPerpage') {

                            $(selector.selectItems).removeClass('active');
                            $(target).addClass('active');

                            renderBlock(target.data('value'));
                            return;
                        }

                        target = target.parent();
                    }
                });
            }
        };
    });
