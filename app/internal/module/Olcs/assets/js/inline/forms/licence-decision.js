$(function () {
    "use strict";

    var formId = ".status-decision-form";

    function isChecked(result)
    {
        return function () {
            return OLCS.formHelper.isChecked("licence-decision-affect-immediate", "immediateAffect") === result;
        };
    }

    OLCS.cascadeForm({
        form: formId,
        cascade: false,
        rulesets: {
            "form-actions": {
                "selector:#affect-immediate": isChecked(true),
                "selector:#submit": isChecked(false)
            },
            "licence-decision": isChecked(false)
        }
    });
});
