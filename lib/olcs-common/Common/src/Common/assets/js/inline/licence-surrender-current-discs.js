$(function () {
    "use strict";
    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "content": {
                "selector:#possessionInfo": function () {
                    return OLCS.formHelper.isChecked("possessionSection", "inPossession", "Y");
                },
                "selector:#lostInfo": function () {
                    return OLCS.formHelper.isChecked("lostSection", "lost", "Y");
                },
                "selector:#stolenInfo": function () {
                    return OLCS.formHelper.isChecked("stolenSection", "stolen", "Y");
                },
            }
        }
    });
});