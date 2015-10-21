OLCS.ready(function() {
  "use strict";

  var form = "form[name=fee]";

  // create a hidden element to hold the combined DateSelect field values and
  // bind change events to populate it. We can then use this to hook in to the
  // cascadeInput behaviour
  var createdDateSelector = $("form[name=fee]").find("[name^='fee-details[createdDate]']");
  var jsDate = $("<input>").attr({type: "hidden", id: "js-created-date", }).appendTo(form);
  $(document).on("change", createdDateSelector, function(e) {
    var dateParts = [];
    $.each(createdDateSelector, function(i, select) {
        dateParts.push(select.value);
    });
    // elements are in d/m/Y order, we want Y-m-d
    jsDate.val(dateParts.reverse().join('-'));
    jsDate.change(); // trigger change event on hidden field for cascadeInput
  });

  OLCS.cascadeInput({
    source: form + " #feeType",
    dest: form + " #amount",
    url: "/fee-type"
  });

  OLCS.cascadeInput({
    source: form + " #js-created-date",
    dest: form + " #feeType",
    url: "/fee-type-list"
    // todo get context from fee page
  });

});
