$(function() {
    function hasValue(value) {
        console.log('triggered');
        return function() {
            return OLCS.formHelper("userType", "userType").val() === value;
        };
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "team": hasValue("internal"),
            }
        }
    });
});
