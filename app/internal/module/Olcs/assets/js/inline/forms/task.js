OLCS.ready(function() {
  "use strict";

  var form = "form[name=task]";

  OLCS.cascadeInput({
    source: form + " #assignedToTeam",
    dest: form + " #assignedToUser",
    url: "/list/users-internal",
    emptyLabel: "Unassigned"
  });

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #subCategory",
    url: "/list/task-sub-categories",
    emptyLabel: "Please select"
  });
});
