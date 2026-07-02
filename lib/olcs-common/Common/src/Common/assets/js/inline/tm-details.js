OLCS.ready(function() {
  "use strict";

  var F = OLCS.formHelper;

  OLCS.cascadeForm({
    form: "#lva-transport-manager-details",
    cascade: false,
    rulesets: {
      "declarations": {
        // Hide the whole declaration fieldset, until the tmType radio has been checked
        "*": function() {
          return F.isChecked("responsibilities", "tmType", "tm_t_i")
            || F.isChecked("responsibilities", "tmType", "tm_t_e");
        },
        "selector:.tm-details-declaration-internal": function() {
          return F.isChecked("responsibilities", "tmType", "tm_t_i");
        },
        "selector:.tm-details-declaration-external": function() {
          return F.isChecked("responsibilities", "tmType", "tm_t_e");
        }
      },
        "optionalData": {
            "selector:#otherLicences": function () {
                return OLCS.formHelper.isChecked("responsibilities[otherLicencesFieldset]", "hasOtherLicences", "Y");
            },
            "selector:#otherEmployments": function () {
                return OLCS.formHelper.isChecked("otherEmployments", "hasOtherEmployment", "Y");
            },
            "selector:#previousConvictions": function () {
                return OLCS.formHelper.isChecked("previousHistory", "hasConvictions", "Y");
            },
            "selector:#previousLicences": function () {
                return OLCS.formHelper.isChecked("previousHistory", "hasPreviousLicences", "Y");
            },
        },
        "hasUndertakenTraining": {
            "selector:.hintNoTraining": function () {
                return OLCS.formHelper.isChecked("details", "hasUndertakenTraining", "N");
            },
        },
        "isOwner": {
            "selector:.hintNoOwner": function () {
                return OLCS.formHelper.isChecked("responsibilities", "isOwner", "N");
            },
        }
    }
  });

  var tableSelector = "form [data-group*='otherLicences']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit-other-licence-applications",
    predicate: function (length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Delete-other-licence-applications",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });
});
