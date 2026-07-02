$(function() {
  "use strict";

  function showTables() {
    return OLCS.formHelper.isChecked("data", "hasEnteredReg") || !OLCS.formHelper.findInput("data", "hasEnteredReg").length;
  }

  function showNotice() {
    if (!OLCS.formHelper.findInput("data", "hasEnteredReg").length) {
      return true;
    }
    return !OLCS.formHelper.isChecked("data", "hasEnteredReg");
  }

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "table": showTables,
      "data": {
        "selector:#notice": showNotice
      }
    }
  });

});


OLCS.ready(function() {
  "use strict";

  OLCS.toggleElement({
    triggerSelector: '.more-actions',
    targetSelector: '.more-actions__list'
  });

});