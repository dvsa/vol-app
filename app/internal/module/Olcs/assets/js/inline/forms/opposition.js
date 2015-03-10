$(function() {
    function hasValue(value) {
        return function() {
            return OLCS.formHelper("fields", "oppositionType").val() === value;
        };
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "date:outOfRepresentationDate": hasValue("otf_rep"),
                "date:outOfObjectionDate": hasValue("otf_eob")
            }
        }
    });
});
