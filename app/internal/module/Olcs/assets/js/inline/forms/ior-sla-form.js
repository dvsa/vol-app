$(function () {
    "use strict";
    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "fieldset": {
                "selector:.__ptr-yes": function () {
                    return OLCS.formHelper.isChecked("fields", "isSubmissionRequiredForApproval", "1");
                },
                "selector:.__sra-yes": function () {
                    return OLCS.formHelper.isChecked("fields", "isSubmissionRequiredForAction", "1");
                },
                "selector:.__ior-revoke":function () {
                    return OLCS.formHelper.isSelected("fields", "actionToBeTaken", "ptr_action_to_be_taken_revoke");
                },
                "selector:.__ior-nfa":function () {
                    return OLCS.formHelper.isSelected("fields", "actionToBeTaken", "ptr_action_to_be_taken_nfa");
                },
                "selector:.__ior-warning": function () {
                    return OLCS.formHelper.isSelected("fields", "actionToBeTaken", "ptr_action_to_be_taken_warning");
                },
                "selector:.__ior-pi": function () {
                    return OLCS.formHelper.isSelected("fields", "actionToBeTaken", "ptr_action_to_be_taken_pi");
                },
                "selector:.__ior-other": function () {
                    return OLCS.formHelper.isSelected("fields", "actionToBeTaken", "ptr_action_to_be_taken_other");
                }
            },
            "fields":{
                "selector:label[for='finalSubmissionPresidingTc']":function () {
                    return OLCS.formHelper.isChecked("fields", "isSubmissionRequiredForAction", "1");
                },
                "selector:label[for='approvalSubmissionPresidingTc']":function () {
                    return OLCS.formHelper.isChecked("fields", "isSubmissionRequiredForApproval", "1");
                },
            }
        }
    });
});