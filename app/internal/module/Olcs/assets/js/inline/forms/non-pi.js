$(function () {

    "use strict";

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "venueOther": function () {
                    return OLCS.formHelper("fields", "venue").val() === "other";
                }
            }
        }
    });
});
