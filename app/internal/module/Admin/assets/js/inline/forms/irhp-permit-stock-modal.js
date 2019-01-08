$(function () {
    "use strict";
    const BILATERAL_ID = 4;

    // When type changes to/from bilateral, toggle visibility of the country select container
    $("#irhpPermitType").change(function () {
        if (parseInt($(this).val(), 10) === BILATERAL_ID) {
            $(".stockCountry").removeClass("js-hidden");
        } else {
            $(".stockCountry").addClass("js-hidden");
        }
    });

    // If editing an entry, unhide country container if Bilateral is pre-selected.
    if(parseInt($("#irhpPermitType").val(), 10) === BILATERAL_ID){
        $(".stockCountry").removeClass("js-hidden");
    }
});
