var OLCS = OLCS || {};

/**
 * Transform
 */

OLCS.transform = (function(document, $, undefined) {

  'use strict';

  return function init(options) {
    var target = $(options.selector);

    if (target.length === 0) {
      return;
    }

    for (var search in options.replace) {
      var replace = options.replace[search];
      if (target.find(search).length) {
        target.removeClass(options.selector.substring(1));
        target.addClass(replace.substring(1));
      }
    }
  };

}(document, window.jQuery));
