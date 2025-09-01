define([
        'jquery',
        'quiz_assessmentdiscussion/preset',
        'quiz_assessmentdiscussion/jquery.splitter',
    ],
    function ($, Preset) {

        const actionSplitter = () => {
            let leftwidth = $('#leftPanel').width();
            let rightwidth = $('#rightPanel').width();
            let width = leftwidth + rightwidth;
            let splitterwidth = (rightwidth / width) * 100;

            Preset.set('splitter_width', splitterwidth);
        };

        return {

            'init': function () {

                let preset = Preset.get();
                let position = preset.splitter_width + '%';

                var splitter = $('#splitpanel')
                    .split({
                        orientation: 'vertical',
                        limit: 300,
                        position: position,
                        onDrag: () => actionSplitter()
                    });

                return true;
            }
        };
    });
