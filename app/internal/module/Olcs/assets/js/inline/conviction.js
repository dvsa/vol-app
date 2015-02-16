OLCS.ready(function() {

  function defendantType() {
    var value = OLCS.formHelper("fields", "defendantType").val();
    return (value !== "def_t_op" && value !== "");
  }

  OLCS.cascadeForm({
    form: "#Conviction",
    rulesets: {
      "fields": {
        "*": true,
        "personFirstname": defendantType,
        "personLastname": defendantType,
        "birthDate": defendantType,
      }
    }
  });

  var categoryText = $('#categoryText');

  $(document).on("change", "#category", function() {
    if ($(this).val() !== '') {
      categoryText.prop('readonly', 'true');
      categoryText.val($(this).find('*:selected').html());
    } else {
      categoryText.removeProp('readonly');
      categoryText.val('');
    }
  });
});
