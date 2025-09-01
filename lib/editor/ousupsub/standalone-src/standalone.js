// We encapsulate all our JavaScript inside an anonymouse function which just
// returns the init_ousupsub function that we want to be available.
(function () {

%%yuilibraries%%

%%ousupsubcode%%

M = {
    str: %%langstrings%%,
    iconRootUrl: null, // Set in a minute.
    util: {
        pending_js: [],
        image_url: function (imageName) {
            return M.iconRootUrl + imageName.replace("e/", "/") + ".svg";
        },
        get_string: function(identifier, component, a) {
            var stringvalue;

            if (!M.str.hasOwnProperty(component) || !M.str[component].hasOwnProperty(identifier)) {
                stringvalue = '[[' + identifier + ',' + component + ']]';
                if (M.cfg.developerdebug) {
                    console.log('undefined string ' + stringvalue, 'warn', 'M.util.get_string');
                }
                return stringvalue;
            }

            stringvalue = M.str[component][identifier];

            if (typeof a === 'undefined') {
                // no placeholder substitution requested
                return stringvalue;
            }

            if (typeof a === 'number' || typeof a === 'string') {
                // replace all occurrences of {$a} with the placeholder value
                stringvalue = stringvalue.replace(/\{\$a\}/g, a);
                return stringvalue;
            }

            if (typeof a === 'object') {
                // replace {$a->key} placeholders
                for (var key in a) {
                    if (typeof a[key] != 'number' && typeof a[key] != 'string') {
                        if (M.cfg.developerdebug) {
                            console.log('invalid value type for $a->' + key, 'warn', 'M.util.get_string');
                        }
                        continue;
                    }
                    var search = '{$a->' + key + '}';
                    stringvalue = stringvalue.replace(search, a[key]);
                }
                return stringvalue;
            }

            if (M.cfg.developerdebug) {
                console.log('incorrect placeholder type', 'warn', 'M.util.get_string');
            }
            return stringvalue;
        }
    },
};

var thisScriptUrl = document.getElementById('ousupsubloader').getAttribute('src');
M.iconRootUrl = thisScriptUrl.substring(0, thisScriptUrl.lastIndexOf("/"));

YUI().use("moodle-editor_ousupsub-editor", function(Y) {
    window.editor_ousupsub = Y.M.editor_ousupsub;
});

}());
