$(function () {
    "use strict";

    function defendantType()
    {
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
                "date:birthDate": defendantType
            }
        }
    });

    var categoryText = $('#categoryText');

    var categoryDropdownVal = $("#category").val();

    if (categoryDropdownVal != '') {
        categoryText.prop('readonly', 'true');
    }

  //this JS gets refired each time the modal is viewed, so we can't delegate to the document.
    $("#category").on("change", function () {
        if ($(this).val() !== '') {
            categoryText.prop('readonly', 'true');
            categoryText.val($(this).find('*:selected').html());
        } else {
            categoryText.removeProp('readonly');
            categoryText.val('');
        }
    });
});
