$(function() {
  "use strict";

  function requiresInformation() {
    return OLCS.formHelper("data").find("[type=radio][value=Y]:checked").length > 0;
  }

  OLCS.cascadeForm({
    form: "form",
    cascade: false,
    rulesets: {
      "data": {
        "*": function() {
          return true;
        },
        "parent:.js-financial-history": requiresInformation,
        "selector:#file": requiresInformation
      }
    }
  });
});
