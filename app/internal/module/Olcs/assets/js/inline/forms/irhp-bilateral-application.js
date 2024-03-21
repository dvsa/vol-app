$(function () {
    "use strict";

    const BILATERAL_ID = "4";

    var permitTypeId = $("#topFields\\[irhpPermitType\\]").val();
    var $bilateralContainer = $("#bilateralContainer");
    var modalWrapper = $(".modal__wrapper");

    if (permitTypeId === BILATERAL_ID) {
        setCountryVisibility();

        $bilateralContainer.find("select").each(function () {
            updateFieldsFromSelect(this);
        });

        $bilateralContainer.on("change", "select", function () {
            updateFieldsFromSelect(this);
        });

        $bilateralContainer.find("[data-role='period']").each(function () {
            setTextboxDisability($(this));
        });

        $bilateralContainer.on("propertychange change keyup paste input", "input[type='text']", function () {
            setTextboxDisability(
                $(this).closest("[data-role='period']")
            )
        });

        $('body').on("change", "#addOrRemoveCountriesContainer input[type='checkbox']", function () {
            var $modalWrapper = $(".modal__wrapper");
            var $applyButton = $modalWrapper.find(".primary-button");

            if ($modalWrapper.find("input:checked").length) {
                $applyButton.show();
            } else {
                $applyButton.hide();
            }
        });

        $('body').on("click", "#addOrRemoveCountriesApplyButton", function (event) {
            var $modalWrapper = $(".modal__wrapper");

            $modalWrapper.find("input[type='checkbox']").each(function () {
                var $countryFieldset = $bilateralContainer.find("fieldset[data-id='" + this.value + "']");
                if ($(this).is(":checked")) {
                    $countryFieldset.removeClass("hidden");
                } else {
                    $countryFieldset.addClass("hidden");
                }
            });

            OLCS.modal.hide();
            event.preventDefault();
        });

        $('body').on("click", "#addOrRemoveCountriesCancelButton", function (event) {
            OLCS.modal.hide();
            event.preventDefault();
        });

        $("#addOrRemoveCountriesButton").on("click", function (event) {
            var markup = "<div id=\"addOrRemoveCountriesContainer\">" +
                "<div style=\"padding-bottom: 20px;\"></div>" +
                "<fieldset class=\"govuk-button-group\">" +
                "<a id=\"addOrRemoveCountriesApplyButton\" class=\"govuk-button primary-button\" href=\"\">Apply</a>" +
                "<a id=\"addOrRemoveCountriesCancelButton\" class=\"govuk-button govuk-button--secondary\" href=\"\">Cancel</a>" +
                "</fieldset>" +
                "</div>";

            OLCS.modal.show(markup, "Add or remove countries");
            var $innerContainer = $("#addOrRemoveCountriesContainer > div");

            $bilateralContainer.find("fieldset[data-role='country']").each(function () {
                var $fieldset = $(this);

                var inputAttributes = {
                    type: "checkbox",
                    name: "countries[]",
                    value: $fieldset.data('id'),
                };

                if (!$fieldset.hasClass("hidden")) {
                    inputAttributes["checked"] = "checked";
                }

                $innerContainer.append(
                    $("<label>").text($fieldset.data('name')).prepend(
                        $("<input>", inputAttributes)
                    )
                );
            });

            event.preventDefault();
        });

        $("#irhpApplication").on("submit", function (event) {
            var selectedCountries = [];

            $bilateralContainer.find("fieldset[data-role='country']").each(function () {
                var $fieldset = $(this);

                if (!$(this).hasClass("hidden")) {
                    selectedCountries.push($fieldset.data('id'));
                }
            });

            $("#selectedCountriesCsv").val(
                selectedCountries.join(",")
            );
        });
    }

    function setCountryVisibility()
    {
        var selectedCountryIds = $("#selectedCountriesCsv").val().split(",");

        $bilateralContainer.find("fieldset[data-role='country']").each(function () {
            var $fieldset = $(this);

            if (!selectedCountryIds.includes($fieldset.data("id"))) {
                $fieldset.addClass("hidden");
            }
        });
    }

    function setTextboxDisability($periodElement)
    {
        var $standardCountryContainer = $periodElement.closest("fieldset[data-role='country'][data-type='standard']");
        if ($standardCountryContainer.length == 0) {
            return;
        }

        var $siblingTextboxes = $periodElement.find("input[type='text']");
        var enabledFieldNameSegment = "periods";

        $siblingTextboxes.each(function () {
            var $textbox = $(this);
            if (isNormalInteger($textbox.val())) {
                var textboxName = $textbox.attr("name");
                if (textboxName.indexOf("journey_multiple") != -1) {
                    enabledFieldNameSegment = "journey_multiple";
                } else {
                    enabledFieldNameSegment = "journey_single";
                }
                return false;
            }
        });

        $siblingTextboxes.each(function () {
            var $textbox = $(this);
            if ($textbox.attr("name").indexOf(enabledFieldNameSegment) != -1) {
                $textbox.removeAttr("disabled");
            } else {
                $textbox.attr("disabled", "disabled");
                $textbox.val("");
            }
        });
    }

    function updateFieldsFromSelect(select)
    {
        var $countryContainer = $(select).closest("fieldset[data-role='country']");
        $countryContainer.find("fieldset[data-role='period']").addClass("hidden");
        $countryContainer.find("fieldset[id='period" + select.value + "']").removeClass("hidden");
    }

    function isNormalInteger(str)
    {
        var n = Math.floor(Number(str));
        return n !== Infinity && String(n) === str && n > 0;
    }
});
