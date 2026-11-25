OLCS.ready(function () {
  "use strict";

  OLCS.cascadeInput({
    source: "#category",
    dest: "#subCategory",
    url: "/list/task-sub-categories",
    emptyLabel: "Please select",
  });
});
