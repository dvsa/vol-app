$(function () {
    "use strict";

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "date:withdrawnDate": function () {
                    return OLCS.formHelper.isChecked("fields", "isWithdrawn");
                }
            }
        }
    });
});
