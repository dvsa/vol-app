$(function () {
    "use strict";

    function hasValue(value)
    {
        return function () {
            return OLCS.formHelper("fields", "writtenOutcome").val() === "piwo_" + value;
        };
    }

    OLCS.cascadeForm({
        form: "[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "date:tcWrittenReasonDate": hasValue("reason"),
                "date:writtenReasonLetterDate": hasValue("reason"),
                "date:tcWrittenDecisionDate": hasValue("decision"),
                "date:writtenDecisionLetterDate": hasValue("decision"),
                "date:decisionLetterSentDate": hasValue("verbal")

            }
        }
    });
});
