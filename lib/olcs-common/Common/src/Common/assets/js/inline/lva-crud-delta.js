OLCS.ready(function() {
  "use strict";

  var tableSelector = "form [data-group*='table']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Edit",
    predicate: {
      max: 1,
      allow: ["E", "U", "A"]
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    selector: "#delete",
    predicate: {
      allow: ["A", "E", "U"]
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Restore",
    predicate: {
      allow: ["C", "D"]
    }
  });

  OLCS.crudTableHandler({
      selector : '.trigger-modal'
  });

});
