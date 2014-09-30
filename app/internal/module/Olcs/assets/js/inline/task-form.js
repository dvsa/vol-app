OLCS.ready(function() {
  var form = "form[name=task]";

  OLCS.cascadeInput({
    source: form + " #assignedToTeam",
    dest: form + " #assignedToUser",
    url: "/list/users",
    emptyLabel: "Unassigned"
  });

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #taskSubCategory",
    url: "/list/task-sub-categories",
    emptyLabel: "Please select"
  });
});
