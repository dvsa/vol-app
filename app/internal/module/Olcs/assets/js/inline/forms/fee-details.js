OLCS.ready(function () {
    "use strict";

    function showWaive()
    {
        if (!OLCS.formHelper.findInput("fee-details", "waiveRemainder").length) {
            return true;
        }
        return OLCS.formHelper.isChecked("fee-details", "waiveRemainder");
    }

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "fee-details": {
                "waiveReason": showWaive
            },
            "form-actions": {
                "selector:#recommend": showWaive,
                "selector:#reject": showWaive,
                "selector:#approve": showWaive
            }
        }
    });

});
