define([
    'jquery',
    'theme_petel/jquery.splitter'
], function($) {

    var hidepos = 0;
    var prevpos = 0;
    var pos1 = 0;
    var pos = 0;

    var splitter = $('#splitpanel')
        .split({
            orientation: 'vertical',
            limit: 0,
        });

    $(document).on('mousedown', '.vsplitter', (e) => {
        e.stopPropagation();
        e.preventDefault();
        pos1 = Math.round(splitter.position()[0]);
    });

    $(document).on('mouseup', '.vsplitter', (e) => {
        e.stopPropagation();
        e.preventDefault();
        pos = Math.round(splitter.position()[0]);
        if (pos === pos1) {
            if (pos !== hidepos) {
                splitter.position(hidepos);
            } else {
                splitter.position(prevpos);
            }
        }
        prevpos = pos;
    });

    prevpos = Math.round(splitter.position()[0]);

    let pageAsideSwitcherBtn = $('.page-aside-switch-lg');
    let hamburgerBtn = $('.btn.nav-link.hamburger-btn');

    if (pageAsideSwitcherBtn.length > 0) {
        pageAsideSwitcherBtn.click(function() {
            splitter.refresh();
        });
    }

    if (hamburgerBtn.length > 0) {
        hamburgerBtn.click(function() {
            for (let i = 1; i < 20; i++) {
                setTimeout(function() {
                    splitter.refresh();
                }, i * 30);
            }
        });
    }

});
