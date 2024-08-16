OLCS.ready(function () {
    "use strict";

    function showIsMlhFieldset()
    {
        return OLCS.formHelper.isChecked("details", "goodsOrPsv", "lcat_gv");
    }

    function showAlphaSplitFieldset()
    {
        return OLCS.formHelper.isSelected("details", "user", "alpha-split");
    }

    $('#user').change(function () {
        if ($('#details\\[id\\]').val() === "") {
            // if not ID then adding, therefore remove alpha-split option
            $(this).find('option[value="alpha-split"]').remove()
        }
    });

    OLCS.cascadeForm({
        cascade: false,
        rulesets: {
            "details[taskAlphaSplit]": showAlphaSplitFieldset,
            "isMlh": showIsMlhFieldset,
        }
    });

    OLCS.cascadeInput({
        source: "#team",
        dest: "#user",
        url: "/list/task-allocation-users"
    });
});
