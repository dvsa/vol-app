$(function() {
  "use strict";

  OLCS.cascadeForm({
    cascade: false,
    form: "#lva-safety-providers",
    rulesets: {
      'not-applicable' : {
        "selector:.checkbox > .hint": function() {
          return OLCS.formHelper.isChecked("data", "isExternal");
        }
      },
    }
  });
});