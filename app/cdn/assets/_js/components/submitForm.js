var OLCS = OLCS || {};

/**
 * Submit form
 *
 * Tiny wrapper to submit a form via AJAX. Ensures
 * that the ajax submission matches the form's method
 * and action etc
 *
 * @NOTE: invoking this component *actually submits* the
 * form in question; it does NOT set up listeners which
 * at some indeterminate point will then submit it
 *
 * If that's what you're after, please use OLCS.formHandler instead
 */

OLCS.submitForm = (function(document, $, undefined) {

  "use strict";

  return function submit(options) {

    var form = options.form;
    var success = options.success;
    var error = options.error || function(/*jqXHR, status, err*/) {
      // no-op
    };
    var disableOnSubmit = options.disable !== undefined ? options.disable : true;

    // cache the form data before we disable it, otherwise
    // we'll serialize nothing
    var data = form.serialize();

    var enabledElements;
    var url;
    var preloaderType;

    if (disableOnSubmit) {
      enabledElements = form.find(":input").not(":disabled");
      enabledElements.attr({
        "disabled"    : true,
        "aria-hidden" : true
      });
    }

    function complete() {
      if (options.complete) {
        options.complete();
      }
      if (disableOnSubmit  && !OLCS.stopEnableButton) {
        enabledElements.removeAttr("disabled", "aria-hidden");
      }
    }

    url = form.attr("action");
    if (!url) {
      OLCS.logger.debug(
        "form has no action attribute, using current path",
        "submitForm"
      );
      url = window.location.pathname;
    }

    if ($(form).hasClass("filters")) {
      preloaderType = "table";
    } else if (data.indexOf("table") >= 0) {
      preloaderType = "modal";
    }

    if (data.match(/(=search|=select)/)) {
      preloaderType = "inline";
    }

    return OLCS.ajax({
      url: url,
      method: form.attr("method"),
      data: data,
      success: success,
      keepModalOpen: options.keepModalOpen || false,
      error: error,
      complete: complete,
      preloaderType: preloaderType
    });
  };

}(document, window.jQuery));