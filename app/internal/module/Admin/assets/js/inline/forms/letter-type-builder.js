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

  var previewTimer = null;
  var inFlight = null;

  function labelFor(select, id) {
    var option = select.querySelector('option[value="' + id + '"]');
    return option ? option.textContent.trim() : String(id);
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

      li.appendChild(
        button("↑", "Move up", index === 0, function () {
          swap(items, index, index - 1);
        }),
      );
      li.appendChild(
        button("↓", "Move down", index === items.length - 1, function () {
          swap(items, index, index + 1);
        }),
      );
      li.appendChild(
        button("✕", "Remove", false, function () {
          items.splice(index, 1);
          redraw();
        }),
      );

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
    schedulePreview();
  }

  function readContext() {
    var choices = [];
    document
      .querySelectorAll('input[name="ctxChoices[]"]:checked')
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
      withChrome: document.getElementById("ctx-with-chrome").checked,
    };

    // An empty context field means "take it from the record", so it is omitted entirely rather
    // than sent as null -- the API treats a null override as "not set".
    addIfSet(payload, "licence", intValue("ctx-licence"));
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
    wrapper.classList.toggle(
      "letter-builder__diagnostics--blocking",
      blocking > 0,
    );

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

  document
    .getElementById("builder-context")
    .addEventListener("change", schedulePreview);

  redraw();
});
