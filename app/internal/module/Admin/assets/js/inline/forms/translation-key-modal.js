$(function () {
    "use strict";

    // Attach event listener for tab element clicks inserted below after XHR call
    $(".modal__wrapper").on("click", ".transKeyTab", function () {
        // Set underline on clicked tab
        $(".transKeyTab").removeClass("current");
        $(this).addClass("current");
        // make relevant text-area visible
        $(".langFields").addClass("js-hidden");
        $("#input-" + $(this).attr("data-lang")).closest("div").removeClass("js-hidden");
    });

    var fieldset = $("*[data-group=\"fields\"]");
    var addedit = $("#addedit").val();
    var jsonBaseUrl = $("#jsonUrl").val();
    var resultsKey = $("#resultsKey").val();
    var translationVar = $("#translationVar").val();

    getLanguages();

    function getLanguages()
    {
        $.get(jsonBaseUrl + "languages", function (result) {
            var current = " current";
            $.each(result.languages, function (idx, lang) {
                    var hideClass = (idx === "en_GB") ? "" : " js-hidden";
                    var tabTemplate =
                        ` < li id = "tab{idx}" class = "horizontal-navigation__item transKeyTab${current}" data - lang = "${idx}" style = "padding: 0px; margin: 0px;" >
                            < a class = "govuk-link" id = "transKeyEnGB" href = "#" > ${lang.label} < / a >
                       <  / li > `;
                    var textAreaTemplate =
                        ` < div class = "langFields field ${hideClass}" >
                                < textarea name = "fields[translationsArray][${idx}]" id = "input-${idx}" class = "extra-long" > < / textarea >
                        <  / div > `;
                    $("#languageTabs").append(tabTemplate);
                    fieldset.prepend(textAreaTemplate);
                    current = "";
            });
            fieldset.removeClass("hidden");
            if (addedit == "edit") {
                getTranslatedText();
            } else {
                $("#mainForm").removeClass("js-hidden");
                $(".translationKeyContainer").removeClass("js-hidden");
                $("#loading").addClass("js-hidden");
            }
        });
    }

    function getTranslatedText()
    {
        $.get(
            jsonBaseUrl + "gettext/" + $("#id").val(),
            function (response) {
                $.each(response[resultsKey], function (ix, text) {
                    $("#input-" + text.language.isoCode).val(text[translationVar]);
                });
                $("#mainForm").removeClass("js-hidden");
                $("#loading").addClass("js-hidden");
            }
        );
    }
});
