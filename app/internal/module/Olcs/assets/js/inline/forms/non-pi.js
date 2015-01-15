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
        "venueOther": function() {
          return OLCS.formHelper("fields", "venue").val() === otherVenue;
        }
      }
    }
  });
});
