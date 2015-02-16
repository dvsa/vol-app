$(function() {

  var otherVenue = "other";

  function checked(selector) {
    return function() {
      return OLCS
        .formHelper("fields", selector)
        .filter(":checked")
        .val() === "Y";
    };
  }

  OLCS.cascadeForm({
    form: "form[method=post]",
    rulesets: {
      "fields": {
        "*": true,
        "piVenueOther": function() {
          return OLCS.formHelper("fields", "piVenue").val() === otherVenue;
        },
        "date:cancelledDate": checked("isCancelled"),
        "cancelledReason": checked("isCancelled"),
        "date:adjournedDate": checked("isAdjourned"),
        "adjournedReason": checked("isAdjourned")
      }
    }
  });
});
