OLCS.ready(function () {
  "use strict";

  var $appendixType = $("#appendixType");
  var $defaultContentGroup = $("#defaultContent").closest(".field");
  var $documentGroup = $("#document").closest(".field");

  function toggleFields() {
    var type = $appendixType.val();

    if (type === "editable") {
      $defaultContentGroup.show();
      $documentGroup.hide();
    } else {
      $defaultContentGroup.hide();
      $documentGroup.show();
    }
  }

  // Initial state
  if ($appendixType.length) {
    toggleFields();
    $appendixType.on("change", toggleFields);
  }
});
