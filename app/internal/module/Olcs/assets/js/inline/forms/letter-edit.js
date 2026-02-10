OLCS.ready(function () {
  "use strict";

  // Track dirty state per issue
  var dirtyMap = {};

  // Listen for EditorJS changes via hidden input mutations
  $(".issue-editor-group").each(function () {
    var $group = $(this);
    var issueId = $group.data("issue-id");
    dirtyMap[issueId] = false;

    // Watch for changes to the hidden input (EditorJS component syncs content here)
    var hiddenInput = $group.find("input[type='hidden']")[0];
    if (hiddenInput) {
      var observer = new MutationObserver(function () {
        dirtyMap[issueId] = true;
        // Hide saved indicator when content changes
        $group.find(".save-indicator").hide();
      });
      observer.observe(hiddenInput, {
        attributes: true,
        attributeFilter: ["value"],
      });

      var originalValue = hiddenInput.value;
      var descriptor = Object.getOwnPropertyDescriptor(
        HTMLInputElement.prototype,
        "value",
      );
      if (descriptor && descriptor.set) {
        var originalSetter = descriptor.set;
        Object.defineProperty(hiddenInput, "value", {
          set: function (val) {
            originalSetter.call(this, val);
            if (val !== originalValue) {
              dirtyMap[issueId] = true;
              $group.find(".save-indicator").hide();
            }
          },
          get: function () {
            return descriptor.get.call(this);
          },
        });
      }
    }
  });

  // Save button handler
  $(".save-issue-btn").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var issueId = $btn.data("issue-id");
    var version = $btn.data("version");
    var $group = $btn.closest(".issue-editor-group");
    var hiddenInput = $group.find("input[type='hidden']");
    var editedContent = hiddenInput.val();

    // Disable button during save
    $btn.prop("disabled", true).text("Saving...");

    $.ajax({
      url: "/letter/save-issue-content",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        issueId: issueId,
        editedContent: editedContent,
        version: version,
      }),
      success: function (response) {
        if (response.success) {
          // Mark as clean
          dirtyMap[issueId] = false;

          // Update version for optimistic locking
          $btn.data("version", response.version);

          // Show saved indicator
          $group.find(".save-indicator").show();

          // Restore button
          $btn.prop("disabled", false).text("Save changes");
        } else {
          showError(response.message || "Failed to save changes");
          $btn.prop("disabled", false).text("Save changes");
        }
      },
      error: function (xhr) {
        var message = "Failed to save changes";
        try {
          var resp = JSON.parse(xhr.responseText);
          if (resp.message) {
            message = resp.message;
          }
        } catch (e) {
          // Use default message
        }
        showError(message);
        $btn.prop("disabled", false).text("Save changes");
      },
    });
  });

  // Back to preview handler with unsaved changes warning
  $("#back-to-preview").on("click", function (e) {
    var unsavedSections = [];
    $(".issue-editor-group").each(function () {
      var $group = $(this);
      var issueId = $group.data("issue-id");
      if (dirtyMap[issueId]) {
        var heading = $group.find("h3").text().trim();
        unsavedSections.push(heading);
      }
    });

    if (unsavedSections.length === 0) {
      // All clean, allow normal navigation
      return;
    }

    // Prevent navigation
    e.preventDefault();

    var href = $(this).attr("href");

    // Build modal content
    var listItems = unsavedSections
      .map(function (name) {
        return "<li>" + $("<span>").text(name).html() + "</li>";
      })
      .join("");

    var modalBody =
      '<div class="govuk-body">' +
      "<p>You have unsaved changes in the following sections:</p>" +
      '<ul class="govuk-list govuk-list--bullet">' +
      listItems +
      "</ul>" +
      "<p>If you go back, your unsaved changes will be lost.</p>" +
      '<div style="margin-top: 20px;">' +
      '<a href="' +
      href +
      '" class="govuk-button govuk-button--warning" style="margin-right: 10px;">Go back anyway</a>' +
      '<button type="button" class="govuk-button govuk-button--secondary modal__close">Stay and save</button>' +
      "</div></div>";

    OLCS.modal.show(modalBody, "Unsaved changes");
  });

  /**
   * Show error in the error summary
   */
  function showError(message) {
    var $errorSummary = $("#edit-error-summary");
    var $errorList = $("#edit-error-list");
    $errorList.html("<li>" + $("<span>").text(message).html() + "</li>");
    $errorSummary.show().focus();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
});
