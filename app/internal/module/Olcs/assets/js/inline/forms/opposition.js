$(function() {
  function hasValue(value) {
    return function() {
      return OLCS.formHelper("fields", "oppositionType").val() === value;
    };
  }

  OLCS.cascadeForm({
    form: "form[method=post]",
      rulesets: {
        "fields": {
          "*": true,
            "label:outOfRepresentationDate": hasValue("otf_rep"),
            "label:outOfObjectionDate": hasValue("otf_eob"),
            "label:opposerType": hasValue("otf_eob")
          }
      }
  });
});
