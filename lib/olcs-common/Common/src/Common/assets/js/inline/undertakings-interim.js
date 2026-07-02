$(function () {
  "use strict";

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "interim": {
        "#applicationInterimReason": function () {
          var check = OLCS.formHelper.isChecked("interim", "goodsApplicationInterim");
          $("#applicationInterimReason").parents('.validation-wrapper').toggle(check);
          return check
        },
        ".interimFee": function () {
          return OLCS.formHelper.isChecked("interim", "goodsApplicationInterim");
        },
        "#application-interim-reason": function() {
          return OLCS.formHelper.isChecked("interim", "goodsApplicationInterim");
        },

          ".typeOfLicence-guidance-restricted": function () {
            var check = OLCS.formHelper.isChecked("interim", "goodsApplicationInterim");
            $("#applicationInterimReason").parents('.validation-wrapper').toggle(check);
            return check;
          },
        "#interimFee": function ()  {
          var check = OLCS.formHelper.isChecked("interim", "goodsApplicationInterim");
          $("#applicationInterimReason").parents('.validation-wrapper').toggle(check);
          return check;
          },
        }
      }
  });
});
