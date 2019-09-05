OLCS.ready(function () {
    "use strict";

    // assign frequently used JQuery selectors to vars to decrease DOM searching
    var yearRadio = $("input[name=\"fields[yearRadios]\"]");
    var selectedYear = $("input[name=\"fields[year]\"]").val();
    var euro5 = $("#requiredEuro5");
    var euro6 = $("#requiredEuro6");

    // When the Year Select radios change, trigger method to do AJAX and field visibility.
    yearRadio.change(function () {
        $("#year").val($(this).val());
        fetchToggle($(this).val());
    });

    // If there is already a year selected at this point, its an Edit form, disable Year selection and show the form
    if (selectedYear) {
        yearRadio.attr("disabled", true);
        // Required to re-check correct radio for user-comfort in some datereceived validation scenarios
        $("input[name=\"fields[yearRadios]\"][value="+selectedYear+"]").prop("checked",true);
        // When editing zeros are returned from backend, make empty strings for validation rules
        if (euro5.val() == 0) { euro5.val(""); };
        if (euro6.val() == 0) { euro6.val(""); };
        fetchToggle(selectedYear);
    }

    // Performs an AJAX request for emissions standards by year.
    function fetchToggle(year) {
        OLCS.preloader.show("modal");
        // Hide the year dependent elements
        $(".yearDependent, .emission5Dependent, .emission6Dependent").addClass("js-hidden");
        // Perform XHR request
        var emissionJsonUrl = "/licence/"+$("#licenceId").val()+"/permits/add/emissions?year="+year;
        $.get(emissionJsonUrl, function (data) {
            // Remove hidden class from generic fiels
            $(".yearDependent").removeClass("js-hidden");
            // Conditionally remove hidden class from euro emissions field based on payload
            if (data.yearEmissions[year].euro5) {
                $(".emission5Dependent").removeClass("js-hidden");
            }
            if (data.yearEmissions[year].euro6) {
                $(".emission6Dependent").removeClass("js-hidden");
            }
            OLCS.preloader.hide();
        });
    }

    // On-save validation tasks.
    $("#saveIrhpPermitApplication").click(function (clickEvent) {
        validatePermitsRequired(euro5, euro6, clickEvent);
    });

    function validatePermitsRequired(euro5, euro6, clickEvent) {
        // Check each field for invalid chars or numbers less than 1
        if (
            (euro5.val() !== "" && parseInt(euro5.val(), 10) < 0) ||
            (!$.isNumeric(euro5.val()) && euro5.val().length > 0)
        ) {
            wrapError(euro5, "Must be a non-negative integer if value is provided.", clickEvent);
        }
        if (
            euro6.val() !== "" && parseInt(euro6.val(), 10) < 0 ||
            (!$.isNumeric(euro6.val()) && euro6.val().length > 0)
        ) {
            wrapError(euro6, "Must be a non-negative integer if value is provided.", clickEvent);
        }

        if(parseInt(euro5.val(), 10) === 0 && parseInt(euro6.val(), 10) === 0){
            wrapError(euro5, "Application must be for at least 1 permit.", clickEvent);
            wrapError(euro6, "Application must be for at least 1 permit.", clickEvent);
        }

        // Compare total permits required with total vehicle authorization
        var euro5Val = parseInt(euro5.val(), 10);
        var euro6Val = parseInt(euro6.val(), 10);
        var numVehicles = parseInt($("#numVehicles").val(), 10);
        if (euro5Val + euro6Val > numVehicles) {
            wrapError(euro6, "Total Euro5 and Euro6 values cannot exceed " + numVehicles, clickEvent);
            wrapError(euro5, "Total Euro5 and Euro6 values cannot exceed " + numVehicles, clickEvent);
        }
    }

    // decorates element with VOL Error stylying and scrolls to the element.
    function wrapError(element, message, clickEvent) {
        var closestEl = element.closest($("div.field"));
        if(!closestEl.hasClass("hasErrors")){
            closestEl
                .addClass("hasErrors")
                .wrap("<div class='validation-wrapper'></div>")
                .prepend("<p class='error__text'>" + message + "</p>");
        }

        $([document.documentElement, document.body]).animate({
            scrollTop: element.offset().top - 100
        }, 500);
        // VOL JS disables Submit button by default, remove these classes as errors sat without POST happning.
        $(".enabled-on-render").removeClass("disabled enabled-on-render");

        clickEvent.preventDefault();
    }
});
