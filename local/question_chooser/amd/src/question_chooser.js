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
 * A javascript module to handle toggling quiz chooser recommendations.
 *
 * * @package local_question_chooser
 *  * @copyright 2022 Devlion.co
 *  * @author Devlion
 *  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(
    [
        'jquery',
        'core/ajax',
        'core/notification'
    ],
    function ($, Ajax, Notification) {


        /**
         * Toggle favorite quiz
         * @param  {String} qtype The type of the quiz
         * @method toggleQtypeFavorites
         */
        function toggleQtypeFavorites(qtype) {
            let obj = $('.favorite-tab').find('.item_favorite_'+qtype);

            $(obj).toggleClass('d-none d-flex');
            $(obj).find('.icon').toggleClass('fa-star-o fa-star');

            $('.all-tab').find('.item_'+qtype+' .icon').toggleClass('fa-star-o fa-star');
        }

        function hideRecomendationIfNull() {
            let counter = 0;
            let objects = $('.recommend-tab .options .alloptions fieldset').find('.option');
            $(objects).each(function() {
                if(!$(this).find('label').hasClass('d-none')){
                    counter++;
                }
            });

            if(counter === 0) {
                $('[data-region="recommend-tab-nav"]').hide();
            }
        }

        return {
            init: function () {

                hideRecomendationIfNull();

                $(document).on('click', '.qfavorites', function () {
                    let qtype = $(this).data('name');

                    toggleQtypeFavorites(qtype);

                    Ajax.call([{
                        methodname: 'local_question_chooser_save_qtypes_favorites',
                        args: {
                            qtypes: qtype
                        },
                        fail: Notification.exception
                    }]);
                });
            }
        };
    });