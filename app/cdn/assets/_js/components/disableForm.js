var OLCS = OLCS || {};

/**
 * Disable form
 *
 * Tools to provide disabling buttons and changing submit button
 * text when form is submitted
 *
 * grunt test:single --target=disableForm
 */

OLCS.disableForm = (function(document, $, undefined) {

  'use strict';

  return function init(custom) {

    var options = $.extend({
      container: '.actions-container',
      actions: '[type="submit"]:not(.js-disable-crud), [class*="action-"]:not(.js-disable-crud)',
      disabledClass: 'disabled',
      loadingText: false
    }, custom);

    $(options.container).each(function() {

      var container = $(this);
      var actions = container.find(options.actions);

      // When any action is clicked
      actions.on('click', function() {

        var target = $(this);

        // Add disabled class to relevant actions
        actions.addClass(options.disabledClass);

        // Add class to signify that being disabled is only temporary
        actions.addClass('enabled-on-render');


        // Change target button text during interim
        if (options.loadingText) {
          if (target.is('input')) {
            target.val(options.loadingText);
          } else {
            target.html(options.loadingText);
          }
        }

      });

      // Revert any buttons that were disabled using this plugin,
      // ignoring any buttons that were previously disabled
      OLCS.eventEmitter.on('render', function() {
        $('.enabled-on-render').removeClass(options.disabledClass + ' enabled-on-render');
      });

    });

  };

}(document, window.jQuery));