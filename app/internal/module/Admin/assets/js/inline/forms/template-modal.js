$(function () {
  "use strict";

  // VOL-7238: detect unsaved edits on the source textarea so we can warn the admin before
  // a sibling-pill click discards them. We use the element's native `defaultValue` property,
  // which the browser sets to the HTML-rendered content at parse time and DOES NOT update
  // when the user types — so .value !== .defaultValue is a perfect dirty check. This works
  // across AJAX swaps too, because each new edit form rendered into the modal carries its
  // own freshly-parsed textarea with its own correct defaultValue. A cached JS variable
  // would go stale on swap; defaultValue does not.
  function isSourceDirty() {
    var el = document.getElementById("source");
    if (!el) {
      return false;
    }
    return el.value !== el.defaultValue;
  }

  var modalTemplate =
    '<div class="modal previewModal">' +
    '  <div class="modal__header"><h1 class="modal__title previewTitle"></h1></div>' +
    '  <div class="modal__content previewContent">' +
    '    <div class="js-content"></div>' +
    '      <div class="previewControls"><select id="dataSetSelect" class="js-hidden"></select><a id="previewClose">Back to Edit</a></div>' +
    '      <div id="previewPane"></div>' +
    "    </div>" +
    "  </div>" +
    "</div>";

  var previewData = {};

  function populatePreview() {
    var previewContent = previewData[$("#dataSetSelect").val()];
    if ($("#format").val() === "plain") {
      previewContent = '<pre class="wordwrap">' + previewContent + "</pre>";
    }

    $("#dataSetSelect").removeClass("js-hidden");
    $("#previewPane").html(previewContent);
  }

  function hideEditShowPreview() {
    // Two entry points: (a) opened as a modal from the list page — there's a `.modal`
    // and `.modal__wrapper` we hide and inject into; (b) navigated directly to
    // /admin/email-templates/edit/{id}/ (e.g. via VOL-7238 sibling pills) — no modal
    // wrapper exists, so fall back to hiding the form and injecting next to it.
    if ($(".modal__wrapper").length) {
      $(".modal").addClass("editModal js-hidden");
      $(".modal__wrapper").prepend(modalTemplate);
    } else {
      var $form = $("#source").closest("form");
      $form.addClass("editModal js-hidden");
      $form.before(modalTemplate);
    }
    $(".previewTitle").html("Preview: " + $("#description").val());
  }

  $("#preview").click(function () {
    $(this).html("Please Wait");

    // Perform an xhr POST with the template ID, and current source from edit window.
    var previewPost = $.post($("#jsonUrl").val(), {
      source: $("#source").val(),
      id: $("#id").val(),
      security: $("#security").val(),
    });

    // POST success handler - Preview worked
    previewPost.done(function (data) {
      //Remove unnecessary var set for debugging purposes and populate var in parent scope.
      delete data.correlationId;
      previewData = data;

      hideEditShowPreview();

      //For each dataset in the JSON payload add an entry to the select box.
      $.each(data, function (i, item) {
        $("#dataSetSelect").append(
          $("<option>", {
            value: i,
            text: i,
          }),
        );
      });

      populatePreview();
    });

    // POST error handler - Preview render failed - show error
    previewPost.fail(function (data) {
      hideEditShowPreview();
      delete data.responseJSON.correlationId;
      $.each(data.responseJSON, function (dataset, error) {
        $("#previewPane").html(
          "<h3>Dataset: " +
            dataset +
            "</h3>" +
            "<pre class='wordwrap'>" +
            error +
            "</pre>",
        );
      });
    });
  });

  // When the dataset select box changes, call populate helper function.
  $(document).on("change", "#dataSetSelect", function () {
    populatePreview();
  });

  // When Cancel is clicked, kill the preview div, untag and unhide the original edit UI
  // (either the modal — list-page flow — or the form itself — direct-URL flow).
  $(document).on("click", "#previewClose", function () {
    $(".previewModal").remove();
    $("#preview").html("Preview");
    $(".modal").removeClass("editModal js-hidden");
    $("#source").closest("form").removeClass("editModal js-hidden");
    $("#dataSetSelect").addClass("js-hidden");
  });

  // VOL-7238: clicking a sibling pill (`.js-template-sibling`) keeps the admin inside
  // the edit modal instead of dropping out to a standalone /edit/{id}/ page. We prompt
  // about unsaved edits, then re-use the existing OLCS modal-AJAX infrastructure to
  // swap the sibling's edit form into the same modal in place.
  //
  // jQuery's delegated-handler order: we bind this BEFORE the global `.js-modal-ajax`
  // binding (which is set up by the page bootstrap) by using stopImmediatePropagation
  // on cancel. On confirm we trigger the load ourselves.
  $(document).on("click", ".js-template-sibling", function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    var href = $(this).attr("href");
    if (!href) {
      return;
    }

    if (isSourceDirty()) {
      var ok = window.confirm(
        "You have unsaved changes to this template. Discard them and switch to the other version?",
      );
      if (!ok) {
        return;
      }
    }

    if (typeof OLCS !== "undefined" && OLCS.ajax && OLCS.modalResponse) {
      // Inside a modal: re-use the OLCS modal-loading flow so the sibling lands in
      // the same modal wrapper.
      OLCS.ajax({
        url: href,
        success: OLCS.modalResponse(),
        preloaderType: "modal",
      });
    } else {
      // Fallback for standalone /edit/{id}/ pages without the modal infrastructure
      // (no `.modal__wrapper` in the DOM) — plain navigation.
      window.location.href = href;
    }
  });

  // VOL-7238: "Send test via Notify" — only present on md template edit modals when the
  // env has a notify_test DSN configured (the controller removes the button otherwise).
  // Click hides the button and reveals an inline panel below the form-actions row:
  // email input + env-aware hint + Send / Cancel. Send POSTs to sendTestEmail; Cancel
  // restores the button. There's only ever one #sendTestViaNotify on the page so we use
  // document-scoped selectors to find it from inside the panel handlers.
  $(document).on("click", "#sendTestViaNotify", function () {
    var button = $(this);
    if ($(".js-send-test-panel").length) {
      return; // panel already showing
    }

    var url = button.data("send-test-url");
    var templateId = button.data("template-id");
    var hint = button.data("send-test-hint") || "";

    if (!url || !templateId) {
      return;
    }

    var panel = $(
      '<div class="js-send-test-panel govuk-inset-text govuk-!-margin-top-3" ' +
        '    role="group" aria-label="Send test via GOV.UK Notify">' +
        '  <div class="govuk-form-group">' +
        '    <label class="govuk-label" for="sendTestRecipient">Recipient email address</label>' +
        '    <p class="govuk-hint js-send-test-hint"></p>' +
        '    <input class="govuk-input govuk-!-width-two-thirds" type="email" ' +
        '           id="sendTestRecipient" autocomplete="email" />' +
        "  </div>" +
        '  <div class="govuk-button-group">' +
        '    <button type="button" class="govuk-button" id="sendTestSubmit">' +
        "      Send to Notify" +
        "    </button>" +
        '    <button type="button" class="govuk-button govuk-button--secondary" id="sendTestCancel">' +
        "      Cancel" +
        "    </button>" +
        "  </div>" +
        "</div>",
    );
    panel.find(".js-send-test-hint").text(hint);

    // Insert AFTER the button's button-group container so the panel sits on its own
    // line below the Save/Cancel/Preview row, not inline beside it.
    var buttonGroup = button.closest(".govuk-button-group");
    if (buttonGroup.length) {
      buttonGroup.after(panel);
    } else {
      button.after(panel);
    }
    button.hide();
    panel.find("#sendTestRecipient").trigger("focus");
  });

  function dismissSendTestPanel() {
    $(".js-send-test-panel").remove();
    $("#sendTestViaNotify").show();
  }

  $(document).on("click", "#sendTestCancel", dismissSendTestPanel);

  $(document).on("click", "#sendTestSubmit", function () {
    var submit = $(this);
    var panel = submit.closest(".js-send-test-panel");
    var button = $("#sendTestViaNotify");
    var url = button.data("send-test-url");
    var templateId = button.data("template-id");
    var recipient = (panel.find("#sendTestRecipient").val() || "").trim();

    if (recipient === "") {
      window.alert("Enter an email address.");
      return;
    }

    var originalLabel = submit.html();
    submit.prop("disabled", true).html("Sending…");
    panel.find("#sendTestRecipient, #sendTestCancel").prop("disabled", true);

    $.post(url, {
      id: templateId,
      recipient: recipient,
      security: $("#security").val(),
    })
      .done(function (data) {
        window.alert((data && data.message) || "Test email sent.");
        dismissSendTestPanel();
      })
      .fail(function (xhr) {
        var msg =
          xhr.responseJSON && xhr.responseJSON.error
            ? xhr.responseJSON.error
            : "Send test failed (HTTP " + xhr.status + ").";
        window.alert(msg);
        submit.prop("disabled", false).html(originalLabel);
        panel
          .find("#sendTestRecipient, #sendTestCancel")
          .prop("disabled", false);
      });
  });

  // Enter key inside the recipient input submits.
  $(document).on("keydown", "#sendTestRecipient", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      $("#sendTestSubmit").trigger("click");
    }
  });
});
