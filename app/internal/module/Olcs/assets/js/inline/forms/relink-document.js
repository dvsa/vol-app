OLCS.ready(function () {
    "use strict";

    var form = "form[name=document-relink]";
    var labels = {
        application: "Application ID",
        busReg: "Bus registaration No",
        case: "Case ID",
            licence: "Licence No",
            irfoOrganisation: "IRFO ID",
            irhpApplication: "IRHP application id",
            transportManager: "Transport manager ID"
    };

    $(document).on("change", form + " #relinkType", function (e) {
        e.preventDefault();
        var value = $(this).val();
        $("label[for='targetId'").text(labels[value]);
    });
});
