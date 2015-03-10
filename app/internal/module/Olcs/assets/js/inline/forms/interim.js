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
      "vehicles": showForm
    }
  });
});
