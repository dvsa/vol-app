OLCS.ready(function() {
  "use strict";

  var form = "form[name=fee]";

  OLCS.cascadeInput({
    source: form + " #feeType",
    dest: form + " #amount",
    url: "/list/fee-type"
  });

});
