OLCS.ready(function() {
  "use strict";

  var cardField = "fpm_card_offline";

  OLCS.cascadeForm({
    form: "form",
    rulesets: {
      "details": {
        "*": function() {
          return true;
        },
        "received": function() {
          return OLCS.formHelper("details", "paymentType").val() !== cardField;
        }
      }
    }
  });
});
