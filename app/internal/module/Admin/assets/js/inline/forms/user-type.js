$(function() {
    function hasValue(fieldset, field, value) {
        console.log('triggered');
        return function() {
            return OLCS.formHelper(field, fieldset).val() === value;
        };
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "userType": {
                "*": true,
                "team": hasValue("userType", "userType", "internal"),
                "application": hasValue("userType", "userType", "transport-manager"),
                "transportManager": hasValue("userType", "userType", "transport-manager"),
                "localAuthority": hasValue("userType", "userType", "local-authority"),
                "licenceNumber": hasValue("userType", "userType", "self-service")
            }
        }
    });
});
