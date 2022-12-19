var OLCS = OLCS || {};

/**
 * Cascade input
 *
 * Given a source and destination input, and a process callback
 * invoked when the source value changes, apply those changes
 * to the destination input.
 */

OLCS.cascadeInput = (function(document, $, undefined) {

  "use strict";

  return function init(options) {

    var trap = options.trap === undefined ? true : options.trap;
    var disableDestination = options.disableDestination === undefined ? true : options.disableDestination;
    var loadingText = options.loadingText || "Loading...";
    var emptyLabel = options.emptyLabel || null;
    var process = options.process;
    var clearWhenEmpty = options.clearWhenEmpty || false;
    var append = options.append || false;
    var filter = options.filter || null;
    var disableSubmit = options.disableSubmit || false;

    // allow a quick shortcut; if the user passed in `url`, then
    // assume they want process to be a simple async GET
    if (options.url) {
      process = function(value, callback) {

        // If the component asks to clear the select values when the empty
        // value is chosen we short-circuit the AJAX request and invoke the
        // callback with one empty value instead
        if (value === "" && clearWhenEmpty) {
          return callback([{value: ""}]);
        }

        if(disableSubmit) {
          document.getElementById(disableSubmit).disabled = true;
          document.getElementById(disableSubmit).addClass('govuk-button--disabled');
        }

        OLCS.ajax({
          url: options.url + "/" + value,
          success: callback
        });
      };
    }

    if (!$.isFunction(process)) {
      throw new Error("Please provide a 'process' function or 'url' string");
    }

    $(document).on("change", options.source, function(e) {

      e.preventDefault();
      var destination = $(options.dest) || "";

      // make sure the event doesn't bubble up if we've askesd for it to be
      // trapped. This is useful because it prevents more generic change
      // listeners (like say a form submit) from firing prematurely
      if (trap) {
        e.stopPropagation();
      }

      function done(result) {

        var content = result;

        if(disableSubmit) {
          document.getElementById(disableSubmit).disabled = false;
          document.getElementById(disableSubmit).removeClass('govuk-button--disabled');
        }

        if (filter) {
          $(filter).remove();
          content = $(result).find(options.filter);
        }

        if (destination.attr("type") === "text") {
          destination.val(result.value);
        }

        if (destination.is("select")) {
          var str = "";
          $.each(result, function(i, r) {
            if(typeof(r) === "object"){
              if (r.value === "" && emptyLabel) {
                r.label = emptyLabel;
              }
              str += "<option value='" + r.value + "'>" + r.label + "</option>";
            }
            });

          destination.html(str);
        }

        if (append && content.length) {
          $(content).insertAfter(options.dest);
        }

        if (disableDestination) {
          destination.removeAttr("disabled");
        }

        if (trap) {
          destination.trigger("change");
        }
      }

      if (disableDestination) {
        if (destination.attr("type") === "text") {
          destination.val(loadingText);
        } else {
          destination.html("<option>" + loadingText + "</option>");
        }
        destination.attr("disabled", true);
      }


      process.call(this, $(this).val(), done);
    });
  };

}(document, window.jQuery));
