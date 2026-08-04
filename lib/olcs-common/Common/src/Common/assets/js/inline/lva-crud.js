OLCS.ready(function() {
    
  "use strict";

  var tableSelector = "form [data-group*='table']";

  /**
   * Always bind some generic edit and delete buttons as they're
   * common across most (all?) CRUD forms
   */
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

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Transfer",
    predicate: function (length, callback) {
      callback(length >= 1);
    }
  });

  OLCS.crudTableHandler({
      selector : '.trigger-modal'
  });

});
