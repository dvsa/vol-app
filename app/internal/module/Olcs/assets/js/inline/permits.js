OLCS.ready(function () {
    "use strict";

    var form = "[name=permits-home]";

    OLCS.formHandler({
        form: form,
        hideSubmit: true,
        container: ".js-body",
        filter: ".js-body"
    });

    if (!$(".js-rows").length) {
        $(".filters").hide();
    }

    // Add event handler for Permits Form Back button click. Prevent default on Cancel, allow to continue on OK.
    $("#backToPermitList").click(function (e) {
        if (!confirm("Going back will lose any unsaved changes. Are you sure? ")) {
            e.preventDefault();
        } else {
            parent.history.back();
        }
    });

    if ($.inArray($(".permitApplicationStatus").val(), ["permit_app_withdrawn", "permit_app_cancelled", "permit_app_valid"]) > -1) {
        $("#saveIrhpPermitApplication").addClass("govuk-visually-hidden");
    }

    $(".permitApplicationStatus").click(function (e) {
        if (!confirm("Going back will lose any unsaved changes. Are you sure?")) {
            e.preventDefault();
        }
    });
});
