$(function () {
    "use strict";

    const ECMT_REMOVAL_ID = "3";
    const COR_VEHICLE_ID = "6";
    const COR_TRAILER_ID = "7";

    var permitTypeId = $("#topFields\\[irhpPermitType\\]").val();
    var declaration = $("#declaration");

    if(permitTypeId === ECMT_REMOVAL_ID) {
        $("#irhpApplication").submit(function( event ) {
            if (!declaration.prop("checked")) {
                event.preventDefault();
                var closestEl = declaration.closest($("div.field"));
                if (!closestEl.hasClass("hasErrors")) {
                    closestEl.addClass("hasErrors").wrap("<div class='validation-wrapper'></div>")
                        .prepend("<p class='error__text'>Declaration must be checked to continue</p>");
                }
                $("#saveIrhpPermitApplication").removeClass("disabled");
                return false;
            }
        });
    }

    $("fieldset[data-group='qa'] > fieldset[data-enabled='false']").each(function() {
        $(this).css("background-color", "#ddd");
        $(this).find(":input").prop("disabled", true);
    });

    if (permitTypeId != COR_VEHICLE_ID && permitTypeId != COR_TRAILER_ID) {
        if ($("fieldset[data-group='qa'] > fieldset[data-enabled='true']").length == 0) {
            $("#saveIrhpPermitApplication").prop("disabled", true);
        }
    }
});
