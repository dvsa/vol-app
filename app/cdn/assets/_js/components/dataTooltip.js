var OLCS = OLCS || {};

/**
 * Data tooltip
 *
 * Dynamically creates a tooltip based off the 'data-tooltip'
 * data attribute.
 */

OLCS.dataTooltip = (function(document, $, undefined) {

  'use strict';

  return function init() {

    // iterate through each occurance
    $('[data-tooltip]').each(function() {

      // get the content of the attribute to act as the tooltip content
      var $tooltipContent = $(this).attr('data-tooltip');

      // add parent tooltip class to target element
      $(this).addClass('tooltip-parent');

      // create the child HTML within the target element
      $(this).append('<div class="tooltip">' + $tooltipContent + '</div>');

    });

  };

}(document, window.jQuery));