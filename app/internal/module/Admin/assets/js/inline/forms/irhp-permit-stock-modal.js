$(function () {
    "use strict";
    const ECMT_REMOVAL_ID = 3;
    const BILATERAL_ID = 4;

    var stockCountry = $(".stockCountry");
    var stockDates = $(".stockDates");
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
    }

    typeSelect.change(function () {
        toggle(parseInt(typeSelect.val(), 10));
    });

    toggle(parseInt(typeSelect.val(), 10));
});
