$(function() {
  OLCS.conditionalButton({
    form: ".table__form",
    label: "Generate",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });
});
