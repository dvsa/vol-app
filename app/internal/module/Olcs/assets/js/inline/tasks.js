$(function() {
  var form = "[name=tasks-home]";

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

  OLCS.cascadeInput({
    source: form + " #assignedToTeam",
    dest: form + " #assignedToUser",
    process: function(value, done) {
      $.get("/list/users/" + value, done);
    }
  });

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #taskSubCategory",
    url: "/list/task-sub-categories"
  });

  OLCS.conditionalButton({
      form: ".table__form",
      label: "Re-assign Task",
      predicate: function(length, callback) {
        callback(length < 1);
      }
  });

  OLCS.conditionalButton({
      form: ".table__form",
      label: "Close Task",
      predicate: function(length, callback) {
        callback(length < 1);
      }
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
