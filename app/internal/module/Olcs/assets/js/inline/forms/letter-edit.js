OLCS.ready(function () {
  "use strict";

  // Track dirty state per issue and appendix
  var dirtyMap = {};

  /**
   * Run a save once the editors' hidden inputs are guaranteed current.
   *
   * These handlers read the hidden input, which EditorJS refreshes on its own debounce.
   * Saving in direct response to a click can therefore send the content as it was
   * before the last keystrokes and still report success, losing the caseworker's edit
   * with nothing on screen to say so.
   *
   * Falls through untouched if the component is not present, so this file keeps working
   * on any page that has no editors.
   */
  function withFlushedEditors(save) {
    if (typeof OLCS.editorjsFlush !== "function") {
      save();
      return;
    }

    OLCS.editorjsFlush().then(save);
  }

  // Helper to set up dirty tracking on a hidden input
  function setupDirtyTracking($group, entityId) {
    dirtyMap[entityId] = false;

    var hiddenInput = $group.find("input[type='hidden']")[0];
    if (hiddenInput) {
      var observer = new MutationObserver(function () {
        dirtyMap[entityId] = true;
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
              dirtyMap[entityId] = true;
              $group.find(".save-indicator").hide();
            }
          },
          get: function () {
            return descriptor.get.call(this);
          },
        });
      }
    }
  }

  // Listen for EditorJS changes via hidden input mutations - issues
  $(".issue-editor-group").each(function () {
    var $group = $(this);
    var issueId = "issue-" + $group.data("issue-id");
    setupDirtyTracking($group, issueId);
  });

  // Listen for EditorJS changes via hidden input mutations - appendices
  $(".appendix-editor-group").each(function () {
    var $group = $(this);
    var appendixId = "appendix-" + $group.data("appendix-id");
    setupDirtyTracking($group, appendixId);
  });

  // Listen for EditorJS changes via hidden input mutations - sections
  $(".section-editor-group").each(function () {
    var $group = $(this);
    var sectionKey = "section-" + $group.data("section-id");
    setupDirtyTracking($group, sectionKey);
  });

  // Listen for EditorJS changes via hidden input mutations - to-dos
  $(".todo-editor-group").each(function () {
    var $group = $(this);
    var todoKey = "todo-" + $group.data("todo-id");
    setupDirtyTracking($group, todoKey);
  });

  /**
   * An editor's content without its timestamp, which EditorJS refreshes on its own (e.g. just after
   * load) without anything having changed.
   */
  function editorBlocks(json) {
    try {
      return JSON.stringify(JSON.parse(json).blocks);
    } catch (e) {
      return json;
    }
  }

  // Save issue button handler
  $(".save-issue-btn").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var issueId = $btn.data("issue-id");
    var version = $btn.data("version");
    var $group = $btn.closest(".issue-editor-group");
    var hiddenInput = $group.find("input[type='hidden']");
    var $heading = $group.find(".other-issue-heading");

    withFlushedEditors(function () {
      var editedContent = hiddenInput.val();
      var sentHeading = $heading.length ? $heading.val() : undefined;

      $btn.prop("disabled", true).text("Saving...");

      $.ajax({
        url: "/letter/save-issue-content",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          issueId: issueId,
          editedContent: editedContent,
          version: version,
          // an Other issue's heading (VOL-7735); left out for standard issues
          editedHeading: sentHeading,
        }),
        success: function (response) {
          if (response.success) {
            // typed into while saving: what's on screen now isn't what was saved
            var changedSince =
              editorBlocks(hiddenInput.val()) !== editorBlocks(editedContent) ||
              ($heading.length && $heading.val() !== sentHeading);
            if (!changedSince) {
              dirtyMap["issue-" + issueId] = false;
              $group.find(".save-indicator").show();
            }
            if ($heading.length) {
              // a cleared heading falls back to the standing one, as the letter does
              $group
                .find(".govuk-heading-s")
                .first()
                .text($.trim(sentHeading) || $heading.data("default-heading"));
            }
            $btn.data("version", response.version);
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
  });

  // Save appendix button handler
  $(".save-appendix-btn").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var appendixId = $btn.data("appendix-id");
    var version = $btn.data("version");
    var $group = $btn.closest(".appendix-editor-group");
    var hiddenInput = $group.find("input[type='hidden']");

    withFlushedEditors(function () {
      var editedContent = hiddenInput.val();

      $btn.prop("disabled", true).text("Saving...");

      $.ajax({
        url: "/letter/save-appendix-content",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          appendixId: appendixId,
          editedContent: editedContent,
          version: version,
        }),
        success: function (response) {
          if (response.success) {
            dirtyMap["appendix-" + appendixId] = false;
            $btn.data("version", response.version);
            $group.find(".save-indicator").show();
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
  });

  // Save section button handler
  $(".save-section-btn").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var sectionId = $btn.data("section-id");
    var version = $btn.data("version");
    var $group = $btn.closest(".section-editor-group");
    var hiddenInput = $group.find("input[type='hidden']");

    withFlushedEditors(function () {
      var editedContent = hiddenInput.val();

      $btn.prop("disabled", true).text("Saving...");

      $.ajax({
        url: "/letter/save-section-content",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          sectionId: sectionId,
          editedContent: editedContent,
          version: version,
        }),
        success: function (response) {
          if (response.success) {
            dirtyMap["section-" + sectionId] = false;
            $btn.data("version", response.version);
            $group.find(".save-indicator").show();
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
  });

  // Save to-do button handler
  $(".save-todo-btn").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var todoId = $btn.data("todo-id");
    var version = $btn.data("version");
    var $group = $btn.closest(".todo-editor-group");
    var hiddenInput = $group.find("input[type='hidden']");

    withFlushedEditors(function () {
      var editedDescription = hiddenInput.val();

      $btn.prop("disabled", true).text("Saving...");

      $.ajax({
        url: "/letter/save-todo-content",
        method: "POST",
        contentType: "application/json",
        // editedDescription, not editedContent: a to-do's wording is called
        // description throughout its own code path, and the DTO matches.
        data: JSON.stringify({
          todoId: todoId,
          editedDescription: editedDescription,
          version: version,
        }),
        success: function (response) {
          if (response.success) {
            dirtyMap["todo-" + todoId] = false;
            $btn.data("version", response.version);
            $group.find(".save-indicator").show();
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
  });

  // An Other issue's heading counts as an unsaved change until saved (VOL-7735)
  $(".other-issue-heading").on("input", function () {
    var $group = $(this).closest(".issue-editor-group");
    dirtyMap["issue-" + $group.data("issue-id")] = true;
    $group.find(".save-indicator").hide();
  });

  /**
   * Adding or deleting a caseworker-written to-do reloads the page (editors only start up on
   * load), so say what unsaved work would be lost first. True when it's fine to go ahead.
   */
  function okToLoseUnsaved(exceptKey) {
    // the editor being deleted isn't "lost", so leave it out of the list
    var wasDirty = exceptKey ? dirtyMap[exceptKey] : undefined;
    if (exceptKey) {
      dirtyMap[exceptKey] = false;
    }

    var unsaved = collectUnsavedSectionNames();
    if (exceptKey) {
      // put it back either way: if the delete then fails, its unsaved text is still guarded
      dirtyMap[exceptKey] = wasDirty;
    }

    var ok =
      unsaved.length === 0 ||
      window.confirm(
        "This reloads the page. Unsaved changes in these sections will be lost:\n\n" +
          unsaved.join("\n") +
          "\n\nContinue?",
      );

    return ok;
  }

  /**
   * Reload the edit page with its to-dos changed, e.g. a new one added.
   */
  function reloadWithTodos(change) {
    var url = new URL(window.location.href);
    var todos = url.searchParams.getAll("todos[]");
    url.searchParams.delete("todos[]");
    change(todos).forEach(function (id) {
      url.searchParams.append("todos[]", id);
    });

    // nothing left to edit (the last ticked to-do was deleted): back to the preview
    var stillEditing = [
      "sections[]",
      "appendices[]",
      "letterSections[]",
      "todos[]",
    ].some(function (name) {
      return url.searchParams.getAll(name).length > 0;
    });
    if (!stillEditing) {
      window.location.href =
        "/letter/preview?id=" + encodeURIComponent(url.searchParams.get("id"));
      return;
    }

    window.location.href = url.toString();
  }

  function postCustomTodo(path, body, $btn, done) {
    $btn.prop("disabled", true);
    $.ajax({
      url: path,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(body),
      success: function (response) {
        if (response.success) {
          done(response);
        } else {
          showError(response.message || "Something went wrong");
          $btn.prop("disabled", false);
        }
      },
      error: function (xhr) {
        var message = "Something went wrong";
        try {
          message = JSON.parse(xhr.responseText).message || message;
        } catch (e) {
          // Use default message
        }
        showError(message);
        $btn.prop("disabled", false);
      },
    });
  }

  // Add a caseworker-written to-do under an Other issue (VOL-7735)
  $(".add-custom-todo-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    if (!okToLoseUnsaved()) {
      return;
    }
    postCustomTodo(
      "/letter/add-custom-todo",
      { issueId: $btn.data("issue-id") },
      $btn,
      function (response) {
        reloadWithTodos(function (todos) {
          return todos.concat([String(response.todoId)]);
        });
      },
    );
  });

  // Delete a caseworker-written to-do (VOL-7735)
  $(".delete-custom-todo-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var todoId = String($btn.data("todo-id"));
    if (!window.confirm("Delete this to-do? This cannot be undone.")) {
      return;
    }
    if (!okToLoseUnsaved("todo-" + todoId)) {
      return;
    }
    postCustomTodo(
      "/letter/delete-custom-todo",
      { todoId: todoId },
      $btn,
      function () {
        reloadWithTodos(function (todos) {
          return todos.filter(function (id) {
            return id !== todoId;
          });
        });
      },
    );
  });

  /**
   * Headings of the editors on this page with unsaved changes. Shared with the change issues
   * modal (letter-change-issues.js), which warns before leaving the page the same way.
   */
  function collectUnsavedSectionNames() {
    var unsavedSections = [];

    $(".issue-editor-group").each(function () {
      var $group = $(this);
      var issueId = "issue-" + $group.data("issue-id");
      if (dirtyMap[issueId]) {
        var heading = $group.find(".govuk-heading-s").first().text().trim();
        unsavedSections.push(heading);
      }
    });

    $(".appendix-editor-group").each(function () {
      var $group = $(this);
      var appendixId = "appendix-" + $group.data("appendix-id");
      if (dirtyMap[appendixId]) {
        var heading = $group.find(".govuk-heading-s").first().text().trim();
        unsavedSections.push(heading);
      }
    });

    $(".section-editor-group").each(function () {
      var $group = $(this);
      var sectionKey = "section-" + $group.data("section-id");
      if (dirtyMap[sectionKey]) {
        var heading = $group.find(".govuk-heading-s").first().text().trim();
        unsavedSections.push(heading);
      }
    });

    $(".todo-editor-group").each(function () {
      var $group = $(this);
      var todoKey = "todo-" + $group.data("todo-id");
      if (dirtyMap[todoKey]) {
        var heading = $group.find(".govuk-heading-s").first().text().trim();
        unsavedSections.push(heading);
      }
    });

    return unsavedSections;
  }

  OLCS.letterEdit = { unsavedSectionNames: collectUnsavedSectionNames };

  // Back to preview handler with unsaved changes warning
  $("#back-to-preview").on("click", function (e) {
    var unsavedSections = collectUnsavedSectionNames();

    if (unsavedSections.length === 0) {
      return;
    }

    e.preventDefault();

    var href = $(this).attr("href");

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
