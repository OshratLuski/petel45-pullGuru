define([
    'jquery',
], function($) {
    return class Toast {
        constructor() {
            if (Toast._instance) {
                return Toast._instance;
            }
            Toast._instance = this;
        }

        toastShow(className) {
            $(`.local-diagnostic-toast.toast.${className}`).css('z-index', 5000);
            $(`.local-diagnostic-toast.toast.${className}`).toast({
                animation: true,
                delay: 2000,
                autohide: true,
            }).toast('show');
        }

        toastHandle(obj) {
            $('.toast').on(obj.method, obj.fn);
        }

    };
});