var OLCS = OLCS || {};


OLCS.surrenderDetails = (function(document, $) {
    "use strict";

    var exports = {};

    exports.init = function () {
        $(".js-surrender-checks-digitalSignature").change(function () {
            exports.toggleSurrender();
            exports.updateChecks();
        });
        $(".js-surrender-checks-ecms").change(function () {
            exports.toggleSurrender();
            exports.updateChecks();
        });

        exports.toggleSurrender();
    };

    exports.updateChecks = function(){
       var checkboxes = {
            "signatureChecked": exports.hasCheckedSignature()  ? 1 : 0,
            "ecmsChecked" : exports.hasCheckedECMS()  ? 1 : 0
        };

       exports.postCheckbox(checkboxes);
    };

    exports.postCheckbox = function(data) {
        OLCS.ajax({
            method: "POST",
            url: "surrender-checks",
            data: data,
            complete: OLCS.surrenderDetails.reload,
            preloaderType: "modal",
        });
    };

    exports.reload = function(){
     window.location.reload();
    };

    exports.shouldEnableButton = function () {
        return exports.hasNoOpenCases() && exports.hasNoBusRegistrations() &&
            exports.hasCheckedSignature() && exports.hasCheckedECMS();
    };

    exports.hasCheckedSignature = function () {
        return $(".js-surrender-checks-digitalSignature").prop("checked");
    };

    exports.hasCheckedECMS = function () {
        return $(".js-surrender-checks-ecms").prop("checked");
    };

    exports.hasNoOpenCases = function () {
        return $(".js-surrender-checks-openCases").length > 0;
    };

    exports.hasNoBusRegistrations = function () {
        if ( $(".js-surrender-checks-busRegistrations").length > 0) {
            return true;
        }

        return $("table[name=busRegistrations]").length < 1;
    };

    exports.toggleSurrender = function () {
        if (exports.shouldEnableButton()) {
            exports.enableSurrender();
        } else {
            exports.disableSurrender();
        }
    };

    exports.enableSurrender = function () {
        $(".js-approve-surrender").removeClass("govuk-button--disabled");
    };

    exports.disableSurrender = function () {
        var button = $(".js-approve-surrender");
        if (!button.hasClass("govuk-button--disabled")) {
            button.addClass("govuk-button--disabled");
        }
    };

    return exports;

}(document, window.jQuery));
