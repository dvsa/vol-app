$(function() {
  "use strict";

  function showForm() {
    return OLCS.formHelper.isChecked("requested", "interimRequested") || !OLCS.formHelper.findInput("requested", "interimRequested").length;
  }

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "data": showForm,
      "operatingCentres": showForm,
      "vehicles": showForm,
      "form-actions": {
        "selector:#grant": showForm
      }
    }
  });

  // actually we are not handling the table, just showing modal after button cicked
  OLCS.tableHandler({
    selector: "#grant",
    bodySelector: ".js-body"
  });
});
