$(function () {
    "use strict";

    var rangesUrl = $("#rangesUrl");
    var irhpPermitApplication = $("#permitAppId");
    var rangeSelect = $("#irhpPermitRange");
    var selectedRange = $("#irhpPermitRangeSelected").val();
    var csrfToken = $("#security");

    fetchRanges();

    function formatRangeLabel(item)
    {
        let rangeCountries = item.countrys.map(function (country) {
            return country.id;
        });
        rangeCountries = item.countrys.length > 0 ? `(${rangeCountries.join()})` : "";
        let rangeLabel = item.prefix;
        rangeLabel = rangeLabel.concat(" ", item.fromNo, "-", item.toNo);
        rangeLabel = rangeLabel.concat(" ", rangeCountries);
        rangeLabel = rangeLabel.concat(" | ", item.remainingPermits, " Remaining");
        rangeLabel = rangeLabel.concat(" | ", item.emissionsCategory.description);
        return rangeLabel;
    }

    function fetchRanges()
    {
        OLCS.preloader.show("modal");
        $.post(
            rangesUrl.val(),
            {
                irhpPermitApplication: irhpPermitApplication.val(),
                security: csrfToken.val()
            },
            function (data) {
                $.each(data.ranges, function (i, item) {
                    rangeSelect.append($("<option>", {
                        value: item.id,
                        text: formatRangeLabel(item),
                        selected: selectedRange == item.id
                    }));
                });
                $(".rangeSelectBox").removeClass("js-hidden");
                OLCS.preloader.hide();
            }
        );
    }
});
