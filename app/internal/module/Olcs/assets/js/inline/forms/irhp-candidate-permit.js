$(function () {
    "use strict";

    var irhpApplication = $("#irhpPermitApplication");
    var rangeSelect = $("#irhpPermitRange");
    var selectedRange = $("#irhpPermitRangeSelected").val();
    var csrfToken = $("#security");

    fetchRanges();

    function fetchRanges() {
        OLCS.preloader.show("modal");
        $.post("ranges", {irhpPermitApplication: irhpApplication.val(), security: csrfToken.val()}, function (data) {
            $.each(data.ranges, function (i, item) {
                let rangeCountries = item.countrys.map(function(country) { return country.id; });
                rangeCountries = item.countrys.length > 0 ? `(${rangeCountries.join()})` : "";
                rangeSelect.append($("<option>", {
                    value: item.id,
                    text : item.prefix+" "+item.fromNo+"-"+item.toNo+" "+rangeCountries+" | "+item.remainingPermits+" Remaining",
                    selected: selectedRange == item.id
                }));
            });
            $(".rangeSelectBox").removeClass("js-hidden");
            OLCS.preloader.hide();
        });
    }
});
