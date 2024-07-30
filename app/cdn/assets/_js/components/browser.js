var OLCS = OLCS || {};


/**
 * Browser
 *
 * Browser sniffing used by the internal
 */

OLCS.browser = (function(document, $, undefined) {

  "use strict";

  var exports = {};

  exports.isOpera = (!!window.opera || navigator.userAgent.indexOf(" OPR/") >= 0);

  exports.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") > -1);

  exports.isSafari = (/constructor/i.test(window.HTMLElement) ||
      (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })
      (typeof window.safari !== "undefined" && typeof window.safari.pushNotification !== "undefined"));

  exports.isChrome = (!!window.chrome && !exports.isOpera && /Google Inc/.test(navigator.vendor));

  exports.isIE = (/*@cc_on!@*/false || !!document.documentMode);

  return exports;

}(document, window.jQuery));
