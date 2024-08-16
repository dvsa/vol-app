$(function () {
    "use strict";
    const ECMT_ANNUAL_ID = 1;
    const ECMT_SHORT_TERM_ID = 2;
    const ECMT_REMOVAL_ID = 3;
    const BILATERAL_ID = 4;
    const CERT_ROADWORTHINESS_VEHICLE_ID = 6;
    const CERT_ROADWORTHINESS_TRAILER_ID = 7;
    const COUNTRY_ID_MOROCCO = 'MA';

    const IS_QA_PROCESS = [
        ECMT_ANNUAL_ID,
        ECMT_SHORT_TERM_ID,
        ECMT_REMOVAL_ID,
        BILATERAL_ID,
        CERT_ROADWORTHINESS_VEHICLE_ID,
        CERT_ROADWORTHINESS_TRAILER_ID
    ];

    var country = $("#country");
    var stockCountry = $(".stockCountry");
    var stockDates = $(".stockDates");
    var typeSelect = $("#irhpPermitType");
    var pathProcessFields = $(".pathProcess");
    var appPathGrp = $("#applicationPathGroup");
    var permitCategory = $("#permitCategory");
    var permitCategoryFields = $(".permitCategoryFields");

    function getSelectedTypeId()
    {
        return parseInt(typeSelect.val(), 10);
    }

    function toggle()
    {
        var typeId = getSelectedTypeId();

        if (typeId === BILATERAL_ID) {
            if (country.val() == COUNTRY_ID_MOROCCO) {
                permitCategoryFields.removeClass("js-hidden");
            } else {
                permitCategoryFields.addClass("js-hidden");
                permitCategory.val("");
            }
            stockCountry.removeClass("js-hidden");
        } else {
            stockCountry.addClass("js-hidden");
            permitCategoryFields.addClass("js-hidden");
            permitCategory.val("");
            country.val("");
        }

        if (typeId === ECMT_REMOVAL_ID || typeId === CERT_ROADWORTHINESS_VEHICLE_ID || typeId === CERT_ROADWORTHINESS_TRAILER_ID) {
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
        toggle();
    });

    country.change(function () {
        toggle();
    });

    toggle();

    $("#IrhpPermitStock").submit(function (e) {
        var selectedTypeId = getSelectedTypeId();

        if (
            IS_QA_PROCESS.includes(selectedTypeId)
            && appPathGrp.val() == ""
        ) {
            e.preventDefault();
            var closestEl = appPathGrp.closest($("div.field"));
            if (!closestEl.hasClass("hasErrors")) {
                closestEl.addClass("hasErrors").wrap("<div class='validation-wrapper'></div>")
                    .prepend("<p class='error__text'>You must select an Application Path for this type.</p>");
            }
            return false;
        }
    });
});
