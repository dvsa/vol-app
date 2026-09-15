$(function() {

  "use strict";

  // get a nicer alias for our form helper
  var F = OLCS.formHelper;

  // cache some input lookups
  var hasEmailElement = F("data", "hasEmail");

  F.selectRadio("data", "hasEmail", "Y");

  var hasEmail = function () {
    return hasEmailElement.filter(":checked").val() !== "N";
  };

  var hasEmailNo = function () {
    return hasEmailElement.filter(":checked").val() == "N";
  };

  // set up a cascade form with the appropriate rules
  OLCS.cascadeForm({
    form: "form",
    cascade: false,
    rulesets: {
      "data": {
        "username": hasEmail,
        "emailAddress": hasEmail,
        "emailConfirm": hasEmail,
        "selector:.tm-guidance-email": hasEmail,
        "selector:.tm-guidance-no-email": hasEmailNo
      }
    },
    submit: false
  });
});
