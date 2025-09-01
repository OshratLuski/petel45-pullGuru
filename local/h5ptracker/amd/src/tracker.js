define(['jquery', 'core/ajax', 'core/notification'], function ($, Ajax, Notification) {
    return {
        init: function (type, cmid) {

            var onIframeReady = function ($i, successFn, errorFn) {
                try {
                    const iCon = $i.first()[0].contentWindow,
                        bl = "about:blank",
                        compl = "complete";
                    const callCallback = () => {
                        try {
                            const $con = $i.contents();
                            if ($con.length === 0) {
                                throw new Error("iframe inaccessible");
                            }
                            successFn($con);
                        } catch (e) { // accessing contents failed
                            errorFn();
                        }
                    };
                    const observeOnload = () => {
                        $i.on("load.jqueryMark", () => {
                            try {
                                const src = $i.attr("src").trim(),
                                    href = iCon.location.href;
                                if (href !== bl || src === bl || src === "") {
                                    $i.off("load.jqueryMark");
                                    callCallback();
                                }
                            } catch (e) {
                                errorFn();
                            }
                        });
                    };
                    if (iCon.document.readyState === compl) {
                        const src = $i.attr("src").trim(),
                            href = iCon.location.href;
                        if (href === bl && src !== bl && src !== "") {
                            observeOnload();
                        } else {
                            callCallback();
                        }
                    } else {
                        observeOnload();
                    }
                } catch (e) { // accessing contentWindow failed
                    errorFn();
                }
            };

            $(document).ready(function() {

                let selector = '';
                switch (type) {
                    case 'h5pactivity':
                        selector = '.h5p-player';
                        break;
                    default:
                        selector = '.h5p-iframe';
                        break;
                }
                var parentiFrame = $(selector);
                onIframeReady(parentiFrame, function ($contents) {
                    // Check for H5P iFrame.
                    var iFrame = type === 'h5pactivity' ? $contents.find('.h5p-iframe') : parentiFrame;
                    if (!iFrame[0] || !iFrame[0].contentWindow) {
                        return;
                    }
                    var H5P = iFrame[0].contentWindow.H5P;

                    // Check for H5P instances.
                    if (!H5P || !H5P.instances || !H5P.instances[0]) {
                        return;
                    }

                    let iframeVideo = H5P.instances[0].video;
                    iframeVideo.on('stateChange', function (event) {
                        let seconds = iframeVideo.getCurrentTime();
                        let action = 'undefined';
                        switch (event.data) {
                            case H5P.Video.ENDED:
                                action = 'ended';
                                break;

                            case H5P.Video.PLAYING:
                                action = 'playing';
                                break;

                            case H5P.Video.PAUSED:
                                action = 'paused';
                                break;
                        }

                        Ajax.call([{
                            methodname: 'local_h5ptracker_track_actions',
                            args: {
                                cmid: cmid,
                                action: action,
                                seconds: seconds
                            }
                        }])[0].fail(Notification.exception);
                    });

                    iframeVideo.on('playbackRateChange', function (event) {
                        let seconds = iframeVideo.getCurrentTime();
                        let action = 'playbackrate';
                        Ajax.call([{
                            methodname: 'local_h5ptracker_track_actions',
                            args: {
                                cmid: cmid,
                                action: action,
                                seconds: seconds
                            }
                        }])[0].fail(Notification.exception);
                    });

                    H5P.$body.find('.ui-slider-horizontal').on('click', function () {
                        let seconds = iframeVideo.getCurrentTime();
                        let action = 'seek';
                        Ajax.call([{
                            methodname: 'local_h5ptracker_track_actions',
                            args: {
                                cmid: cmid,
                                action: action,
                                seconds: seconds
                            }
                        }])[0].fail(Notification.exception);
                    })
                });
            });
        }
    }
})