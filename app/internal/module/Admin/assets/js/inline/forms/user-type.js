$(function () {
  function hasValue(fieldset, field, value) {
    return function () {
      return OLCS.formHelper(field, fieldset).val() === value;
    };
  }

  OLCS.cascadeForm({
    form: "form[method=post]",
    rulesets: {
      "userType": {
        "*": true,
        "team": hasValue("userType", "userType", "internal"),
        "localAuthority": hasValue("userType", "userType", "local-authority"),
        "transportManager": hasValue("userType", "userType", "transport-manager"),
        "licenceNumber": hasValue("userType", "userType", "self-service"),
        "partnerContactDetails": hasValue("userType", "userType", "partner")
      },
      "userType[applicationTransportManagers]": {
        "*": hasValue("userType", "userType", "transport-manager")
      }
    }
  });
});
