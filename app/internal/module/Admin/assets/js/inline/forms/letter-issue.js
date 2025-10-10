OLCS.ready(function () {
  "use strict";

  OLCS.cascadeInput({
    source: "#category",
    dest: "#subCategory",
    url: "/list/document-sub-categories",
    emptyLabel: "Please Select",
  });
});
