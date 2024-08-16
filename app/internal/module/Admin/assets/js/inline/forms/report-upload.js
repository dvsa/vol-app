$(function () {
    "use strict";
    const BULK_LETTER = 'rep_typ_bulk_letter';
    const BULK_EMAIL = 'rep_typ_bulk_email';

    var reportType = $("#reportType");
    var docTemplateContainer = $(".docTemplateContainer");
    var emailTemplateContainer = $(".emailTemplateContainer");
    var docTemplate = $("#docTemplate");
    var emailTemplate = $("#emailTemplate");
    var selectedReportType = reportType.val();

    function toggle(reportType)
    {
        if (reportType === BULK_LETTER) {
            docTemplateContainer.removeClass("js-hidden");
            docTemplate.prop("disabled", false);
        } else {
            docTemplateContainer.addClass("js-hidden");
            docTemplate.prop("disabled", true);
        }

        if (reportType === BULK_EMAIL) {
            emailTemplateContainer.removeClass("js-hidden");
            emailTemplate.prop("disabled", false);
        } else {
            emailTemplateContainer.addClass("js-hidden");
            emailTemplate.prop("disabled", true);
        }
    }

    reportType.change(function () {
        toggle(reportType.val());
    });

    toggle(selectedReportType);

    function wrapError(selectBox)
    {
        var closestEl = selectBox.closest($("div.field"));
        if (!closestEl.hasClass("hasErrors")) {
            closestEl.addClass("hasErrors").wrap("<div class='validation-wrapper'></div>")
                .prepend("<p class='error__text'>You must select a template!</p>");
        }
    }

    $("#reportUpload").submit(function (e) {
        if (reportType.val() === BULK_LETTER && docTemplate.val() === "") {
            wrapError(docTemplate);
            e.preventDefault();
            return false;
        }
        if (reportType.val() === BULK_EMAIL && emailTemplate.val() === "") {
            wrapError(emailTemplate);
            e.preventDefault();
            return false;
        }
    });
});
