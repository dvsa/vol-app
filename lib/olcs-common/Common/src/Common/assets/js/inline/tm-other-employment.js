OLCS.ready(function() {
  "use strict";

  var tableSelector = "form [data-group*='otherEmployment']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit-employment",
    predicate: function (length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Delete-employment",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });
});
