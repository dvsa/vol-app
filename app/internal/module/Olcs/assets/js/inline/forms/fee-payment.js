OLCS.ready(function() {
  "use strict";

  // see Olcs\Service\Data\PaymentType
  var cardField   = "fpm_card_offline";
  var chequeField = "fpm_cheque";
  var poField     = "fpm_po";

  function isNotCard() {
    return OLCS.formHelper("details", "paymentType").val() !== cardField;
  }

  OLCS.cascadeForm({
    form: "form",
    rulesets: {
      "details": {
        "*": function() {
          return true;
        },
        "received": isNotCard,
        "date:receiptDate": isNotCard,
        "payer": isNotCard,
        "slipNo": isNotCard,
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
