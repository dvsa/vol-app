OLCS.ready(function () {
  "use strict";

  // Track dirty state per issue and appendix
  var dirtyMap = {};

  var EDITOR_GROUPS =
    ".issue-editor-group, .appendix-editor-group, .section-editor-group, .todo-editor-group";

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

    // never rejects: a failed flush is logged and the input left as it was
    OLCS.editorjsFlush().then(save);
  }

  // What each editor last held that's saved: what it opened on, then what was last saved from it
  var savedBlocks = {};

  // Helper to set up dirty tracking on a hidden input
  function setupDirtyTracking($group, entityId) {
    dirtyMap[entityId] = false;

    var hiddenInput = $group.find("input[type='hidden']")[0];
    if (hiddenInput) {
      // The editor writes what it opened on once it's ready (twice, with a new timestamp):
      // that isn't a change, it's the starting point. Treating every write as a change marked
      // every editor unsaved as soon as the page loaded.
      var noteWrite = function (val) {
        if (!(entityId in savedBlocks)) {
          savedBlocks[entityId] = editorBlocks(val);
          return;
        }
        if (editorBlocks(val) !== savedBlocks[entityId]) {
          dirtyMap[entityId] = true;
          $group.find(".save-indicator").hide();
        }
      };

      // a hidden input's value is its attribute, so this also sees every write to .value
      var observer = new MutationObserver(function () {
        noteWrite(hiddenInput.getAttribute("value"));
      });
      observer.observe(hiddenInput, {
        attributes: true,
        attributeFilter: ["value"],
      });

      var descriptor = Object.getOwnPropertyDescriptor(
        HTMLInputElement.prototype,
        "value",
      );
      if (descriptor && descriptor.set) {
        var originalSetter = descriptor.set;
        Object.defineProperty(hiddenInput, "value", {
          set: function (val) {
            originalSetter.call(this, val);
            noteWrite(val);
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

  /**
   * The server's reason for a failed request, or the fallback.
   */
  function failureMessage(xhr, fallback) {
    try {
      return JSON.parse(xhr.responseText).message || fallback;
    } catch (e) {
      return fallback;
    }
  }

  /**
   * Saves (and to-do adds and deletes) go to the server one at a time: each writes the letter's
   * own row, so two at once on the same letter would clash and the second would be refused.
   */
  var lastRequest = $.Deferred().resolve().promise();

  function oneAtATime(send) {
    var previous = lastRequest;
    var finished = $.Deferred();
    lastRequest = finished.promise();
    previous.always(function () {
      send(function () {
        finished.resolve();
      });
    });
  }

  /**
   * Save one editor group. If it's typed into while the save is in flight, what's on screen isn't
   * what was saved, so it stays unsaved (and guarded) until it's saved again.
   *
   * request(content, heading, version) builds the body; saved(heading) runs after a save.
   */
  function saveEditor($btn, key, url, request, saved) {
    var $group = $btn.closest(EDITOR_GROUPS);
    var hiddenInput = $group.find("input[type='hidden']");
    var $heading = $group.find(".other-issue-heading");

    $btn.prop("disabled", true).text("Saving...");

    oneAtATime(function (done) {
      withFlushedEditors(function () {
        var content = hiddenInput.val();
        var heading = $heading.length ? $heading.val() : undefined;

        $.ajax({
          url: url,
          method: "POST",
          contentType: "application/json",
          data: JSON.stringify(request(content, heading, $btn.data("version"))),
        })
          // .then() rather than .done()/.fail(): if a handler throws, .always() below still
          // runs, so the button comes back and the next request in the queue still goes
          .then(
            function (response) {
              if (!response.success) {
                showError(response.message || "Failed to save changes");
                return;
              }
              var changedSince =
                editorBlocks(hiddenInput.val()) !== editorBlocks(content) ||
                ($heading.length && $heading.val() !== heading);
              savedBlocks[key] = editorBlocks(content);
              if (!changedSince) {
                dirtyMap[key] = false;
                $group.find(".save-indicator").show();
              }
              $btn.data("version", response.version);
              if (saved) {
                saved(heading);
              }
            },
            function (xhr) {
              showError(failureMessage(xhr, "Failed to save changes"));
            },
          )
          .always(function () {
            $btn.prop("disabled", false).text("Save changes");
            done();
          });
      });
    });
  }

  $(".save-issue-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var issueId = $btn.data("issue-id");
    var $group = $btn.closest(EDITOR_GROUPS);

    saveEditor(
      $btn,
      "issue-" + issueId,
      "/letter/save-issue-content",
      function (content, heading, version) {
        return {
          issueId: issueId,
          editedContent: content,
          version: version,
          // an Other issue's heading (VOL-7735); left out for standard issues
          editedHeading: heading,
        };
      },
      function (heading) {
        var $heading = $group.find(".other-issue-heading");
        if ($heading.length) {
          // a cleared heading falls back to the standing one, as the letter does
          $group
            .find(".govuk-heading-s")
            .first()
            .text(heading.trim() || $heading.data("default-heading"));
        }
      },
    );
  });

  $(".save-appendix-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var appendixId = $btn.data("appendix-id");

    saveEditor(
      $btn,
      "appendix-" + appendixId,
      "/letter/save-appendix-content",
      function (content, heading, version) {
        return {
          appendixId: appendixId,
          editedContent: content,
          version: version,
        };
      },
    );
  });

  $(".save-section-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var sectionId = $btn.data("section-id");

    saveEditor(
      $btn,
      "section-" + sectionId,
      "/letter/save-section-content",
      function (content, heading, version) {
        return {
          sectionId: sectionId,
          editedContent: content,
          version: version,
        };
      },
    );
  });

  $(".save-todo-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    var todoId = $btn.data("todo-id");

    saveEditor(
      $btn,
      "todo-" + todoId,
      "/letter/save-todo-content",
      function (content, heading, version) {
        // editedDescription, not editedContent: a to-do's wording is called
        // description throughout its own code path, and the DTO matches.
        return { todoId: todoId, editedDescription: content, version: version };
      },
    );
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

  function postCustomTodo(path, body, $btn, exceptKey, done) {
    // the page reloads once this is done, so nothing else can be changed while it's in flight:
    // an edit made in the meantime would be lost
    var page = document.getElementById("main") || document.body;
    var unblock = function () {
      page.removeAttribute("inert");
      $btn.prop("disabled", false);
    };
    page.setAttribute("inert", "");
    $btn.prop("disabled", true);

    // Runs after any save that's already on its way. The unsaved check is done here, not on
    // the click, so a save that was in flight has finished and isn't listed as unsaved.
    oneAtATime(function (finished) {
      if (!okToLoseUnsaved(exceptKey)) {
        unblock();
        finished();
        return;
      }

      $.ajax({
        url: path,
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify(body),
      })
        // .then() so the queue carries on even if a handler throws (see saveEditor)
        .then(
          function (response) {
            if (response.success) {
              done(response);
            } else {
              unblock();
              showError(response.message || "Something went wrong");
            }
          },
          function (xhr) {
            unblock();
            showError(failureMessage(xhr, "Something went wrong"));
          },
        )
        .always(finished);
    });
  }

  // Add a caseworker-written to-do under an Other issue (VOL-7735)
  $(".add-custom-todo-btn").on("click", function (e) {
    e.preventDefault();
    var $btn = $(this);
    postCustomTodo(
      "/letter/add-custom-todo",
      { issueId: $btn.data("issue-id") },
      $btn,
      undefined,
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
    postCustomTodo(
      "/letter/delete-custom-todo",
      { todoId: todoId },
      $btn,
      // the to-do being deleted doesn't count as unsaved work
      "todo-" + todoId,
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
