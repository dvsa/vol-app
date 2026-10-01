var OLCS = OLCS || {};

OLCS.translationKeyModalData = {
  value: function (row, format) {
    if (format === "editorjs") {
      return typeof row.contentJson === "string"
        ? row.contentJson
        : JSON.stringify(row.contentJson);
    }
    return row.translatedText || "";
  },
  canPromote: function (rows, preview) {
    return rows.every(function (row) {
      var value = preview && preview[row.language.isoCode];
      if (typeof value === "string") {
        try {
          value = JSON.parse(value);
        } catch (e) {
          return false;
        }
      }
      return value && Array.isArray(value.blocks);
    });
  },
};

$(function () {
  "use strict";

  var fieldset = $('*[data-group="fields"]');
  var addedit = $("#addedit").val();
  var jsonBaseUrl = $("#jsonUrl").val();
  var resultsKey = $("#resultsKey").val();
  var longTextMode = $("#longTextMode").val() === "1";
  var rows = [];
  var format = $("#format").val() || "text";

  function showError(message) {
    $("#translationEditorError").text(message).removeClass("js-hidden");
  }

  function field(language) {
    return document.getElementById("input-" + language);
  }

  function renderLanguage(language, value, enabled) {
    var area = $("#language-" + language).empty();
    var name = "fields[translationsArray][" + language + "]";
    if (format === "editorjs") {
      var container = $("<div>", {
        class: "editorjs-container",
        "data-element-name": name,
        "data-tools-profile": "govuk-long-text",
        "data-placeholder": "Enter the wording for this page...",
      });
      container.append(
        $("<div>", { id: "editor-" + language, class: "editorjs-editor" }),
      );
      container.append(
        $("<input>", {
          type: "hidden",
          id: "input-" + language,
          name: name,
          value: value || JSON.stringify({ blocks: [] }),
          disabled: !enabled,
          "data-enable-on-editor-change": "",
        }),
      );
      area.append(container);
      container.on("input change", function () {
        $(field(language)).prop("disabled", false);
      });
      OLCS.eventEmitter.emit("render");
    } else {
      area.append(
        $("<textarea>", {
          id: "input-" + language,
          name: name,
          class: "extra-long",
        }).val(value || ""),
      );
    }
  }

  function setFormat(nextFormat, values, enabledLanguages) {
    format = nextFormat;
    $("#format").val(nextFormat);
    $(".langFields").each(function () {
      var language = $(this).data("language");
      renderLanguage(
        language,
        values[language] || "",
        enabledLanguages.indexOf(language) !== -1,
      );
    });
  }

  $(".modal__wrapper")
    .off("click.translationKeyModal", ".transKeyTab")
    .on("click.translationKeyModal", ".transKeyTab", function (event) {
      event.preventDefault();
      $(".transKeyTab").removeClass("current");
      $(this).addClass("current");
      $(".langFields").addClass("js-hidden");
      $("#language-" + $(this).data("lang")).removeClass("js-hidden");
    });

  function showForm() {
    $("#mainForm").removeClass("js-hidden");
    $("#loading").addClass("js-hidden");
    $(".translationDescriptionContainer").removeClass("js-hidden");
    if (addedit === "add") {
      $(".newTranslationKeyContainer").removeClass("js-hidden");
    }
    if (longTextMode) {
      $(".translationDescriptionContainer label").text(
        "Page name / description",
      );
      $(".newTranslationKeyContainer label").text("UID / Content key");
    }
  }

  function getTranslatedText() {
    $.get(jsonBaseUrl + "gettext/" + $("#id").val(), function (response) {
      rows = response[resultsKey] || [];
      format = response.format || (rows[0] && rows[0].format) || "text";
      $("#description").val(response.description || "");
      var values = {};
      rows.forEach(function (row) {
        var language = row.language.isoCode;
        if (format === "editorjs" && !row.contentJson) {
          showError(
            "Editor source is missing for " +
              language +
              ". Save is unavailable until it is repaired.",
          );
          $("#mainForm :submit").prop("disabled", true);
          return;
        }
        values[language] = OLCS.translationKeyModalData.value(row, format);
      });
      setFormat(format, values, Object.keys(values));
      if (longTextMode && format === "text") {
        $("#promoteTranslation").removeClass("js-hidden");
      }
      showForm();
    }).fail(function () {
      showError("The translation could not be loaded.");
    });
  }

  $("#promoteTranslation").on("click", function (event) {
    event.preventDefault();
    $.get(
      jsonBaseUrl + "gettext/" + $("#id").val(),
      { previewEditorJs: true },
      function (response) {
        var preview = response.editorJsPreview;
        if (!OLCS.translationKeyModalData.canPromote(rows, preview)) {
          showError(
            "Every existing language needs a valid EditorJS preview before conversion.",
          );
          return;
        }
        var values = {};
        rows.forEach(function (row) {
          var language = row.language.isoCode;
          values[language] =
            typeof preview[language] === "string"
              ? preview[language]
              : JSON.stringify(preview[language]);
        });
        setFormat("editorjs", values, Object.keys(values));
        $("#promoteTranslation").addClass("js-hidden");
      },
    ).fail(function () {
      showError(
        "This content cannot be converted safely. It has not been changed.",
      );
    });
  });

  function setupExistingKeyPicker() {
    if (!longTextMode || addedit !== "add") {
      return;
    }
    $("#existingMarkupPicker").removeClass("js-hidden");
    $("#existingMarkupSearch").on("input", function () {
      var term = $(this).val();
      var results = $("#existingMarkupResults").empty();
      if (term.length < 3) {
        return;
      }
      $.get(
        jsonBaseUrl + "xhrsearch",
        { translationSearch: term, includeText: 1 },
        function (response) {
          (response.results || []).forEach(function (item) {
            results.append(
              $("<li>").append(
                $("<a>")
                  .addClass("govuk-link js-modal-ajax")
                  .attr("href", jsonBaseUrl + "editkey/" + item.id)
                  .text(item.translationKey + " — " + (item.description || "")),
              ),
            );
          });
        },
      );
    });
  }

  $.get(jsonBaseUrl + "languages", function (result) {
    Object.keys(result.languages).forEach(function (language) {
      var tab = $("<li>", { class: "horizontal-navigation__item transKeyTab" })
        .attr("data-lang", language)
        .append(
          $("<a>", { class: "govuk-link", href: "#" }).text(
            result.languages[language].label,
          ),
        );
      $("#languageTabs").append(tab);
      fieldset.prepend(
        $("<div>", {
          id: "language-" + language,
          class: "langFields field js-hidden",
        }).attr("data-language", language),
      );
    });
    $("#languageTabs .transKeyTab").first().addClass("current");
    $(
      "#language-" + $("#languageTabs .transKeyTab").first().data("lang"),
    ).removeClass("js-hidden");
    fieldset.removeClass("hidden");
    if (addedit === "edit") {
      getTranslatedText();
    } else {
      setFormat(format, {}, []);
      setupExistingKeyPicker();
      showForm();
    }
  }).fail(function () {
    showError("Languages could not be loaded.");
  });
});
