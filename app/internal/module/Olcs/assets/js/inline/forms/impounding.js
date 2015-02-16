$(function() {

  var hearingType = "impt_hearing";
  var otherVenue = "other";

  function showHearing() {
    return OLCS.formHelper("fields", "impoundingType").val() === hearingType;
  }

  OLCS.cascadeForm({
    form: "form[method=post]",
    rulesets: {
      "fields": {
        "*": true,
        "label:hearingDate": showHearing,
        "piVenue": showHearing,
        "piVenueOther": function() {
          return showHearing() && OLCS.formHelper("fields", "piVenue").val() === otherVenue;
        }
      }
    }
  });
});
