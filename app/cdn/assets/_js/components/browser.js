var OLCS = OLCS || {};


/**
 * Browser
 *
 * Browser sniffing used by the internal
 */

OLCS.browser = (function(document, $, undefined) {

  "use strict";

  var exports = {};

  exports.isOpera = !!window.opera || navigator.userAgent.indexOf(" OPR/") >= 0;

  exports.isFirefox = typeof InstallTrigger !== "undefined";

  exports.isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf("Constructor") > 0;

  exports.isChrome = !!window.chrome && !exports.isOpera;

  exports.isIE = /*@cc_on!@*/false || !!document.documentMode;

  return exports;

}(document, window.jQuery));
