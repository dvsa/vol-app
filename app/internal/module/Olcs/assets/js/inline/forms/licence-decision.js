$(function() {
  "use strict";

  var formId = "#LicenceStatusDecisionCurtail";

  function isChecked(result) {
    return function() {
      return OLCS.formHelper.isChecked("licence-decision-affect-immediate", "immediateAffect") === result;
    };
  }

  OLCS.cascadeForm({
    form: formId,
    cascade: false,
    rulesets: {
      "form-actions": isChecked(true),
      "licence-decision": isChecked(false)
    }
  });

  // @FIXME: a bug in how we render and then execute inline script means the
  // cascade rules above fire too early when showing in a modal. This causes
  // it to wrongly evaluate the current visible state of some elements as 'hidden'
  // when in fact they're visible but hidden by virtue of the container (the modal)
  // not yet being displayed.
  // By hooking into the show event of the modal we can force the cascade form to
  // check any changes when the form is actually displayed
  OLCS.eventEmitter.once("show:modal", function() {
    $(formId).change();
  });
});
