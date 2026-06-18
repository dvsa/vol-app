OLCS.ready(function() {
  "use strict";

  OLCS.formHandler({
    form: ".table__form",
    isModal: true,
    onChange: false,
    success: OLCS.modalResponse(".js-body"),
    container: ".js-body"
  });

  OLCS.tableSorter({
    table: ".table__form",
    // where we'll render any response data to
    container: ".table__form",
    // filter the data returned from the server to only
    // contain content within this element
    filter: ".table__form"
  });
});
