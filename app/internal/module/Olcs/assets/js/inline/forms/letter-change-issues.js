/**
 * Change the issues on a letter that has already been generated (VOL-7734).
 *
 * Loaded on the letter preview and edit pages. "Change issues" opens /letter/change-issues in a
 * modal; saving posts the ticked issues to /letter/update-issues. If that would lose the
 * caseworker's edits the server changes nothing and names them, and we ask before posting again
 * with confirmLoss.
 */
OLCS.ready(function () {
  "use strict";

  var FORM = "#change-issues-form";

  function escapeText(text) {
    return $("<span>").text(text).html();
  }

  function showError(message) {
    $("#change-issues-error-message").text(message);
    $("#change-issues-error").show().trigger("focus");
  }

  function loadModal(letterInstanceId, from) {
    $.ajax({
      url:
        "/letter/change-issues?id=" +
        encodeURIComponent(letterInstanceId) +
        "&from=" +
        encodeURIComponent(from),
      method: "GET",
      success: function (html) {
        OLCS.modal.show(html, "Change issues");
      },
      error: function () {
        OLCS.modal.show(
          '<p class="govuk-body">The issues could not be loaded. Please try again.</p>',
          "Change issues",
        );
      },
    });
  }

  // "Change issues" on the preview sidebar or the edit page
  $("body").on("click", "#change-issues-btn", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var letterInstanceId = $btn.data("letter-instance-id");
    var from = $btn.data("from");

    var unsaved =
      OLCS.letterEdit && OLCS.letterEdit.unsavedSectionNames
        ? OLCS.letterEdit.unsavedSectionNames()
        : [];

    if (unsaved.length === 0) {
      loadModal(letterInstanceId, from);
      return;
    }

    var listItems = unsaved
      .map(function (name) {
        return "<li>" + escapeText(name) + "</li>";
      })
      .join("");

    OLCS.modal.show(
      '<div class="govuk-body">' +
        "<p>You have unsaved changes in the following sections:</p>" +
        '<ul class="govuk-list govuk-list--bullet">' +
        listItems +
        "</ul>" +
        "<p>Save your changes first. If you change issues now, your unsaved changes will be lost.</p>" +
        '<div class="govuk-button-group">' +
        '<button type="button" class="govuk-button govuk-button--warning" id="change-issues-anyway">Change issues anyway</button>' +
        '<button type="button" class="govuk-button govuk-button--secondary js-change-issues-close">Stay and save</button>' +
        "</div></div>",
      "Unsaved changes",
    );

    $("#change-issues-anyway").on("click", function () {
      loadModal(letterInstanceId, from);
    });
  });

  // Not .modal__close: the modal styles that class as its header close icon, hiding the button
  $("body").on("click", ".js-change-issues-close", function (e) {
    e.preventDefault();
    OLCS.modal.hide();
  });

  // Issue type groups open and close like the create screen's
  $("body").on("click", FORM + " .letter-section__header", function (e) {
    e.preventDefault();
    var $section = $(this).closest(".letter-section");
    var $content = $section.find(".letter-section__content");
    var opening = !$content.is(":visible");
    $content.toggle(opening);
    $section.find(".letter-section__show-hide").text(opening ? "Hide" : "Show");
  });

  $("body").on("click", FORM + " #toggle-all-sections", function (e) {
    e.preventDefault();
    var $link = $(this);
    var opening = $link.text().indexOf("Show") === 0;
    var $form = $(FORM);
    $form.find(".letter-section__content").toggle(opening);
    $form.find(".letter-section__show-hide").text(opening ? "Hide" : "Show");
    $link.text(opening ? "Hide All Sections" : "Show All Sections");
  });

  function selectedIssueIds() {
    return $(FORM + " input[name='letterIssues[]']:checked")
      .map(function () {
        return $(this).val();
      })
      .get();
  }

  // Where to land once the issues are saved
  function afterSave(response) {
    var $form = $(FORM);
    var letterInstanceId = $form.data("letter-instance-id");

    if ($form.data("from") !== "edit") {
      window.location.reload();
      return;
    }

    var params = new URLSearchParams(window.location.search);
    var keep = function (name, stillThere) {
      return params.getAll(name).filter(function (id) {
        return (
          stillThere === undefined || stillThere.indexOf(Number(id)) !== -1
        );
      });
    };

    var sections = keep("sections[]", response.issueIds || []);
    (response.addedIssueIds || []).forEach(function (id) {
      if (sections.indexOf(String(id)) === -1) {
        sections.push(String(id));
      }
    });
    // a to-do that changed issue is a new row now, so follow it to its new id
    var moved = response.movedTodoIds || {};
    params.getAll("todos[]").forEach(function (id) {
      if (moved[id] !== undefined) {
        params.append("todos[]", String(moved[id]));
      }
    });
    var todos = keep("todos[]", response.todoIds || []);
    var appendices = keep("appendices[]");
    var letterSections = keep("letterSections[]");

    if (
      sections.length +
        todos.length +
        appendices.length +
        letterSections.length ===
      0
    ) {
      window.location.href =
        "/letter/preview?id=" + encodeURIComponent(letterInstanceId);
      return;
    }

    var url = "/letter/edit?id=" + encodeURIComponent(letterInstanceId);
    [
      ["sections[]", sections],
      ["appendices[]", appendices],
      ["letterSections[]", letterSections],
      ["todos[]", todos],
    ].forEach(function (pair) {
      pair[1].forEach(function (id) {
        url += "&" + pair[0] + "=" + encodeURIComponent(id);
      });
    });
    window.location.href = url;
  }

  function save(confirmLoss) {
    var $form = $(FORM);
    var issues = selectedIssueIds();

    $("#change-issues-error").hide();

    if (issues.length === 0 && String($form.data("has-appendices")) !== "1") {
      showError("Choose at least one issue");
      return;
    }

    var $buttons = $form.find(
      "#change-issues-save, #change-issues-confirm-continue",
    );
    $buttons.prop("disabled", true);

    $.ajax({
      url: "/letter/update-issues",
      method: "POST",
      data: {
        letterInstanceId: $form.data("letter-instance-id"),
        letterIssues: issues,
        confirmLoss: confirmLoss ? "1" : "",
        security: $form.find("input[name='security']").val(),
      },
      dataType: "json",
      success: function (response) {
        $buttons.prop("disabled", false);

        if (response.confirmationRequired) {
          $("#change-issues-confirm-list").html(
            (response.lostEdits || [])
              .map(function (name) {
                return "<li>" + escapeText(name) + "</li>";
              })
              .join(""),
          );
          $("#change-issues-select").hide();
          $("#change-issues-confirm").show();
          return;
        }

        afterSave(response);
      },
      error: function (xhr) {
        $buttons.prop("disabled", false);
        $("#change-issues-confirm").hide();
        $("#change-issues-select").show();
        var message = xhr.responseJSON && xhr.responseJSON.message;
        showError(
          message || "The issues could not be saved. Please try again.",
        );
      },
    });
  }

  $("body").on("click", FORM + " #change-issues-save", function (e) {
    e.preventDefault();
    save(false);
  });

  $("body").on(
    "click",
    FORM + " #change-issues-confirm-continue",
    function (e) {
      e.preventDefault();
      save(true);
    },
  );

  $("body").on("click", FORM + " #change-issues-confirm-cancel", function (e) {
    e.preventDefault();
    $("#change-issues-confirm").hide();
    $("#change-issues-select").show();
  });
});
