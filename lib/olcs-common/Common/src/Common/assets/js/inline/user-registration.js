OLCS.ready(function() {
  "use strict";

  var F = OLCS.formHelper;

  function hasValue(fieldset, field, value) {
    return function () {
      return F.isChecked(fieldset, field, value);
    };
  }

  OLCS.cascadeForm({
    form: "form[method=post]",
    rulesets: {
      "fields": {
        "*": true,
        "licenceNumber": hasValue("fields", "isLicenceHolder", "Y"),
        "organisationName": hasValue("fields", "isLicenceHolder", "N"),
        "#businessType": hasValue("fields", "isLicenceHolder", "N")
      }
    }
  });
});
