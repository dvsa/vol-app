$(function () {
    "use strict";

    const ECMT_ANNUAL_ID = "1";
    const ECMT_SHORT_ID = "2";
    const BILATERAL_ID = "4";
    const HAS_YEAR_SELECTION = [ECMT_ANNUAL_ID, ECMT_SHORT_ID];
    const HAS_STOCK_SELECTION = [ECMT_SHORT_ID];

    var typeSelect = $("#permitType");
    var yearSelect = $("#yearList");
    var stockSelect = $("#stock");
    var countriesContainer = $("#bilateralCountries");
    var submitBtn = $("#form-actions\\[submit\\]");
    var csrfToken = $("#security");

    submitBtn.hide();

    countriesContainer.delegate("input", "change", function () {
        if (countriesContainer.find("input:checked").length) {
            submitBtn.show();
        } else {
            submitBtn.hide();
        }
    });

    typeSelect.change(function () {
        submitBtn.hide();
        yearSelect.empty();
        stockSelect.empty();
        countriesContainer.addClass("js-hidden").empty();
        $(".yearSelect").addClass("js-hidden");
        $(".stock").addClass("js-hidden")
        if (typeSelect.val() == BILATERAL_ID) {
            fetchBilateralCountries();
        } else if (HAS_YEAR_SELECTION.includes(typeSelect.val())) {
            fetchYears();
        } else {
            submitBtn.show();
        }
    });

    yearSelect.change(function () {
        stockSelect.empty();
        if ( yearSelect.val() == "2019") {
            $(".stock").addClass("js-hidden");
        }
        fetchStocks();
    });

    function fetchBilateralCountries()
    {
        OLCS.preloader.show("modal");
        $.post("../../irhp-application/available-countries", {security: csrfToken.val()}, function (data) {
            if (data.countries.length) {
                countriesContainer.append("<p>").text("Select countries:");
                $.each(data.countries, function (i, item) {
                    countriesContainer.append(
                        $("<label>").text(item.name).prepend(
                            $("<input>", {
                                type: "checkbox",
                                name: "countries[]",
                                value: item.id
                            })
                        )
                    );
                });
            } else {
                countriesContainer.append("<p>").text("No bilateral countries currently available.");
            }
            countriesContainer.removeClass("js-hidden");
            OLCS.preloader.hide();
        });
    }

    function fetchYears()
    {
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

    function fetchStocks()
    {
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
            if (
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
