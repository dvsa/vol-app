OLCS.ready(function () {
  "use strict";

  /* global AbortController */

  var root = document.getElementById("letter-type-builder");
  if (!root) {
    return;
  }

  var letterTypeId = parseInt(root.getAttribute("data-letter-type-id"), 10);
  if (!letterTypeId) {
    return;
  }

  // The composition lives here, not in the DOM. Reordering is then a list operation rather than
  // node shuffling, and the array order IS the display order -- the same rule LetterType\Update
  // applies when it derives display_order from array position on save.
  var composition = {
    sections: [],
    appendices: [],
  };

  // What is stored, so Save writes back the screen rather than an empty list, and so a change can
  // be told from no change at all.
  var savedComposition = { sections: [], appendices: [] };

  try {
    savedComposition =
      JSON.parse(root.getAttribute("data-saved-composition")) ||
      savedComposition;
  } catch (e) {
    savedComposition = { sections: [], appendices: [] };
  }

  composition.sections = savedComposition.sections.slice();
  composition.appendices = savedComposition.appendices.slice();

  var previewTimer = null;
  var inFlight = null;

  // The record the preview renders against. Caseworkers think in licence numbers, so the
  // picker resolves whatever was typed (licNo or id) server-side and this holds the answer;
  // the raw input is never sent to the preview.
  var resolvedRecord = { licenceId: null, licNo: null };

  // Read from select.options rather than querySelector. An unquoted attribute value has to be a
  // CSS identifier, and identifiers cannot begin with a digit -- so `option[value=20]` throws a
  // SyntaxError, which took out the whole change handler and made the picker do nothing at all.
  function labelFor(select, id) {
    var wanted = String(id);

    for (var i = 0; i < select.options.length; i++) {
      if (select.options[i].value === wanted) {
        return select.options[i].textContent.trim();
      }
    }

    return wanted;
  }

  function renderList(listId, items) {
    var list = document.getElementById(listId);
    list.innerHTML = "";

    items.forEach(function (item, index) {
      var li = document.createElement("li");
      li.className = "letter-builder__item";

      var name = document.createElement("span");
      name.className = "letter-builder__item-name";
      name.textContent = item.name;
      li.appendChild(name);

      var btnMoveup = button("↑", "Move up", index === 0, function () {
        swap(items, index, index - 1);
      });
      li.appendChild(btnMoveup);
      var btnMovedown = button(
        "↓",
        "Move down",
        index === items.length - 1,
        function () {
          swap(items, index, index + 1);
        },
      );
      li.appendChild(btnMovedown);
      var btnRemove = button("✕", "Remove", false, function () {
        items.splice(index, 1);
        redraw();
      });
      li.appendChild(btnRemove);

      list.appendChild(li);
    });
  }

  function button(glyph, title, disabled, onClick) {
    var el = document.createElement("button");
    el.type = "button";
    el.textContent = glyph;
    el.title = title;
    el.setAttribute("aria-label", title);
    el.disabled = disabled;
    el.addEventListener("click", onClick);
    return el;
  }

  function swap(items, from, to) {
    var moved = items.splice(from, 1)[0];
    items.splice(to, 0, moved);
    redraw();
  }

  function redraw() {
    renderList("composition-sections", composition.sections);
    renderList("composition-appendices", composition.appendices);
    renderContextSummary();
    renderDirtyState();
    schedulePreview();
  }

  function idsOf(items) {
    return items
      .map(function (item) {
        return item.id;
      })
      .join(",");
  }

  function isDirty() {
    return (
      idsOf(composition.sections) !== idsOf(savedComposition.sections) ||
      idsOf(composition.appendices) !== idsOf(savedComposition.appendices)
    );
  }

  function renderDirtyState() {
    var dirty = isDirty();
    document.getElementById("composition-dirty").hidden = !dirty;
    document.getElementById("save-composition").disabled = !dirty;
    document.getElementById("revert-composition").disabled = !dirty;
    if (dirty) {
      document.getElementById("save-status").textContent = "";
    }
  }

  function saveComposition() {
    var status = document.getElementById("save-status");
    var button = document.getElementById("save-composition");

    button.disabled = true;
    status.textContent = "Saving…";

    window
      .fetch("/admin/letter-type-builder/save/", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: letterTypeId,
          sections: composition.sections.map(function (s) {
            return s.id;
          }),
          // Echoed from what was loaded: omitting it clears every is_required flag, which quietly
          // turns blocking diagnostics into advisory ones.
          sectionsRequired: composition.sections
            .filter(function (s) {
              return s.isRequired;
            })
            .map(function (s) {
              return s.id;
            }),
          appendices: composition.appendices.map(function (a) {
            return a.id;
          }),
        }),
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.status !== 200) {
          status.textContent = data.message || "Could not save";
          button.disabled = false;
          return;
        }
        savedComposition = {
          sections: composition.sections.slice(),
          appendices: composition.appendices.slice(),
        };
        renderDirtyState();
        status.textContent = "Saved";
      })
      .catch(function () {
        status.textContent = "Could not reach the server";
        button.disabled = false;
      });
  }

  // The bar collapses to what it is previewing as, because that is what an admin needs to keep an
  // eye on while reordering -- the controls themselves are only wanted while actually changing it.
  function renderContextSummary() {
    var parts = [];

    if (resolvedRecord.licenceId !== null) {
      parts.push(resolvedRecord.licNo || "Licence " + resolvedRecord.licenceId);
    }
    var appSelect = document.getElementById("ctx-application");
    if (appSelect.value !== "") {
      parts.push(appSelect.options[appSelect.selectedIndex].textContent.trim());
    }

    [
      "ctx-goods-or-psv",
      "ctx-is-variation",
      "ctx-is-ni",
      "ctx-org-type",
    ].forEach(function (id) {
      var select = document.getElementById(id);
      if (select.value !== "") {
        parts.push(select.options[select.selectedIndex].textContent.trim());
      }
    });

    document
      .querySelectorAll(".js-ctx-choice:checked")
      .forEach(function (input) {
        // input.labels rather than a built selector, for the same reason as labelFor
        var label = input.labels && input.labels[0];
        parts.push(label ? label.textContent.trim() : input.value);
      });

    var summaryText = "No record chosen — bookmarks will not resolve";
    if (parts.length) {
      summaryText = parts.join(" · ");
    }
    document.getElementById("context-summary").textContent = summaryText;
  }

  function readContext() {
    var choices = [];
    document
      .querySelectorAll(".js-ctx-choice:checked")
      .forEach(function (input) {
        choices.push(parseInt(input.value, 10));
      });

    var payload = {
      letterType: letterTypeId,
      sections: composition.sections.map(function (s) {
        return s.id;
      }),
      appendices: composition.appendices.map(function (a) {
        return a.id;
      }),
      // Sent even when empty: an absent ArrayInput fails validation rather than defaulting, and
      // on the save path an omitted list is destructive rather than merely ignored.
      sectionsRequired: [],
      issues: [],
      selectedChoiceIds: choices,
    };

    // An empty context field means "take it from the record", so it is omitted entirely rather
    // than sent as null -- the API treats a null override as "not set".
    addIfSet(payload, "licence", resolvedRecord.licenceId);
    addIfSet(payload, "application", intValue("ctx-application"));
    addIfSet(payload, "goodsOrPsv", stringValue("ctx-goods-or-psv"));
    addIfSet(payload, "organisationType", stringValue("ctx-org-type"));
    addIfSet(payload, "isVariation", boolValue("ctx-is-variation"));
    addIfSet(payload, "isNi", boolValue("ctx-is-ni"));

    return payload;
  }

  function addIfSet(payload, key, value) {
    if (value !== null) {
      payload[key] = value;
    }
  }

  function intValue(id) {
    var raw = document.getElementById(id).value.trim();
    return raw === "" ? null : parseInt(raw, 10);
  }

  function stringValue(id) {
    var raw = document.getElementById(id).value;
    return raw === "" ? null : raw;
  }

  function boolValue(id) {
    var raw = document.getElementById(id).value;
    return raw === "" ? null : raw === "1";
  }

  // Every change redraws, and a drag can fire several in quick succession, so the render is
  // debounced and any render already running is abandoned rather than raced.
  function schedulePreview() {
    window.clearTimeout(previewTimer);
    previewTimer = window.setTimeout(renderPreview, 250);
  }

  function renderPreview() {
    if (inFlight) {
      inFlight.abort();
    }

    var status = document.getElementById("preview-status");
    status.hidden = false;
    status.textContent = "Rendering…";

    inFlight = new AbortController();

    window
      .fetch("/admin/letter-type-builder/preview/", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(readContext()),
        signal: inFlight.signal,
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.status !== 200) {
          status.textContent =
            data.message || "Could not render this composition";
          return;
        }
        status.hidden = true;
        showPreview(data.html);
        showDiagnostics(data.diagnostics || []);
      })
      .catch(function (error) {
        if (error.name === "AbortError") {
          return;
        }
        status.hidden = false;
        status.textContent = "Could not reach the server";
      });
  }

  function showPreview(html) {
    // srcdoc keeps the letter in its own document, so its stylesheet cannot leak into the admin
    // page and the admin page cannot restyle the letter -- the preview has to look like the PDF.
    document.getElementById("builder-preview-frame").srcdoc = html;
  }

  function showDiagnostics(diagnostics) {
    var wrapper = document.getElementById("builder-diagnostics");
    var list = document.getElementById("diagnostics-list");
    var summary = document.getElementById("diagnostics-summary");

    list.innerHTML = "";

    if (!diagnostics.length) {
      wrapper.hidden = true;
      return;
    }

    var blocking = diagnostics.filter(function (d) {
      return d.severity === "blocking";
    }).length;

    wrapper.hidden = false;
    var blockingClass = "letter-builder__diagnostics--blocking";
    wrapper.classList.toggle(blockingClass, blocking > 0);

    summary.textContent =
      diagnostics.length +
      (diagnostics.length === 1 ? " note" : " notes") +
      (blocking ? " (" + blocking + " blocking)" : "");

    diagnostics.forEach(function (diagnostic) {
      var li = document.createElement("li");
      li.textContent = diagnostic.message;
      list.appendChild(li);
    });
  }

  document
    .getElementById("diagnostics-toggle")
    .addEventListener("click", function () {
      var list = document.getElementById("diagnostics-list");
      list.hidden = !list.hidden;
      this.setAttribute("aria-expanded", list.hidden ? "false" : "true");
    });

  function wireAdder(selectId, items) {
    var select = document.getElementById(selectId);
    select.addEventListener("change", function () {
      var id = parseInt(select.value, 10);
      if (!id) {
        return;
      }
      var already = items.some(function (item) {
        return item.id === id;
      });
      // A section may appear at most once per letter type -- the join has a composite key, so a
      // duplicate would fail the save after the existing rows had already been cleared.
      if (!already) {
        items.push({ id: id, name: labelFor(select, id) });
        redraw();
      }
      select.value = "";
    });
  }

  wireAdder("add-section", composition.sections);
  wireAdder("add-appendix", composition.appendices);

  // Creating a section from here has to leave the builder standing. The stock `.js-modal-ajax`
  // path cannot: its success handler follows the add form's 302 with OLCS.url.load(), which
  // navigates to the section list and takes the unsaved composition with it. So this opens the
  // same modal against the same URL, but supplies its own success handler -- one that treats the
  // redirect as "created", closes the modal, and refreshes the picker in place.
  //
  // A distinct class rather than `js-modal-ajax` so the global binding never fires on it. The
  // alternative, stopImmediatePropagation, depends on this file's listener being bound first.
  document
    .getElementById("new-section")
    .addEventListener("click", function (event) {
      event.preventDefault();
      openNewSectionModal(this.getAttribute("href"));
    });

  function openNewSectionModal(url) {
    if (!window.OLCS || !OLCS.ajax || !OLCS.modalForm) {
      window.location.href = url;
      return;
    }

    OLCS.ajax({
      url: url,
      preloaderType: "modal",
      success: OLCS.normaliseResponse(function (response) {
        // modalForm reads body/title off this object and passes `success` straight to the form
        // handler it binds, which is the only way to displace the default redirect-following one.
        response.success = onNewSectionSubmitted;
        OLCS.modalForm(response);
      }),
    });
  }

  function onNewSectionSubmitted(raw) {
    OLCS.normaliseResponse({
      // The add command answers a successful save with a redirect to the section list. Following
      // it is exactly what must not happen here.
      followRedirects: false,
      callback: function (response) {
        if (response.status === 302) {
          OLCS.modal.hide();
          refreshSections();
          return;
        }

        // Anything else is the form coming back with its errors on it. Put it back in the modal
        // so the admin can correct it, rather than closing over the top of the message.
        OLCS.modal.updateBody(response.body);
        OLCS.formHelper.renderModalTitle(response.title);
      },
    })(raw);
  }

  function refreshSections() {
    window
      .fetch("/admin/letter-type-builder/sections/", {
        credentials: "same-origin",
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.status !== 200) {
          return;
        }
        var select = document.getElementById("add-section");
        var placeholder = select.options[0];

        select.innerHTML = "";
        select.appendChild(placeholder);

        data.sections.forEach(function (section) {
          var option = document.createElement("option");
          option.value = section.id;
          option.textContent = section.name;
          select.appendChild(option);
        });
      })
      .catch(function () {
        // Leaving the old list in place is the safe failure: the admin can still reload by hand.
      });
  }

  // --- record picker -------------------------------------------------------------------
  //
  // Typing pauses for 400ms, then the term goes to the server, which decides whether it is a
  // licence number or an id. On a hit the application dropdown fills with that licence's real
  // applications, so an application that does not belong to the licence cannot be chosen.

  var recordTimer = null;

  document.getElementById("ctx-record").addEventListener("input", function () {
    var term = this.value.trim();
    window.clearTimeout(recordTimer);

    if (term === "") {
      setResolvedRecord(null, null);
      return;
    }

    recordTimer = window.setTimeout(function () {
      lookupRecord(term);
    }, 400);
  });

  function lookupRecord(term) {
    window
      .fetch(
        "/admin/letter-type-builder/record/?term=" + encodeURIComponent(term),
        { credentials: "same-origin" },
      )
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        // A stale response for an earlier term must not clobber the current one
        if (term !== document.getElementById("ctx-record").value.trim()) {
          return;
        }
        if (!data.found) {
          setResolvedRecord(null, null);
          showRecordResult("No licence found for \u201C" + term + "\u201D");
          return;
        }

        setResolvedRecord(
          data.licence.id,
          data.licence.licNo,
          data.applications,
        );
        showRecordResult(
          data.licence.licNo +
            " \u2014 " +
            (data.licence.organisationName || "Unknown operator") +
            " (" +
            (data.licence.goodsOrPsv || "?") +
            ", " +
            (data.licence.isNi ? "NI" : "GB") +
            ")",
        );
      })
      .catch(function () {
        // Network failure: keep whatever was resolved before rather than wiping it
      });
  }

  function showRecordResult(text) {
    var el = document.getElementById("record-result");
    el.textContent = text;
    el.hidden = false;
  }

  function setResolvedRecord(licenceId, licNo, applications) {
    resolvedRecord.licenceId = licenceId;
    resolvedRecord.licNo = licNo;

    var select = document.getElementById("ctx-application");
    select.innerHTML = "";

    var licenceOnly = document.createElement("option");
    licenceOnly.value = "";
    licenceOnly.textContent = "Licence only";
    select.appendChild(licenceOnly);

    (applications || []).forEach(function (app) {
      var option = document.createElement("option");
      option.value = app.id;
      option.textContent =
        "App " +
        app.id +
        " \u2014 " +
        (app.status || "unknown status") +
        (app.isVariation ? " (variation)" : "");
      select.appendChild(option);
    });

    select.disabled = licenceId === null;

    if (licenceId === null) {
      document.getElementById("record-result").hidden = true;
    }

    renderContextSummary();
    schedulePreview();
  }

  document
    .getElementById("save-composition")
    .addEventListener("click", saveComposition);

  document
    .getElementById("revert-composition")
    .addEventListener("click", function () {
      composition.sections = savedComposition.sections.slice();
      composition.appendices = savedComposition.appendices.slice();
      redraw();
    });

  document
    .getElementById("builder-context")
    .addEventListener("change", function () {
      renderContextSummary();
      schedulePreview();
    });

  document
    .getElementById("context-toggle")
    .addEventListener("click", function () {
      var controls = document.getElementById("context-controls");
      controls.hidden = !controls.hidden;
      this.setAttribute("aria-expanded", controls.hidden ? "false" : "true");
      this.textContent = controls.hidden ? "Edit preview context" : "Done";
    });

  redraw();
});
