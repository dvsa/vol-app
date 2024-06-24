var OLCS = OLCS || {};

/**
 * Query string
 */

OLCS.queryString = (function(document, $, undefined) {

  "use strict";

  var exports = {};

  exports.parse = function(str) {
    var queryParams = {};
    var index = str.indexOf("?");

    if (index === -1) {
      return queryParams;
    }

    var queryParts = str.substr(index + 1).split("&");

    for (var i = 0, j = queryParts.length; i < j; i++) {
      var part = queryParts[i].split("=");

      // allow for empty params like ?foo&bar to still be truthy
      if (part.length === 1) {
        part.push("");
      }
      queryParams[part[0]] = part[1];
    }

    return queryParams;
  };

  return exports;

}(document, window.jQuery));
