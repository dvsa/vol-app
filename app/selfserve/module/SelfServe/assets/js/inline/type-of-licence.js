$(function() {

  "use strict";

  var F = OLCS.formHelper;

  var niFlag       = F("operator-location", "niFlag");
  var operatorType = F("operator-type", "goodsOrPsv");

  OLCS.cascadeForm({
    form: "#application_type-of-licence_form",
    rulesets: {
      // operator location is *always* shown
      "operator-location": true,

      // operator type only shown when location has been completed
      // and value is great britain
      "operator-type": function() {
        return niFlag.filter(":checked").val() === "0";
      },

      // licence type is nested; the first rule defines when to show the fieldset
      // (in this case if the licence is NI or the user has chosen an operator type)
      "licence-type": {
        "*": function() {
          return (
            niFlag.filter(":checked").val() === "1" ||
            niFlag.filter(":checked").length && operatorType.filter(":checked").length
          );
        },

        // this rule relates to an element within the fieldset
        "licenceType=special-restricted": function() {
          return operatorType.filter(":checked").val() === "psv";
        }
      }
    },
    submit: function() {
      if (F("operator-type").is(":hidden")) {
        operatorType.first().prop("checked", true);
      }

      if (F("licence-type").is(":hidden")) {
        F("licence-type", "licenceType").first().prop("checked", true);
      }
    }
  });
});
