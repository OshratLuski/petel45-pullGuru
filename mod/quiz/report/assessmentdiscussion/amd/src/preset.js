define(['jquery'],
    function ($) {

        const savePreset = (name, value) => {
            $('.block_assessmentdiscussion_preset #' + name).val(value);
        };

        return {

            'setDefault': function (def) {
                $('.block_assessmentdiscussion_preset').find('input').each(function(index, obj) {
                    let name = $(obj).attr('id');

                    if (def[name] !== undefined) {
                        savePreset(name, def[name]);
                    }
                })
            },

            'get': function () {
                let res = [];
                $('.block_assessmentdiscussion_preset').find('input').each(function(index, obj) {
                    let name = $(obj).attr('id');
                    res[name] = $(obj).val();
                })

                return res;
            },

            'set': function (name, value) {
                savePreset(name, value);
            },
        };
    });
