OLCS.ready(function() {
  OLCS.tableHandler({
    table: ".table__form",
    container: ".table__form",
    formFilter: ".js-content--main"
  });

  OLCS.tableSorter({
    table: ".table__form",
    container: ".table__form",
    filter: ".table__form"
  });
});
