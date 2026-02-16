OLCS.ready(function () {
  "use strict";

  // Enable/disable "Continue to editor" based on checkbox selection
  $("input[name='sections[]'], input[name='appendices[]']").on(
    "change",
    function () {
      var anyChecked =
        $("input[name='sections[]']:checked").length > 0 ||
        $("input[name='appendices[]']:checked").length > 0;
      $("#continue-to-editor").prop("disabled", !anyChecked);
      if (anyChecked) {
        $("#sections-error-summary").remove();
      }
    },
  );

  // Handle "Continue to editor" button click
  $("#continue-to-editor").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var letterInstanceId = $btn.data("letter-instance-id");

    // Collect selected section checkboxes
    var selectedSections = [];
    $("input[name='sections[]']:checked").each(function () {
      selectedSections.push($(this).val());
    });

    // Collect selected appendix checkboxes
    var selectedAppendices = [];
    $("input[name='appendices[]']:checked").each(function () {
      selectedAppendices.push($(this).val());
    });

    // Remove any existing error summary
    $("#sections-error-summary").remove();

    // Validate at least one section or appendix is checked
    if (selectedSections.length === 0 && selectedAppendices.length === 0) {
      var errorHtml =
        '<div id="sections-error-summary" class="govuk-error-summary" data-module="govuk-error-summary" role="alert" tabindex="-1">' +
        '<h2 class="govuk-error-summary__title">There is a problem</h2>' +
        '<div class="govuk-error-summary__body">' +
        '<ul class="govuk-list govuk-error-summary__list">' +
        "<li>Please select at least one section or appendix to edit</li>" +
        "</ul></div></div>";

      $btn.closest(".small-module").before(errorHtml);
      $("#sections-error-summary").focus();
      return;
    }

    // Build URL with selected sections and appendices
    var url = "/letter/edit?id=" + encodeURIComponent(letterInstanceId);
    selectedSections.forEach(function (sectionId) {
      url += "&sections[]=" + encodeURIComponent(sectionId);
    });
    selectedAppendices.forEach(function (appendixId) {
      url += "&appendices[]=" + encodeURIComponent(appendixId);
    });

    window.location.href = url;
  });

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
