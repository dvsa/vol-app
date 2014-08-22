$(function() {
  OLCS.cascadeInput({
    source: "#assignedToTeam",
    dest: "#assignedToUser",
    process: function(value, done) {
      $.get("/tasks/users/" + value, done);
    },
    emptyLabel: "Unassigned"
  });

  OLCS.cascadeInput({
    source: "#category",
    dest: "#taskSubCategory",
    url: "/tasks/sub-categories",
    emptyLabel: "Please select"
  });
});
