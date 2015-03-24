OLCS.ready(function() {
  "use strict";

  var select   = ".js-definition-source";
  var textarea = ".js-definition-target";

  function updateText(index) {
    var str = $(select)
    .find("option[value=" + index + "]")
    .text();
    var txtArea = $(textarea).val();

    if (txtArea != '') {
      $(textarea).val(
        $(textarea).val() + "\n"
      );
    }
    $(textarea).val(
      $(textarea).val() + str
    );
  }

  $(document).on("change", select, function() {
    var values = $(this).val();
    for (var i = 0, j = values.length; i < j; i++) {
      updateText(values[i]);
    }
  });
});
