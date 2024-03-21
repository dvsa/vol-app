$(function () {
    function hasValue(fieldset, field, value)
    {
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
                ".tm-current": hasValue("userType", "userType", "transport-manager"),
                "#applicationTransportManagers": hasValue("userType", "userType", "transport-manager"),
                "transportManager": hasValue("userType", "userType", "transport-manager"),
                "licenceNumber": hasValue("userType", "userType", "operator"),
                "partnerContactDetails": hasValue("userType", "userType", "partner")
            }
        }
    });
});
