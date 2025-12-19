OLCS.ready(function () {
  "use strict";

  // Handle individual section toggles
  $("body").on("click", ".letter-section__header", function (e) {
    e.preventDefault();
    var $section = $(this).closest(".letter-section");
    var $content = $section.find(".letter-section__content");
    var $button = $(this).find(".letter-section__toggle");

    if ($content.is(":visible")) {
      $content.hide();
      $button.text("Show");
      $section.removeClass("letter-section--expanded");
    } else {
      $content.show();
      $button.text("Hide");
      $section.addClass("letter-section--expanded");
    }
  });

  // Handle "Show All Sections" / "Hide All Sections" toggle
  $("body").on("click", "#toggle-all-sections", function (e) {
    e.preventDefault();
    var $allSections = $(".letter-section");
    var $allContent = $(".letter-section__content");
    var allVisible =
      $allContent.filter(":visible").length === $allContent.length;

    if (allVisible) {
      $allContent.hide();
      $(".letter-section__toggle").text("Show");
      $allSections.removeClass("letter-section--expanded");
      $(this).text("Show All Sections");
    } else {
      $allContent.show();
      $(".letter-section__toggle").text("Hide");
      $allSections.addClass("letter-section--expanded");
      $(this).text("Hide All Sections");
    }
  });

  // Function to update button state based on checkbox selection
  function updateCreateButtonState() {
    var $checkboxes = $('input[name="letterIssues[]"]:checked');
    var $createBtn = $("#create-letter-btn");

    if ($checkboxes.length > 0) {
      $createBtn.prop("disabled", false).removeClass("govuk-button--disabled");
    } else {
      $createBtn.prop("disabled", true).addClass("govuk-button--disabled");
    }
  }

  // Create letter button click - submit form via AJAX
  $("body").on("click", "#create-letter-btn", function (e) {
    e.preventDefault();

    var $form = $("#letter-create-form");
    var $allCheckboxes = $('input[name="letterIssues[]"]');
    var $checkboxes = $('input[name="letterIssues[]"]:checked');
    var $errorDiv = $("#validation-error");
    var $button = $(this);

    // Validate at least one issue is selected
    if ($checkboxes.length === 0) {
      $errorDiv.show();
      $errorDiv[0].scrollIntoView({ behavior: "smooth", block: "nearest" });
      return false;
    }

    // Hide error
    $errorDiv.hide();

    // Disable button and show loading state
    $button.prop("disabled", true).text("Creating letter...");

    // Get the generate URL from the form's data attribute
    var generateUrl = $form.data("generate-url");

    // Submit form data
    $.ajax({
      url: generateUrl,
      type: "POST",
      data: $form.serialize(),
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Hide the form
          $form.hide();
          $("#toggle-all-sections").hide();

          // Display the debug output
          var $debugOutput = $("#letter-debug-output");
          var $debugContent = $("#debug-content");

          // Format the letter instance data as readable JSON
          var formattedData = JSON.stringify(response.letterInstance, null, 2);
          $debugContent.text(formattedData);

          // Show the debug section
          $debugOutput.show();

          // Change button to "View in System" that redirects
          $button
            .text("View in System")
            .prop("disabled", false)
            .off("click")
            .on("click", function () {
              if (response.redirectUrl) {
                window.location.href = response.redirectUrl;
              }
            });
        } else {
          // Show error message
          $errorDiv
            .find("span:last")
            .text(response.message || "Failed to create letter");
          $errorDiv.show();
          $button.prop("disabled", false).text("Create letter");
        }
      },
      error: function (xhr, status, error) {
        var errorMessage = "An error occurred while creating the letter";

        try {
          var response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMessage = response.message;
          }
        } catch (e) {
          // Use default error message
        }

        $errorDiv.find("span:last").text(errorMessage);
        $errorDiv.show();
        $button.prop("disabled", false).text("Create letter");
      },
    });
  });

  // Update button state and hide messages when checkbox changes
  $("body").on("change", 'input[name="letterIssues[]"]', function () {
    updateCreateButtonState();
    var $checkboxes = $('input[name="letterIssues[]"]:checked');
    if ($checkboxes.length > 0) {
      $("#validation-error").hide();
    }
    $("#placeholder-message").hide(); // Hide placeholder when selection changes
  });

  // Cancel button - close modal
  $("body").on("click", "#cancel-letter-btn", function (e) {
    e.preventDefault();
    if (typeof OLCS !== "undefined" && OLCS.modal && OLCS.modal.hide) {
      OLCS.modal.hide();
    }
  });

  // Set initial button state on page load
  updateCreateButtonState();
});
