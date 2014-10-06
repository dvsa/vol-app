OLCS.ready(function() {
  OLCS.cascadeForm({
    form: "#appeal",
    rulesets: {
      "fields": {
        "*": true,
        "label:dob": function() {
          return OLCS.formHelper.isChecked("fields", "isWithdrawn");
        }
      }
    }
  });
});
