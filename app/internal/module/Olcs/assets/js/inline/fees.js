OLCS.ready(function() {
  var buttonHandler = OLCS.conditionalButton({
    form: ".table__form",
    label: "Pay",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });
});
