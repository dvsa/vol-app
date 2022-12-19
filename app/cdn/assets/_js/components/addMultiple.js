var OLCS = OLCS || {};

/**
 * Add multiple
 *
 * Uses the Laminas method for form collections, using the template generated instead of
 * just duplicating the fieldset as 'addAnother' does
 */
OLCS.addMultiple = (function (document, $) {
    'use strict';

    return function init(custom) {
        var options = $.extend({
            container: '.add-multiple',
            triggerSelector: '.hint button[type="submit"]',
            removeTriggerSelector: '.remove-link a',
            targetSelector: 'span[data-template]'
        }, custom);

        OLCS.eventEmitter.once('render', function () {
            // Run the plugin on each container
            $(options.container).each(function () {
                var container = $(this);
                var triggerSelector = container.find(options.triggerSelector);

                triggerSelector.on('click', function (e) {
                    var template = container.find(options.targetSelector).data('template');

                    var maxFieldsetID = 0;

                    container.find('> fieldset').each(function () {
                        var thisIndex = $(this).data('group').split('[')[1].split(']')[0];
                        maxFieldsetID = Math.max(maxFieldsetID, thisIndex);
                    });

                    template = template.replace(/__index__/g, maxFieldsetID + 1);
                    container.find(options.targetSelector).before(template);

                    e.preventDefault();
                });

                container.on('click', options.removeTriggerSelector, function(e) {
                    $(this).parents(options.container + ' > fieldset').remove();
                    e.preventDefault();
                });
            });
        });
    };
}(document, window.jQuery));
