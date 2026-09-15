OLCS.ready(function() {
  "use strict";

  if (document.body.className.search("internal") === -1) {
    return;
  }

  var tableSelector = "form [data-group*='table']";

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Void",
    predicate: function(length, callback) {
      callback(length >= 1);
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Restore",
    predicate: {
      attr: "status",
      allow: ["cl_sts_withdrawn", "cl_sts_suspended"]
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Stop",
    predicate: {
      attr: "status",
      allow: ["cl_sts_active"]
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Reprint",
    predicate: {
      attr: "status",
      allow: ["cl_sts_active"]
    }
  });

  OLCS.conditionalButton({
    container: tableSelector,
    label: "Annul",
    predicate: {
      attr: "status",
      allow: ["cl_sts_active","cl_sts_pending"]
    }
  });
});
