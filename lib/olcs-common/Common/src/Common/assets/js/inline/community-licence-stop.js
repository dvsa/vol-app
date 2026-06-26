$(function() {
  "use strict";

  var F = OLCS.formHelper;

  function isSuspended() {
    return F.isChecked("data", "type");
  }

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "data": {
        "selector:#dates": isSuspended,
      }
    }
  });
});
