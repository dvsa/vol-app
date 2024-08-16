$(function () {
    var objection = "otf_obj";
    var envObjection = "otf_eob";
    var representation = "otf_rep";

    function hasValue(value)
    {
        return function () {
            return OLCS.formHelper("fields", "oppositionType").val() === value;
        };
    }

    function showOpposerType()
    {
        var val = OLCS.formHelper("fields", "oppositionType").val();
        return ((val === objection) || (val === envObjection));
    }

    OLCS.cascadeForm({
        form: "form[method=post]",
        rulesets: {
            "fields": {
                "*": true,
                "label:outOfRepresentationDate": hasValue(representation),
                "label:outOfObjectionDate": hasValue(envObjection),
                "label:opposerType": showOpposerType
            }
        }
    });
});
