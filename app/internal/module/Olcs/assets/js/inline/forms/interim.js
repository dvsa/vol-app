OLCS.ready(function () {
    "use strict";

    function showForm()
    {
        return OLCS.formHelper.isChecked("requested", "interimRequested") ||
        !OLCS.formHelper.findInput("requested", "interimRequested").length ||
        OLCS.formHelper.findInput("requested", "interimRequested")[0].disabled;
    }

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "data": showForm,
            "operatingCentres": showForm,
            "vehicles": showForm,
            "interimStatus": showForm,
            "form-actions": {
                "selector:#grant": showForm,
                "selector:#refuse": showForm,
                "selector:#reprint": showForm
            }
        }
    });

  // actually we are not handling the table, just showing modal after button clicked
    OLCS.crudTableHandler({
        selector: "#grant,#refuse"
    });
});
