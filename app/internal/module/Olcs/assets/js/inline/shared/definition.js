OLCS.ready(function () {
    "use strict";

    var select   = ".js-definition-source";
    var textarea = ".js-definition-target";

    function updateText(index)
    {
        var str = $(select)
        .find("option[value=" + index + "]")
        .text();
        var txtArea = $(textarea).val();

        if (txtArea != '') {
            $(textarea).val(
                $(textarea).val() + "\n"
            );
        }
        $(textarea).val(
            $(textarea).val() + str
        );
    }

    $(document).on("change", select, function () {
        var selectedValue = $(this).val();

        if (selectedValue != '') {
            updateText(selectedValue);
            $(this).val('').trigger('chosen:updated'); //jquery chosen plugin - refreshes the displayed value
        }
    });
});
