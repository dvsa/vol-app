$(function () {
    "use strict";
    const ECMT_SHORT_TERM_ID = 2;
    const ECMT_REMOVAL_ID = 3;
    const BILATERAL_ID = 4;
    const IS_QA_PROCESS = [ECMT_SHORT_TERM_ID, ECMT_REMOVAL_ID];

    var stockCountry = $(".stockCountry");
    var stockDates = $(".stockDates");
    var typeSelect = $("#irhpPermitType");
    var pathProcessFields = $(".pathProcess");
    var appPathGrp = $("#applicationPathGroup");

    function toggle(typeId) {
        if (typeId === BILATERAL_ID) {
            stockCountry.removeClass("js-hidden");
        } else {
            stockCountry.addClass("js-hidden");
        }

        if (typeId === ECMT_REMOVAL_ID) {
            stockDates.addClass("js-hidden");
        } else {
            stockDates.removeClass("js-hidden");
        }

        if (IS_QA_PROCESS.includes(typeId)) {
            pathProcessFields.removeClass("js-hidden");
        } else {
            pathProcessFields.addClass("js-hidden");
        }
    }

    typeSelect.change(function () {
        toggle(parseInt(typeSelect.val(), 10));
    });

    toggle(parseInt(typeSelect.val(), 10));

    $("#IrhpPermitStock").submit(function (e) {
        if (
            IS_QA_PROCESS.includes(parseInt(typeSelect.val(), 10))
            && appPathGrp.val() == ""
        ) {
            e.preventDefault(e);
            var closestEl = appPathGrp.closest($("div.field"));
            if (!closestEl.hasClass("hasErrors")) {
                closestEl.addClass("hasErrors").wrap("<div class='validation-wrapper'></div>")
                    .prepend("<p class='error__text'>You must select an Application Path for this type.</p>");
            }
            return false;
        }
    });
});
