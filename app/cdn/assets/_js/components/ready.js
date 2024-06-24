var OLCS = OLCS || {};

/**
 * Ready
 */

OLCS.ready = (function(document, $, undefined) {

  "use strict";

  var cache = [];

  function isCached(fn) {
    var str = fn.toString();
    for (var i = 0, j = cache.length; i < j; i++) {
      if (cache[i] === str) {
        return true;
      }
    }
    return false;
  }

  function addCache(fn) {
    cache.push(fn.toString());
  }

  return function init(fn) {
    if (typeof fn !== "function") {
      throw new Error("Please supply a function to OLCS.ready");
    }

    if (isCached(fn)) {
      return;
    }

    addCache(fn);
    $(document).ready(fn);
  };

}(document, window.jQuery));
