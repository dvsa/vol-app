$(function() {
  var form = "form[name=task]";

  OLCS.cascadeInput({
    source: form + " #assignedToTeam",
    dest: form + " #assignedToUser",
    url: "/tasks/users",
    emptyLabel: "Unassigned"
  });

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #taskSubCategory",
    url: "/tasks/sub-categories",
    emptyLabel: "Please select"
  });
});
