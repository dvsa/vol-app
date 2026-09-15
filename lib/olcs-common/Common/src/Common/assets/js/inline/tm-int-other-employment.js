OLCS.ready(function() {
  "use strict";

  var tableSelector = ".table__form";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit",
    predicate: function (length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Delete",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });
});
