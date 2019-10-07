$(function () {
    "use strict";

    const ECMT_SHORT_ID = 2;
    var typeSelect = $("#permitType");
    var yearSelect = $("#yearList");

    typeSelect.change(function () {
        if($(this).val() == ECMT_SHORT_ID){
            fetchYears();
        }
    });

    yearSelect.change(function () {
        if($(this).val() !== "2019") {
            fetchStocks();
        } else {
            clearStocks();
        }
    });

    function fetchYears() {
        OLCS.preloader.show("modal");
        $.post("available-years", {permitType: typeSelect.val()}, function (data) {
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
        $.post("available-stocks", {
            permitType: typeSelect.val(),
            year: yearSelect.val()
        }, function (data) {
            $.each(data.stocks, function (i, item) {
                $("#stock").append($("<option>", {
                    value: item.id,
                    text : item.periodName
                }));
            });
            $(".stock").removeClass("js-hidden");
            OLCS.preloader.hide();
        });
    }

    function clearStocks(){
        $('#stock').empty();
        $(".stock").addClass("js-hidden");
    }
});

