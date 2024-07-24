var OLCS = OLCS || {};

/**
 * Table sorter
 */

OLCS.tableSorter = (function(document, $, undefined) {

  "use strict";

  return function init(options) {
    var table = options.table;
    var filter = options.filter;
    var container = options.container;

    var linkSelector =
      table + " .results-settings a, " +
      table + " .sortable a, " +
      table + " .pagination a";

    $(document).on("click", linkSelector, function clickHandler(e) {
      e.preventDefault();
      var clickedElement = this;

      OLCS.ajax({
        url: $(this).attr("href"),
        success: OLCS.filterResponse(filter, $(clickedElement).parents(options.container)),
        complete: function() {
          OLCS.eventEmitter.emit("update:" + container);
        }
      });

      var queryParams = OLCS.queryString.parse(
        $(this).attr("href")
      );

      $.each(["sort", "order", "limit"], function(k, v) {
        if (queryParams[v]) {
          $("#" + v).val(queryParams[v]);
        }
      });
    });
  };

}(document, window.jQuery));
