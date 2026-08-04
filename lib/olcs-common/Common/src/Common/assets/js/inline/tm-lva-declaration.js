$(function () {
    "use strict";
    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "form-actions": {
                "selector:#sign": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "Y");
                },
                "selector:#submitAndPay": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "N");
                },
                "selector:#submit": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "N");
                },
                "selector:#change": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "Y") ||
                        OLCS.formHelper.isChecked("content", "isDigitallySigned", "N");
                }
            },
            "content": {
                "selector:.download": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "N");
                },
                "selector:#label-declarationConfirmation": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "Y");
                },
                "selector:.declarationForVerify": function () {
                    return OLCS.formHelper.isChecked("content", "isDigitallySigned", "Y");
                },
            }
        }
    });
});

/* use pattern for form */