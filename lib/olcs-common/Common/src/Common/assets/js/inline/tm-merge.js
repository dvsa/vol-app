$(function () {
  "use strict";

  $("#toTmId").change(function () {
    $("#toTmName").text("");
    var id = $(this).val();
    if (!isNaN(id - parseFloat(id))) {
      var ajaxUrl = $(this).data("lookup-url");
      $.getJSON(ajaxUrl + "?transportManager=" + id, function (e) {
        if ($("#toTmName").length == 0) {
          var element = $('<span id="toTmName"></span');
          $("#toTmId").after(element);
        }
        $("#toTmName").text(e.name);
      });
    }
  });
});
