define([
    'jquery',
    'core/ajax',
    'core/notification',
    'community_sharewith/modal',
    'community_sharewith/storage'
], function($, Ajax, Notification, modal, St) {

    /** @alias module:community_sharewith/sharewithteacher */
    return {
        modalBody: '',
        addBtn: '',
        resultBlock: '',
        tagWrapper: '',
        input: '',

        init: function(modal) {
            let self = this;
            $(modal.body).on("click", 'button[data-handler="removeTag"]', function(e) {
                e.stopPropagation();
                var target = e.currentTarget;
                self.modalBody = $(target).closest('.modal-body');
                self.removeTag(target);
            });
            $(modal.body).on("click", function(e) {
                if ($('.result-block button').length > 0) {
                    $('.result-block ').html('');
                }
            });

            $(modal.body).on("input", '[data-handler="selectTeacher"]', function(e) {
                e.stopPropagation();
                var target = e.currentTarget;
                self.modalBody = $(target).closest('.modal-body');
                if (target.value.length >= 3) {
                    self.autocompleteTeachers(target);
                }
            });

        },

        showSearchResult: function(response) {
            var self = this;
            self.resultBlock.innerHTML = '';
            var teachers = JSON.parse(response);
            teachers.forEach(function(teacher) {

                var unit = document.createElement('button');
                unit.dataset.teacherid = teacher.teacher_id;
                unit.dataset.teachername = teacher.teacher_name;
                unit.dataset.handler = 'addTag';
                unit.classList.add('btn', 'btn-secondary', 'd-flex', 'mb-1', 'w-100');
                unit.setAttribute('tabindex', '0');
                unit.innerHTML = '<div class = "sw-img" >' +
                    '<img src = "' + M.cfg.wwwroot + teacher.teacher_url + '" alt = "">' +
                    '</div><span class = "pl-2">' + teacher.teacher_name + '</span>';

                $(self.resultBlock).removeClass('d-none');
                $(self.resultBlock).append(unit);
            });

            // Focus.
            self.focusOnTag();

            $('[data-handler="addTag"]').on("click", function(e) {
                e.stopPropagation();
                var target = e.currentTarget;
                self.modalBody = $(target).closest('.modal-body');
                self.addTag(target);
            });

            $('[data-handler="addTag"]').keydown(function(e) {

                let keyUp = 38;
                let keyDown = 40;

                var moves = $(self.modalBody).find(".result-block .btn");
                // Key up function
                if (e.keyCode === keyDown) {
                    e.preventDefault();
                    for (i = 0; i <= moves.length; i++) {
                        if (moves[i] === $(self.modalBody).find(".result-block .btn:focus").get(0)) {
                           $(moves[i + 1]).focus();
                            break;
                        }
                    }
                }
                if (e.keyCode === keyUp) {
                    e.preventDefault();
                    for (i = 0; i <= moves.length; i++) {
                        if (moves[i] === $(self.modalBody).find(".result-block .btn:focus").get(0)) {
                           $(moves[i - 1]).focus();
                            break;
                        }
                    }
                }
            });
        },

        focusOnTag: function() {

            let flag = false;
            $('[data-handler="addTag"]').each(function(index) {
                if ($(this).length && !flag) {
                    $(this).focus();
                    flag = true;
                }
            });
        },

        autocompleteTeachers: function(target) {
            var self = this;
            var inputValue = target.value;
            self.resultBlock = $(self.modalBody).find('.result-block');
            self.tagWrapper = $(self.modalBody).find('.tag-wrapper');
            self.input = $(self.modalBody).find('input[data-handler = "selectTeacher"]');

            if (!self.resultBlock.childElementCount && !inputValue) {
                self.resultBlock.classList.add('d-none');
            }
            Ajax.call([{
                methodname: 'community_sharequestion_autocomplete_teachers',
                args: {
                    searchstring: inputValue
                },
                done: self.showSearchResult.bind(this),
                fail: Notification.exception
            }]);
        },

        addTag: function(target) {
            var self = this;
            var teacherid = target.dataset.teacherid,
                tag = $(this.tagWrapper).find('[data-teacherid=' + teacherid + ']');
            if (tag.length) {
                this.removeTag(tag[0]);
                return;
            }
            var teacherTag = $(this.tagWrapper).find('.example').clone();
            teacherTag.attr('data-teacherid', teacherid);
            teacherTag.append('<span>' + target.dataset.teachername + '</span>');
            teacherTag.removeClass('example d-none');
            target.remove();
            $(this.tagWrapper).append(teacherTag);
            this.input.value = '';

            // Focus.
            self.focusOnTag();
        },

        removeTag: function(target) {
            var teacherid = target.dataset.teacherid;
            $(this.resultBlock).find('[data-teacherid=' + teacherid + ']')
                .removeClass('active');
            $(target).remove();
        },
    };
});
