OLCS.ready(function () {
  "use strict";

  // Handle "Save letter and exit" button click
  $("#save-letter-exit").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var letterInstanceId = $btn.data("letter-instance-id");

    // Disable button and show loading state
    $btn.prop("disabled", true).text("Saving...");

    // For now, simulate save (actual API call can be added in future ticket)
    // The letter instance was already created/saved when generated
    setTimeout(function () {
      // Show success banner
      $("#success-banner").show();

      // Update button state
      $btn.text("Saved").addClass("govuk-button--secondary");

      // Scroll to top to show banner
      window.scrollTo({ top: 0, behavior: "smooth" });
    }, 500);
  });
});
