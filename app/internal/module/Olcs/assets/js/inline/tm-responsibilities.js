OLCS.ready(function() {
  OLCS.conditionalButton({
    container: ".table__form",
    label: "Remove",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });
});
