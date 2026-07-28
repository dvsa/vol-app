OLCS.ready(function () {
  "use strict";

  // Enable/disable "Continue to editor" based on checkbox selection
  $(
    "input[name='sections[]'], input[name='appendices[]'], input[name='letterSections[]']",
  ).on("change", function () {
    var anyChecked =
      $("input[name='sections[]']:checked").length > 0 ||
      $("input[name='appendices[]']:checked").length > 0 ||
      $("input[name='letterSections[]']:checked").length > 0;
    $("#continue-to-editor").prop("disabled", !anyChecked);
    if (anyChecked) {
      $("#sections-error-summary").remove();
    }
  });

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

    // Collect selected letter instance section checkboxes
    var selectedLetterSections = [];
    $("input[name='letterSections[]']:checked").each(function () {
      selectedLetterSections.push($(this).val());
    });

    // Remove any existing error summary
    $("#sections-error-summary").remove();

    // Validate at least one section, appendix, or letter section is checked
    if (
      selectedSections.length === 0 &&
      selectedAppendices.length === 0 &&
      selectedLetterSections.length === 0
    ) {
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

    // Build URL with selected sections, appendices and letter instance sections
    var url = "/letter/edit?id=" + encodeURIComponent(letterInstanceId);
    selectedSections.forEach(function (sectionId) {
      url += "&sections[]=" + encodeURIComponent(sectionId);
    });
    selectedAppendices.forEach(function (appendixId) {
      url += "&appendices[]=" + encodeURIComponent(appendixId);
    });
    selectedLetterSections.forEach(function (letterSectionId) {
      url += "&letterSections[]=" + encodeURIComponent(letterSectionId);
    });

    window.location.href = url;
  });

  // Handle "Save letter and exit" button click.
  //
  // Nothing is written from this page: the letter_instance row is persisted by
  // generateAction, and section, issue and appendix edits are each saved via their own
  // AJAX call on /letter/edit. So this button confirms rather than saves.
  //
  // It must not confirm unconditionally, though. Sections flagged "Input required" still
  // hold placeholder text at this point, and the only enforcement of that lives in the
  // PrepareToSend command handler -- a path this button does not go through. Reporting
  // "saved" over an unfilled placeholder is how a letter reaches an operator still
  // reading "*** Free Text ***".
  $("#save-letter-exit").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var $error = $("#input-required-error");
    var $errorList = $("#input-required-error-list");
    var pending = $("[data-input-pending='1']");

    if (pending.length) {
      $errorList.empty();
      pending.each(function () {
        var name =
          $(this).data("section-name") || $(this).find("label").text().trim();
        $errorList.append($("<li>").text(name));
      });

      $("#success-banner").hide();
      $error.show();
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    $error.hide();
    $("#success-banner").show();
    $btn
      .prop("disabled", true)
      .text("Saved")
      .addClass("govuk-button--secondary");
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
});
