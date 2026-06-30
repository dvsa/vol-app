$(function () {
    "use strict";
    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "form-actions": {
                "selector:#sign": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "Y") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureVerifyMandate", "Y");
                },
                "selector:#submitAndPay": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "N") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "printSignReturnFallBack", "N");
                },
                "selector:#submit": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "N") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "printSignReturnFallBack", "N");
                },
                "selector:#change": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "Y") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "N") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureVerifyMandate", "Y");
                }
            },
            "declarationsAndUndertakings": {
                "selector:.download": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "N") || 
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "printSignReturnFallBack", "N");
                },
                "selector:#label-declarationConfirmation": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "Y") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureVerifyMandate", "Y");
                },
                "selector:.declarationForVerify": function () {
                    return OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureOptions", "Y") ||
                        OLCS.formHelper.isChecked("declarationsAndUndertakings", "signatureVerifyMandate", "Y");
                },
            }
        }
    });
});
