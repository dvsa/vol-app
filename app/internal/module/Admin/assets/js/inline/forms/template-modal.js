$(function () {
    "use strict";

    var modalTemplate = '<div class="modal previewModal">' +
                        '  <div class="modal__header"><h1 class="modal__title previewTitle"></h1></div>' +
                        '  <div class="modal__content previewContent">' +
                        '    <div class="js-content"></div>' +
                        '      <div class="previewControls"><select id="dataSetSelect" class="js-hidden"></select><a id="previewClose">Back to Edit</a></div>' +
                        '      <div id="previewPane"></div>' +
                        '    </div>' +
                        '  </div>' +
                        '</div>';

    var previewData = {};

    function populatePreview()
    {
        var previewContent = previewData[$("#dataSetSelect").val()];
        if ($("#format").val() === "plain") {
            previewContent = '<pre class="wordwrap">' + previewContent + '</pre>';
        }

        $("#dataSetSelect").removeClass("js-hidden");
        $("#previewPane").html(previewContent);
    }

    function hideEditShowPreview()
    {
        //Tag and hide the main modal div, add the preview template defined above into the modal wrapper div
        $(".modal").addClass("editModal js-hidden");
        $(".modal__wrapper").prepend(modalTemplate);
        $(".previewTitle").html("Preview: " + $("#description").val());
    }

    $("#preview").click(function () {
        $(this).html("Please Wait");

        // Perform an xhr POST with the template ID, and current source from edit window.
        var previewPost = $.post(
            $("#jsonUrl").val(),
            {
                source : $("#source").val(),
                id : $("#id").val(),
                security: $("#security").val()
                }
        );

        // POST success handler - Preview worked
        previewPost.done(function ( data ) {
            //Remove unnecessary var set for debugging purposes and populate var in parent scope.
            delete data.correlationId;
            previewData = data;

            hideEditShowPreview();

            //For each dataset in the JSON payload add an entry to the select box.
            $.each(data, function (i, item) {
                $('#dataSetSelect').append($('<option>', {
                    value: i,
                    text : i
                }));
            });

            populatePreview();
        });

        // POST error handler - Preview render failed - show error
        previewPost.fail(function (data) {
            hideEditShowPreview();
            delete data.responseJSON.correlationId;
            $.each(data.responseJSON, function (dataset, error) {
                $("#previewPane").html("<h3>Dataset: " + dataset + "</h3>" + "<pre class='wordwrap'>" + error + "</pre>");
            });
        });
    });

    // When the dataset select box changes, call populate helper function.
    $(document).on("change","#dataSetSelect",function () {
        populatePreview();
    });

    // When Cancel is clicked, kill the preview div, untag and unhide the original modal content.
    $(document).on("click","#previewClose",function () {
        $(".previewModal").remove();
        $("#preview").html("Preview");
        $(".modal").removeClass("editModal js-hidden");
        $("#dataSetSelect").addClass("js-hidden");
    });
});
