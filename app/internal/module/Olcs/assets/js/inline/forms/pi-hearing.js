$(function () {
    "use strict";

    function publish()
    {
        var isAdjourned = OLCS.formHelper("fields", "isAdjourned").filter(":checked").val();
        var isCancelled = OLCS.formHelper("fields", "isCancelled").filter(":checked").val();
        return !(isAdjourned === "Y" && isCancelled === "Y");
    }

    function venueOther()
    {
        var value = OLCS.formHelper("fields", "venue").val();
        return (value === "other");
    }

    function checked(selector)
    {
        return function () {
            return OLCS
            .formHelper("fields", selector)
            .filter(":checked")
            .val() === "Y";
        };
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "venueOther": venueOther,
                "date:cancelledDate": checked("isCancelled"),
                "cancelledReason": checked("isCancelled"),
                "date:adjournedDate": checked("isAdjourned"),
                "adjournedReason": checked("isAdjourned")
            },
            "form-actions": {
                "*": true,
                "#form-actions\\[publish\\]": publish
            }
        }
    });
});
