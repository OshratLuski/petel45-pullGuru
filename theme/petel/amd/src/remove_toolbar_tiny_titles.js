/**
 * Removes the native browser tooltips (title) from TinyMCE toolbar groups.
 */
export const init = () => {
    function removeToolbarTinyTitles() {
        document.querySelectorAll('.tox-toolbar__group[title]').forEach(function(el) {
            el.removeAttribute('title');
        });
    }

    removeToolbarTinyTitles();

    const observer = new MutationObserver(removeToolbarTinyTitles);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
};
