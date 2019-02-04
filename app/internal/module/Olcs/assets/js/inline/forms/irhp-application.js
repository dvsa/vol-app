$(function () {
    'use strict';
    $("input[name*='permitsRequired']").change(function () {
        var totalPermits = 0;
        $("input[name*='permitsRequired']").each(function () {
            totalPermits += parseInt($(this).val(), 10);
        });
        if (totalPermits >= parseInt($("#numVehicles").val(), 10)) {
            $(".actions-container")
                .addClass("validation-wrapper")
                .prepend('<p class="error__text jserrormsg">Total Permits Required on this page may not exceed total vehicle authorisation</p>');
            $("#saveIrhpPermitApplication").prop('disabled', true);
        } else {
            $(".actions-container").removeClass("validation-wrapper");
            $(".jserrormsg").remove();
            $("#saveIrhpPermitApplication").prop('disabled', false);
        }
    });
});
