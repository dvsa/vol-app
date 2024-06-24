var OLCS = OLCS || {};

/**
 * Accessibility
 *
 * Various tools and helpers to aid with accessibility
 */

  OLCS.accessibility = (function(document, $, undefined) {

  'use strict';

  return function init(custom) {

    var options = $.extend({
      errorContainer : '#validationSummary',
      skipTrigger    : '#skipToContent',
      skipTarget     : '#main',
      inputLabels    : '[type="radio"], [type="checkbox"], [type="file"]'
    }, custom);

    // Run the code on each "render" of the page
    OLCS.eventEmitter.on('render', function() {

      /**
       * Validation Error Messages
       *
       * Automatically set focus to and scroll to form errors
       */

      if ($(options.errorContainer).length) {
        // Make error messages container focusable and set focus
        $(options.errorContainer).attr('tabIndex', -1).focus();
        // Scroll to the error messages
        window.location.hash = options.errorContainer;
      }

      /**
       * Input labels
       *
       * Allows focus to be given to label elements which contain child
       * input elements, and removes ability to focus on said child elements
       * to prevent double tabbing
       */

      // Make input labels focusable with the tab key
      $('label:not(.disabled)').has(options.inputLabels).attr('tabindex', 0);

      // Prevent child inputs from being tab-able
      $('label').find(options.inputLabels).attr('tabindex', -1);

      // When a label is 'focused', shift focus to the child input
      $('label:not(.disabled)').has(options.inputLabels).focus(function() {
        $(this).attr('tabindex', -1);
        $(this).addClass('focused').blur().find(options.inputLabels).focus();
      });

      // When an input is blured, remove simulated focus from parent label
      $('label:not(.disabled)').find(options.inputLabels).blur(function () {
        $(this).parent('label').removeClass('focused');
        $(this).parent('label').attr('tabindex', 0);
      });

    }); // OLCS.eventEmitter

    /**
     * Skip To Main Content
     *
     * Gives focus to the content that is "skipped" to using the
     * skipToContent accessibility link
     *
     * https://code.google.com/p/chromium/issues/detail?id=262171
     * http://stackoverflow.com/questions/6280399/skip-links-not-working-in-chrome
     */

    $(options.skipTrigger).click(function () {
      $(options.skipTarget).attr('tabIndex', -1).focus();
    });

  };

}(document, window.jQuery));