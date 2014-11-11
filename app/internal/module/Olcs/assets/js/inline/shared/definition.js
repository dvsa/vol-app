OLCS.ready(function() {
  var select   = ".js-definition-source";
  var textarea = ".js-definition-target";

  function updateText(index) {
    var str = $(select)
    .find("option[value=" + index + "]")
    .text();

    $(textarea).val(
      $(textarea).val() + str + "\n"
    );
  }

  $(document).on("change", select, function(e) {
    var values = $(this).val();
    for (var i = 0, j = values.length; i < j; i++) {
      updateText(values[i]);
    }
  });
});
