OLCS.ready(function () {
    "use strict";

    var F = OLCS.formHelper;

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "details": {
                "trafficArea": function () {
                    return F.isSelected("details", "type", "operator");
                }
            }
        }
    });
});