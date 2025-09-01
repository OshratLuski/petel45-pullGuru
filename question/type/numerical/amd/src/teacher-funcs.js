define(['jquery'], function($) {
    'use strict';

    /**
     *
     */
    function changeSelectUnit() {

        var flag = 0;
        $('input[name^="unitvalue"]').each(function() {

            var str = $.trim($(this).val());

            if (str.length !== 0) {
                flag = 1;
            }
        });


        if (flag) {
            $('select[name="unitrole"]').val("1");
        } else {
            $('select[name="unitrole"]').val("3");
        }

    }

    const teacherFuncs = {

        init: () => {

            // // Default state.
            // changeSelectUnit();
            //
            //  $('select[name="unitrole"]').prop('disabled', true);
            //
            // $('input[name^="unitvalue"]').on('input', function() {
            //     changeSelectUnit();
            // });

        }
    };

    return teacherFuncs;
});
