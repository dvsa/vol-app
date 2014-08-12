$(function() {
  OLCS.formHandler({
    // the form to bind to
    form: "#tasks-home_form",
    // make sure the primary submit button is hidden
    hideSubmit: true,
    // where we'll render any response data to
    container: ".table__form",
    // filter the data returned from the server to only
    // contain content within this element
    filter: ".table__form"
  });

  OLCS.cascadeInput({
    source: "#team",
    dest: "#owner",
    process: function(value, done) {
      $.get("/tasks/users/" + value, done);
    },
  });

  OLCS.cascadeInput({
    source: "#category",
    dest: "#sub_category",
    url: "/tasks/sub-categories"
  });
});
