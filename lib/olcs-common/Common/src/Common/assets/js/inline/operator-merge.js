$(function () {
  "use strict";

  $("#toOperatorId").change(function () {
    $("#toOperatorName").text("");
    var id = $(this).val();
    if (!isNaN(id - parseFloat(id))) {
      var ajaxUrl = $(this).data("lookup-url");
      $.getJSON(ajaxUrl + "?organisation=" + id, function (e) {
        if ($("#toOperatorName").length == 0) {
          var orgNameElement = $('<span id="toOperatorName"></span');
          $("#toOperatorId").after(orgNameElement);
        }
        $("#toOperatorName").text(e.name);
      });
    }
  });
});
