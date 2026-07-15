$(function() {
  "use strict";
  OLCS.cascadeForm({
    form: "form",
    cascade: false,
    rulesets: {
      "data[table]": function() {
        return OLCS.formHelper.isChecked("data", "question");
      }
    }
  });
});
