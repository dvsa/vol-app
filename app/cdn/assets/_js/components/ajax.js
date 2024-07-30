var OLCS = OLCS || {};

/**
 * Ajax
 *
 * Simple wrapper component around jQuery.ajax which allows better
 * request and response introspection
 */

OLCS.ajax = (function(document, $, undefined) {

  "use strict";

  var lastRequestId = 0;

  //This will hold a date object to compare POST timestamps
  var lastTimestamp = 0;

   // If we trap two POSTs within this number of msec we'll throw
  // a warning
  var minPostThreshold = 50;

  return function ajax(options) {

    var requestId = ++lastRequestId;

    // although jQuery doesn't care about missing defaults, we do
    // for the purposes of logging
    options = $.extend({
      method: "GET"
    }, options);

    var finalOptions = $.extend({}, options, {
      beforeSend: function(jqXHR, settings) {
        var method = options.method.toUpperCase();
        var postData;
        var submitTimestamp = new Date();
        var since = submitTimestamp - lastTimestamp;

        OLCS.ajaxError.removeError();

        OLCS.logger
          .group(method + " " + options.url)
          .log("Request ID " + requestId + ": start");

        if (method === "POST") {
          if (since < minPostThreshold) {
            OLCS.logger.error("Possible duplicate POST detected - time since last request is " + since + "ms");
          }
          OLCS.logger.group("Request data");
          if (settings.data === "") {
            OLCS.logger.warn("No POST data - is this correct?");
          } else {
            postData = OLCS.queryString.parse("?" + decodeURI(settings.data));
            for (var key in postData) {
              OLCS.logger.log(key + ": " + postData[key]);
            }
          }
          OLCS.logger.groupEnd();
        }

        lastTimestamp = new Date();

        if (options.beforeSend) {
          options.beforeSend.apply(null, arguments);
        }

        OLCS.preloader.show(options.preloaderType);

      },
      success: function(data, textStatus, jqXHR) {
        OLCS.logger
          .log("Request ID " + requestId + ": end (" + jqXHR.status + " " + textStatus + ")")
          .groupEnd();

        if (options.success) {
          options.success.apply(null, arguments);
        }
      },
      error: function(jqXHR, testStatus, errorThrown) {
        OLCS.logger
          .warn("Request ID " + requestId + ": " + errorThrown)
          .groupEnd();

        if (options.error) {
          options.error.apply(null, arguments);
        } 
        OLCS.ajaxError.showError();
        OLCS.preloader.hide();
      },
      // this fires *after* success or error
      complete: function() {
        if (options.complete) {
          options.complete.apply(null, arguments);
        }
      }
    });

    return $.ajax(finalOptions);
  };

}(document, window.jQuery));
