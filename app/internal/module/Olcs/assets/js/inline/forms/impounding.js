OLCS.ready(function() {

  var hearingType = "impt_hearing";
  var otherVenue = "other";

  OLCS.cascadeForm({
    form: "form[method=post]",
    rulesets: {
      "fields": {
        "*": true,
        "label:hearingDate": function() {
          return OLCS.formHelper("fields", "impoundingType").val() === hearingType;
        },
        "piVenueOther": function() {
          return OLCS.formHelper("fields", "piVenue").val() === otherVenue;
        }
      }
    }
  });
});
