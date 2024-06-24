var OLCS = OLCS || {};

/**
 * Form helper
 */

OLCS.formHelper = (function(document, $, undefined) {

  "use strict";

  // the class we apply to a hidden input used to simulate 
  // which button was clicked when submitting a form
  var formClickAction = "form__action";

  var errorSelectors = [
    ".validation-summary",
    ".validation-wrapper"
  ];

  var warningSelector = ".notice--warning";

  // Expose a jQuery-esque function which tries to work
  // out which actual public property to invoke purely
  // based off argument length.
  var exports = function() {
    switch (arguments.length) {
      case 1:
        return exports.fieldset.apply(null, arguments);
      case 2:
        return exports.input.apply(null, arguments);
    }
  };

  // public interface
  exports.fieldset = function(selector) {
    selector = selector
      .replace("[", "\\[")
      .replace("]", "\\]");
    return $("html").find("fieldset[data-group='" + selector + "']");
  };

  exports.input = function(fieldset, name) {
    fieldset = fieldset
      .replace("[", "\\[")
      .replace("]", "\\]");
    return $("html").find("[name=" + fieldset + "\\[" + name + "\\]]");
  };

  exports.findInput = function(fieldset, name) {
    return exports
    .fieldset(fieldset)
    .find("[name*=\\[" + name + "\\]]");
  };

  exports.pressButton = function(form, button) {
    var actionValue = button.val();
    var actionName  = button.attr("name");
    form.find("." + formClickAction).remove();
    form.prepend("<input class='" + formClickAction + "' type=hidden name='" + actionName + "' />");
    form.find("." + formClickAction).val(actionValue);
  };

  exports.buttonPressed = function(form, name) {
    var actionName = form.find("." + formClickAction).attr("name");
    return (typeof actionName === "string" && actionName.indexOf(name) !== -1);
  };

  exports.isChecked = function(fieldset, name, value) {
    if (value === undefined) {
      value = "Y";
    }

    return exports.input(fieldset, name)
    .filter(":checked")
    .val() === value;
  };

  exports.isSelected = function(fieldset, name, value) {
    return exports.input(fieldset, name)
      .val() === value;
  };

  exports.containsErrors = function(payload) {
    for (var i = 0, j = errorSelectors.length; i < j; i++) {
      if (exports.containsElement(payload, errorSelectors[i])) {
        return true;
      }
    }

    return false;
  };

  exports.containsWarnings = function(payload) {
    return exports.containsElement(payload, warningSelector);
  };

  exports.containsElement = function(payload, selector) {
    if (typeof payload === "string") {
      // assume the payload needs a container if it's just a string
      payload = $("<div>" + payload + "</div>");
    }

    return payload.find(selector).length > 0;
  };

  exports.clearErrors = function(context) {
    // context can be null, hence why we don't use $(context).find()
    $(".validation-summary", context).remove();
    $(".validation-wrapper ul:first-child", context).remove();
    $(".validation-wrapper", context).removeClass("validation-wrapper");
  };

  exports.render = function(container, body) {
    // the fact we redraw means we sometimes lose our scroll position;
    // so cache it and re-apply it immediately after render
    var scrollTop = $(window).scrollTop();
    $(container).html(body);
    $(window).scrollTop(scrollTop);

    OLCS.eventEmitter.emit("render");
  };

  exports.renderModalTitle = function(title) {
    if ($("#modal-title").length > 0) {
      $("#modal-title").html(title);
    }
  };

  exports.selectRadio = function(fieldset, name, value) {
    exports.input(fieldset, name)
    .filter("[value='"+value+"']")
    .prop("checked", true)
    .change();
  };

  return exports;

}(document, window.jQuery));
