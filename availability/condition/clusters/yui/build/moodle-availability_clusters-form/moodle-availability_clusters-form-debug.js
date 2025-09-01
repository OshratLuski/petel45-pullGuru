YUI.add('moodle-availability_clusters-form', function (Y, NAME) {

/**
 * JavaScript for form editing clusters conditions.
 *
 * @module moodle-availability_clusters-form
 */
M.availability_clusters = M.availability_clusters || {};

/**
 * @class M.availability_clusters.form
 * @extends M.core_availability.plugin
 */
M.availability_clusters.form = Y.Object(M.core_availability.plugin);

/**
 * Groups available for selection (alphabetical order).
 *
 * @property clusterss
 * @type Array
 */
M.availability_clusters.form.clusters = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} clusters Array of objects containing clustersid => name
 */
M.availability_clusters.form.initInner = function(clusters) {
    this.clusters = clusters;
};

M.availability_clusters.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<label><span class="p-r-1">' + M.util.get_string('title', 'availability_clusters') + '</span> ' +
            '<span class="availability-clusters">' +
            '<select name="id" class="custom-select">' +
            '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    for (var i = 0; i < this.clusters.length; i++) {
        var clusters = this.clusters[i];
        // String has already been escaped using format_string.
        html += '<option value="' + clusters.id + '">' + clusters.name + '</option>';
    }
    html += '</select></span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined &&
                node.one('select[name=id] > option[value=' + json.id + ']')) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 'any');
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_clusters.form.addedEvents) {
        M.availability_clusters.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_clusters select');
    }

    return node;
};

M.availability_clusters.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = 'choose';
    } else {
        value.id = parseInt(selected, 10);
    }
};

M.availability_clusters.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check clusters item id.
    if (value.id && value.id === 'choose') {
        errors.push('availability_clusters:error_selectclusters');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
