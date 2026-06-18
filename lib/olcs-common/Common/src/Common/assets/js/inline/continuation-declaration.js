$(function () {
  "use strict";

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "formActions": {
        "selector:#sign": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "Y");
        },
        "selector:#submitAndPay": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "N");
        },
        "selector:#submit": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "N");
        },
        "selector:#change": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "Y") ||
              OLCS.formHelper.isChecked("content", "signatureOptions", "N");
        }
      },
      "content": {
        "selector:.download": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "N");
        },
        "selector:#label-declarationConfirmation": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "Y");
        },
        "selector:.declarationForVerify": function () {
          return OLCS.formHelper.isChecked("content", "signatureOptions", "Y");
        },
      }
    }
  });
});
