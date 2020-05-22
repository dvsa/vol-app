$(function () {
    "use strict";

    const ECMT_ANNUAL_ID = "1";
    const ECMT_SHORT_ID = "2";
    const HAS_YEAR_SELECTION = [ECMT_ANNUAL_ID, ECMT_SHORT_ID];
    const HAS_STOCK_SELECTION = [ECMT_SHORT_ID];

    var typeSelect = $("#permitType");
    var yearSelect = $("#yearList");
    var stockSelect = $("#stock");
    var submitBtn = $("#form-actions\\[submit\\]");
    var csrfToken = $("#security");

    submitBtn.hide();

    typeSelect.change(function () {
        submitBtn.hide();
        yearSelect.empty();
        stockSelect.empty();
        $(".yearSelect").addClass("js-hidden");
        $(".stock").addClass("js-hidden")
        if(HAS_YEAR_SELECTION.includes(typeSelect.val())){
            fetchYears();
        } else {
            submitBtn.show();
        }
    });

    yearSelect.change(function () {
        stockSelect.empty();
        if( yearSelect.val() == "2019") {
            $(".stock").addClass("js-hidden");
        }
        fetchStocks();
    });

    function fetchYears() {
        OLCS.preloader.show("modal");
        $.post("../../irhp-application/available-years", {permitType: typeSelect.val(), security: csrfToken.val()}, function (data) {
            yearSelect.append($("<option>", {
                value: "",
                text : "Please Select"
            }));
            $.each(data.years, function (i, item) {
                yearSelect.append($("<option>", {
                    value: item,
                    text : item
                }));
            });
            $(".yearSelect").removeClass("js-hidden");
            OLCS.preloader.hide();
        });
    }

    function fetchStocks() {
        OLCS.preloader.show("modal");
        $.post("../../irhp-application/available-stocks", {
            permitType: typeSelect.val(),
            year: yearSelect.val(),
            security: csrfToken.val()
        }, function (data) {
            $.each(data.stocks, function (i, item) {
                stockSelect.append($("<option>", {
                    value: item.id,
                    text : item.periodName
                }));
            });
            if(
                HAS_STOCK_SELECTION.includes(typeSelect.val())
                && yearSelect.val() !== "2019"
            ) {
                $(".stock").removeClass("js-hidden");
            }
            submitBtn.show();
            OLCS.preloader.hide();
        });
    }
});

