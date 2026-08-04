$(function () {
    "use strict";

    var F = OLCS.formHelper;

    function willSurrender()
    {
        return F.isChecked("data", "willSurrender", "Y");
    }

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "data": {
                ".will-surrender": willSurrender
            }
        }
    });
});
