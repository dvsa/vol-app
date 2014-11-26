OLCS.ready(function() {
  "use strict";

  OLCS.cascadeForm({
    form: "form",
    rulesets: {
      "details": {
        "*": function() {
          return true;
        },
        "received": function() {
          return [
            "fpm_card_offline",
            "fpm_card_online"
          ].indexOf(
            OLCS.formHelper("details", "paymentType").val()
          ) === -1;
        }
      }
    }
  });
});
