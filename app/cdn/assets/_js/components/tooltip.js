var OLCS = OLCS || {};

/**
 * Tooltip
 *
 * Whilst the actual tooltip functionality is handled purely
 * with CSS, this file is required for appropriate ARIA labels
 *
 * grunt test:single --target=tooltip
 */

OLCS.tooltip = (function(document, $, undefined) {

  'use strict';

  return function init(options) {

    //Get the tooltip parent
    var parent = options.parent;

    $(parent).each(function() {

      var tooltip = $(this);

      tooltip.hover(
        function() { tooltip.attr('aria-hidden', 'false'); },
        function() { tooltip.attr('aria-hidden', 'true' ); }
      );

    });

  };

}(document, window.jQuery));