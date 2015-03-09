OLCS.ready(function() {
  var form = ".form__filter";

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
});
