OLCS.ready(function () {

    var stockCaptions = {
        "stock_scoring_never_run": "Stock scoring has never been run on this stock item.",
        "stock_scoring_pending": "Stock scoring is queued for execution and will take place shortly.",
        "stock_scoring_in_progress": "Stock scoring is in progress. You will be notified when the process is complete.",
        "stock_scoring_successful": "Stock scoring has been run and has completed successfully",
        "stock_scoring_prereq_fail": "An attempt has been made to run stock scoring, but one or more prerequisites were not satisfied.",
        "stock_scoring_unexpected_fail": "An attempt has been made to run stock scoring, but an unexpected error was encountered.",
        "stock_accept_pending": "Stock acceptance is queued for execution and will take place shortly.",
        "stock_accept_in_progress": "Stock acceptance is in progress. You will be notified when the process is complete.",
        "stock_accept_successful": "Stock acceptance has been run and has concluded successfully",
        "stock_accept_prereq_fail": "An attempt has been made to run scoring acceptance, but one or more prerequisites were not satisfied.",
        "stock_accept_unexpected_fail": "An attempt has been made to run scoring acceptance, but an unexpected error was encountered."
    };

    function updateStatus(statusUrl)
    {
        $.get(statusUrl, function (data) {
            var divContent = "<h2>Current status: " + data.stockStatusMessage + "</h2>";
            divContent += "<ul style=\"position: relative; left: 30px;\">";
            divContent += "<li>" + stockCaptions[data.stockStatusId] + "</li>";

            var scoringMessage;
            var scoringVisibility;
            if (data.scoringPermitted) {
                scoringMessage = "Press the <strong>Run</strong> or <strong>Run with mean deviation</strong> button to start the scoring process.";
                scoringVisibility = "visible";
            } else {
                scoringMessage = "The <strong>Run</strong> option is not currently available. " + data.scoringMessage;
                scoringVisibility = "hidden";
            }

            divContent += "<li>" + scoringMessage + "</li>";
            $("#runButton, #runWithDeviationButton, #deviation").css("visibility", scoringVisibility);

            var acceptMessage;
            var acceptVisibility;
            if (data.acceptAndPostScoringReportPermitted) {
                acceptMessage = "Press the <strong>Accept</strong> button to start the acceptance process.";
                acceptVisibility = "visible";
            } else {
                acceptMessage = "The <strong>Accept</strong> option is not currently available. " + data.acceptAndPostScoringReportMessage;
                acceptVisibility = "hidden";
            }
            divContent += "<li>" + acceptMessage + "</li>";

            var alignStockMessage;
            var alignStockVisibility;
            if (data.acceptAndPostScoringReportPermitted) {
                alignStockMessage = "Press the <strong>Align stock</strong> button to download the stock alignment report.";
                alignStockVisibility = "visible";
            } else {
                alignStockMessage = "The <strong>Align stock</strong> option is not currently available. " + data.acceptAndPostScoringReportMessage;
                alignStockVisibility = "hidden";
            }
            divContent += "<li>" + alignStockMessage + "</li>";

            var postScoringReportMessage;
            var postScoringReportVisibility;
            if (data.acceptAndPostScoringReportPermitted) {
                postScoringReportMessage = "Press the <strong>Report</strong> button to download the post scoring report.";
                postScoringReportVisibility = "visible";
            } else {
                postScoringReportMessage = "The <strong>Report</strong> option is not currently available. " + data.acceptAndPostScoringReportMessage;
                postScoringReportVisibility = "hidden";
            }
            divContent += "<li>" + postScoringReportMessage + "</li>";

            if (data['meanDeviation']) {
                divContent += "<li>The computed mean deviation from the last scoring run is " + data['meanDeviation'] + ".</li>";
            }

            divContent += "</ul>";
            $("#acceptButton").css("visibility", acceptVisibility);
            $("#alignStockButton").css("visibility", alignStockVisibility);
            $("#postScoringReportButton").css("visibility", postScoringReportVisibility);

            $("#statusContainer").html(divContent);
            setTimeout(function () {
                updateStatus(statusUrl); }, 2500);
        });
    }

    $("#deviation").on("input propertychange paste", function () {
        var deviationValue = $("#deviation").val();
        var $runWithDeviationButton = $("#runWithDeviationButton");

        if (deviationValue != "") {
            var href = $runWithDeviationButton.data('href').replace('deviation', deviationValue);
            $runWithDeviationButton.attr('href', href);
            $runWithDeviationButton.css("display", "inline-block");
        } else {
            $runWithDeviationButton.css("display", "none");
        }
    });

    var stockId = $("#statusContainer").data('stock-id');
    var statusUrl = "/admin/permits/stocks/" + stockId + "/scoring/status";
    updateStatus(statusUrl);
});
