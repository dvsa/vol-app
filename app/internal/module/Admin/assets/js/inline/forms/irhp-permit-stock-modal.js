$(function () {
    "use strict";
    const ECMT_SHORT_ID = 2;
    const ECMT_REMOVAL_ID = 3;
    const BILATERAL_ID = 4;

    var stockCountry = $(".stockCountry");
    var stockDates = $(".stockDates");
    var stockEmissions = $(".stockEmissions");
    var typeSelect = $("#irhpPermitType");

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

        if (typeId === ECMT_SHORT_ID) {
            $("#emissionsCategory option[value='emissions_cat_na']").remove();
            stockEmissions.removeClass("js-hidden");
        } else {
            stockEmissions.addClass("js-hidden");
        }
    }

    typeSelect.change(function () {
        toggle(parseInt(typeSelect.val(), 10));
    });

    toggle(parseInt(typeSelect.val(), 10));
});
