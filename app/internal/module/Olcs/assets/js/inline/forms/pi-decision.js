OLCS.ready(function() {
  var select   = "[name='fields[definition][]']";
  var textarea = "[name='fields[decisionNotes]']";
  var cache = {};

  function updateText(index) {
    if (cache[index]) {
      return;
    }

    var str = $(select)
    .find("option[value=" + index + "]")
    .text();

    cache[index] = true;

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
