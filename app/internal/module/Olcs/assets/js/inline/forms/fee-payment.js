OLCS.ready(function() {
  "use strict";

  var cardFields = [
    "fpm_card_offline",
    "fpm_card_online"
  ];

  OLCS.cascadeForm({
    form: "form",
    rulesets: {
      "details": {
        "*": function() {
          return true;
        },
        "received": function() {
          return cardFields.indexOf(OLCS.formHelper("details", "paymentType").val()) === -1;
        }
      }
    }
  });
});
