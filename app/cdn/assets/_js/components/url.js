var OLCS = OLCS || {};

/**
 * Url
 *
 * Contains url helper methods
 */

OLCS.url = (function(document, $, undefined) {

  'use strict';

  function addTrailingSlash(url) {

    url = OLCS.URI(url);
    var path = url.path();

    var lastChar = path.substr(path.length - 1);
    if(lastChar !== '/') {
      path += '/';
    }

    url.path(path);
    return url.toString();

  }

  var exports = {

    isSame: function(url1, url2) {

      url1 = addTrailingSlash(url1);
      url2 = addTrailingSlash(url2);

      return url1 === url2;
    },

    isCurrentPage: function(url1) {
      url1 = OLCS.URI(url1).absoluteTo(window.location).fragment('').normalize().toString();
      var url2 = OLCS.URI(window.location).fragment('').normalize().toString();
      return exports.isSame(url1, url2);
    },

    load: function(url) {
      OLCS.stopEnableButton = true;
      window.location.href = url;
    }
  };

  return exports;

}(document, $));
