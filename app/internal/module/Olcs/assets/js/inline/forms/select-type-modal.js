$(function () {
    "use strict";

    const ECMT_SHORT_ID = 2;
    var typeSelect = $("#permitType");

    typeSelect.change(function () {
        if($(this).val() == ECMT_SHORT_ID){
            fetchYears($(this).val());
        }
    });

    function fetchYears(year) {
        OLCS.preloader.show("modal");
        $.post("available-years", {permitType: typeSelect.val()}, function (data) {
            $.each(data.years, function (i, item) {
                $("#yearList").append($("<option>", {
                    value: item,
                    text : item
                }));
            });
            $(".yearSelect").removeClass("js-hidden");
            OLCS.preloader.hide();
        });
    }
});

