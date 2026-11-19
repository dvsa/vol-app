var OLCS = OLCS || {};

/**
 * Accessibility
 *
 * Various tools and helpers to aid with accessibility
 */

OLCS.accessibility = (function (document, $, undefined) {
  "use strict";

  return function init(custom) {
    var options = $.extend(
      {
        skipTrigger: "#skipToContent",
        skipTarget: "#main",
      },
      custom,
    );

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
      $(options.skipTarget).attr("tabIndex", -1).focus();
    });
  };
})(document, window.jQuery);
