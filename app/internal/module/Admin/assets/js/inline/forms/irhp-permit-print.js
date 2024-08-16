OLCS.ready(function () {
    "use strict";

    const BILATERAL_ID = 4;
    const MOROCCO_ID = 'MA';

    var form = "form[name=IrhpPermitPrint]";
    var F = OLCS.formHelper;

    function getCountryValue()
    {
        return F.findInput("fields", "country").val();
    }

    function isBilateral()
    {
        return (parseInt(F.findInput("fields", "irhpPermitType").val(), 10) === BILATERAL_ID);
    }

    function isTypeSelected()
    {
        return parseInt(F.findInput("fields", "irhpPermitType").val(), 10);
    }

    function isCountrySelected()
    {
        return getCountryValue().length === 2;
    }

    function isMoroccoSelected()
    {
        return getCountryValue() === MOROCCO_ID;
    }

    function isStockSelected()
    {
        return parseInt(F.findInput("fields", "irhpPermitStock").val(), 10);
    }

    function isRangeTypeSelected()
    {
        var rangeType = F.findInput("fields", "irhpPermitRangeType").val();
        return rangeType.length && rangeType != 'Loading...';
    }

    function isSubmitVisible()
    {
        return isStockSelected() && (!isBilateral() || isBilateral() && (isRangeTypeSelected() || isMoroccoSelected()));
    }

    $(document).on("change", "#irhpPermitType", function () {
        var field = isBilateral() ? 'irhpPermitTypeForCountry' : 'irhpPermitTypeForStock';

        F.findInput("fields", field).val(F.findInput("fields", "irhpPermitType").val());
        F.findInput("fields", field).trigger("change");
    });

    $(document).on("change", "#irhpPermitStock", function () {
        if (!isBilateral()) {
            return;
        }
        var field = 'irhpPermitStockForRangeType';

        F.findInput("fields", field).val(F.findInput("fields", "irhpPermitStock").val());
        F.findInput("fields", field).trigger("change");
    });

    OLCS.cascadeInput({
        source: "#irhpPermitTypeForCountry",
        dest: "#country",
        url: "/list/irhp-permit-print-country",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: "#country",
        dest: "#irhpPermitStock",
        url: "/list/irhp-permit-print-stock-by-country",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: "#irhpPermitTypeForStock",
        dest: "#irhpPermitStock",
        url: "/list/irhp-permit-print-stock-by-type",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: "#irhpPermitStockForRangeType",
        dest: "#irhpPermitRangeType",
        url: "/list/irhp-permit-print-range-type-by-stock",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeForm({
        form: form,
        rulesets: {
            "fields": {
                "*": true,
                "country": function () {
                    return isBilateral();
                },
                "irhpPermitStock": function () {
                    return isTypeSelected() && (!isBilateral() || isBilateral() && isCountrySelected());
                },
                "irhpPermitRangeType": function () {
                    return isTypeSelected() && isBilateral() && isStockSelected() && !isMoroccoSelected();
                },
            },
            "form-actions": function () {
                return isSubmitVisible();
            }
        }
    });
});
