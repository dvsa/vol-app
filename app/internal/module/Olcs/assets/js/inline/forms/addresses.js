$(function() {
  "use strict";

  function showForm() {
    return OLCS.formHelper.isChecked("consultant", "add-transport-consultant") ||
     !OLCS.formHelper.findInput("consultant", "add-transport-consultant").length ||
     OLCS.formHelper.findInput("consultant", "add-transport-consultant")[0].disabled;
  }

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "consultant": {
        "writtenPermissionToEngage": showForm,
        "transportConsultantName": showForm,
        "selector:fieldset[data-group='consultant[contact]']": showForm,
        "selector:fieldset[data-group='consultant[address]']": showForm
      }
    }
  });
});
