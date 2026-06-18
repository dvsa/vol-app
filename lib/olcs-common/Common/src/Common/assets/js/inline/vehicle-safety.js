$(function() {
  "use strict";

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "licence": {
        "*": true,
        "tachographInsName": function() {
          var input = OLCS.formHelper("licence", "tachographIns")
          .filter(":checked");

          return input.length && input.val() == "tach_external";
        }
      }
    }
  });
});
