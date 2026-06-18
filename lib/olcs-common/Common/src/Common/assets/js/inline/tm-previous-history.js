OLCS.ready(function() {
  "use strict";

  // We have multiple tables on this form, so need to handle these differently
  var tableSelector = "form [data-group*='convictions']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit-previous-conviction",
    predicate: function (length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Delete-previous-conviction",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });

  tableSelector = "form [data-group*='previousLicences']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit-previous-licence",
    predicate: function (length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Delete-previous-licence",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });
});
