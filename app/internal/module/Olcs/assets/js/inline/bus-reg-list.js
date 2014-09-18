$(function() {
    var form = "[name=bus-reg-list]";

    OLCS.tableHandler({
        table: ".table__form",
        container: ".table__form",
        filter: ".table__form"
    });

    OLCS.formHandler({
        // the form to bind to
        form: form,
        // make sure the primary submit button is hidden
        hideSubmit: true,
        // where we'll render any response data to
        container: ".table__form",
        // filter the data returned from the server to only
        // contain content within this element
        filter: ".table__form"
    });

    /**
     * Non component logic; bridge the table controls to the form
     */
    $(document).on(
        "click",
        ".table__form .sortable a, .table__form .results-settings a",
        function(e) {
            e.preventDefault();

            queryParams = OLCS.queryString.parse(
                $(this).attr("href")
            );

            $.each(["sort", "order", "limit"], function(k, v) {
                if (queryParams[v]) {
                    $("#" + v).val(queryParams[v]);
                }
            });
        }
    );
});