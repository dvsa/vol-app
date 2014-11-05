OLCS.ready(function() {
  OLCS.tableHandler({
    table: ".table__form",
    container: ".table__form",
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
