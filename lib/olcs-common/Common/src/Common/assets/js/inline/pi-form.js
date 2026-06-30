$(function() {
  "use strict";

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "fields": {
                "selector:.pi_ecms_first_received_date": function () {
                    return OLCS.formHelper.isSelected("fields", "isEcmsCase", "Y");
                }
            }
        }
    });
});
