$(function () {
    "use strict";

    const ECMT_REMOVAL_ID = "3";
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
});
