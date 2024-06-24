var OLCS = OLCS || {};

/**
 * Form handler
 *
 * A simple component to listen for form submissions and
 * make them asynchronous by using OLCS.submitForm to submit them.
 */

OLCS.formHandler = (function(document, $, undefined) {

  "use strict";

  return function init(options) {

    var selector = options.form;
    var isModal = options.isModal || false;
    var success = options.success;
    var keepModalOpen = false;

    if($(selector).find("#generate-document").length > 0){
      keepModalOpen = true;
    }

    // need the strict check because onChange can be passed in as false
    var onChange = options.onChange !== undefined ? options.onChange : function() {
      $(this).submit();
    };

    var submitButton = options.submit || $(selector).find("[type=submit]");
    var actionSelector = selector + " [type=submit]";

    if (!success) {
      // if the user didn't pass a success callback then we assume they
      // used the filter & container shorthand instead. As such, construct
      // a response filter (which internally normalises the response first)
      // to simply replace the container with the new filtered HTML
      success = OLCS.filterResponse(options.filter, options.container);
    }

    // we'll return this so consumers can unbind listeners if they want to
    var handler = {
      unbind: function() {
        /**
         * @NOTE: it is crucial that *all* listeners are unbound here, so
         * when adding any `on` calls, make sure you add the
         * corresponding `off` too
         */
        $(document).off("submit", selector);

        $(document).off("click", actionSelector);

        if (onChange) {
          $(document).off("change", selector);
        }
      }
    };

    var F = OLCS.formHelper;

    if (options.hideSubmit) {
      $(submitButton).hide();
    }

    if (onChange) {
      $(document).on("change", selector, function(e) {
        var form = $(selector);
        onChange.call(form, e);
      });
    }

    // we need to hook into click events to make sure we set the
    // correct input name when submitting the form via AJAX. Normally
    // these don't get set, but some backend logic acts based on
    // which button was clicked
    $(document).on("click", actionSelector, function(e) {

      var form   = $(this).parents(selector);
      var button = $(this);

      F.pressButton(form, button);

      // don't interfere with a normal submit on a multipart form; remove
      // the submit handler and let the click event happen normally
      if (form.attr("enctype") === "multipart/form-data") {

        // got any file inputs populated?
        var isDirty = false;

        $.each(form.find("input[type=file]"), function(i, e) {
          if ($(e).val() !== "") {
            isDirty = true;
          }
        });

        // if the user has pressed upload we *always* want to unbind
        // otherwise if any file inputs have values and the form has been
        // submitted, unbind too
        if (F.buttonPressed(form, "[upload]") || (isDirty && F.buttonPressed(form, "[submit]"))) {
          handler.unbind();
          return;
        }
      }

      e.preventDefault();

      // make sure we don't try and submit cancel buttons
      if (isModal && F.buttonPressed(form, "[cancel]")) {
        OLCS.eventEmitter.emit("modal:cancel");
        OLCS.logger.debug("trapped 'cancel' click inside modal, won't submit form", "formHandler");
        return;
      }

      form.submit();
    });

    // bind a simple submit handler to send the form via * AJAX
    $(document).on("submit", selector, function(e) {
      e.preventDefault();

      OLCS.logger.debug("submitting form '" + selector + "'", "formHandler");

      var form = $(this);

      OLCS.submitForm({
        form: form,
        success: success,
        disable: options.disable,
        keepModalOpen: keepModalOpen,
        complete: function() {
          OLCS.eventEmitter.emit("update:" + options.container);
        }
      });
    });

    // callers of this component might want to manually unbind the listeners
    // we've bound to it, so we need to return a wrapped object which lets
    // them do so
    return handler;
  };

}(document, window.jQuery));
