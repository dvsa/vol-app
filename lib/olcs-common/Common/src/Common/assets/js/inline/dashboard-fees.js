OLCS.ready(function() {
  "use strict";

  var tableSelector = ".table__form";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Pay",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });

});
