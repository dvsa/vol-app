$(function() {
    function hasValue(fieldset, field, value) {
        return function() {
            return OLCS.formHelper(field, fieldset).val() === value;
        };
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "userType": {
                "*": true,
                "application": hasValue("userType", "userType", "transport-manager"),
                "transportManager": hasValue("userType", "userType", "transport-manager"),
                "localAuthority": hasValue("userType", "userType", "local-authority"),
                "licenceNumber": hasValue("userType", "userType", "self-service")
            }
        }
    });
});
