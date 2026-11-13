var OLCS = OLCS || {};

/**
 * Keep GOV.UK error links from using a nested question's legend as the
 * label for another input in an unlabelled layout fieldset.
 */
OLCS.errorSummary = function (ErrorSummary) {
  "use strict";

  var getAssociatedLegendOrLabel = ErrorSummary.prototype.getAssociatedLegendOrLabel;

  ErrorSummary.prototype.getAssociatedLegendOrLabel = function (input) {
    var label = getAssociatedLegendOrLabel.call(this, input);

    if (label && label.tagName === "LEGEND" && label.parentElement !== input.closest("fieldset")) {
      return input.labels && input.labels.length ? input.labels[0] : input.closest("label");
    }

    return label;
  };

  OLCS.eventEmitter.on("render", function (context) {
    if (!context) {
      return;
    }

    context.find('[data-module="govuk-error-summary"]:not([data-govuk-error-summary-init])').each(function () {
      return new ErrorSummary(this);
    });
  });
};
