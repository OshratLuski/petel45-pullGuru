define([
    'core/str'
], function (str) {
    `use strict`;

    const loadingSpinner = {

        body: document.querySelector(`body`),
        text: `אנא המתן`,

        show: function () {

            if (document.querySelector(`.loading`)) return

            const loading = document.createElement(`div`);

            loading.classList.add(`loading`);
            loading.style.cssText = `
              position: fixed;
              z-index: 10000;
              width: 200px;
              height: 200px;
              right: calc(50% - 100px);
              border: 1px solid rgb(221, 221, 221);
              top: calc(50% - 100px);
              background-color: rgb(255, 255, 255);
              background-image: url(${M.cfg.wwwroot}/local/community/plugins/social/pix/loading_icon.svg);
              background-repeat: no-repeat;
              background-position: 50% 30%;
              background-size: 50%;
              border-radius: 5px;
              color: rgb(63, 28, 122);
              display: flex;
              padding-top: 114px;
              font-style: normal;
              font-variant: normal;
              font-weight: 400;
              font-stretch: normal;
              font-size: 26px;
              line-height: normal;
              font-family: assistant;
              justify-content: center;
              align-items: center;
              box-shadow: 0px 0px 10px 0px #7b7b7b;
            `;
            loading.innerHTML = this.text;
            this.body.appendChild(loading);
        },

        remove: function () {
            if (document.querySelector(`.loading`)) {
                document.querySelector(`.loading`).remove();
            }
        }
    }

    return loadingSpinner;
});
