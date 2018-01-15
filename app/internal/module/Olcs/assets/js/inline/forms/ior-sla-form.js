$(function() {
    "use strict";

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "fields": {
                "selector:.__ptr-yes": function () {
                    return OLCS.formHelper.isSelected("fields", "fields[isSubmissionRequiredForApproval]", "1");
                }
            }
        }
    });
});