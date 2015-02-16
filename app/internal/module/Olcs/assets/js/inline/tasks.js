OLCS.ready(function() {
  var form = "[name=tasks-home]";
  var reassignHandler;
  var closeHandler;

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

  reassignHandler = OLCS.conditionalButton({
    form: ".table__form",
    label: "Re-assign Task",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });

  closeHandler = OLCS.conditionalButton({
    form: ".table__form",
    label: "Close Task",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });

  OLCS.eventEmitter.on("update:.table__form", function() {
    reassignHandler.check();
    closeHandler.check();
  });
});
