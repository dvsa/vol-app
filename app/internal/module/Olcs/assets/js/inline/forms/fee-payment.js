OLCS.ready(function() {
  "use strict";

  // see Olcs\Service\Data\PaymentType
  var cardField   = "fpm_card_offline";
  var chequeField = "fpm_cheque";
  var poField     = "fpm_po";

  OLCS.cascadeForm({
    form: "form",
    rulesets: {
      "details": {
        "*": function() {
          return true;
        },
        "received": function() {
          return OLCS.formHelper("details", "paymentType").val() !== cardField;
        },
        "receiptDate": function() {
          return OLCS.formHelper("details", "paymentType").val() !== cardField;
        },
        "payer": function() {
          return OLCS.formHelper("details", "paymentType").val() !== cardField;
        },
        "slipNo": function() {
          return OLCS.formHelper("details", "paymentType").val() !== cardField;
        },
        "chequeNo": function() {
          return OLCS.formHelper("details", "paymentType").val() == chequeField;
        },
        "poNo": function() {
          return OLCS.formHelper("details", "paymentType").val() == poField;
        }
      }
    }
  });
});
