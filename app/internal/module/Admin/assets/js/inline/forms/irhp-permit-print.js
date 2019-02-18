OLCS.ready(function() {
  "use strict";

  const BILATERAL_ID = 4;

  var form = "form[name=IrhpPermitPrint]";
  var F = OLCS.formHelper;

  function isBilateral() {
    return (parseInt(F.findInput("fields", "irhpPermitType").val(), 10) === BILATERAL_ID);
  }

  function isTypeSelected() {
    return parseInt(F.findInput("fields", "irhpPermitType").val(), 10);
  }

  function isCountrySelected() {
    return F.findInput("fields", "country").val().length === 2;
  }

  function isStockSelected() {
    return parseInt(F.findInput("fields", "irhpPermitStock").val(), 10);
  }

  $(document).on("change", "#irhpPermitType", function() {
    var field = isBilateral() ? 'irhpPermitTypeForCountry' : 'irhpPermitTypeForStock';

    F.findInput("fields", field).val(F.findInput("fields", "irhpPermitType").val());
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

  OLCS.cascadeForm({
    form: form,
    rulesets: {
      "fields": {
        "*": true,
        "country": function() {
          return isBilateral();
        },
        "irhpPermitStock": function() {
          return isTypeSelected() && (!isBilateral() || isBilateral() && isCountrySelected());
        }
      },
      "form-actions": function() {
        return isStockSelected();
      }
    }
  });
});