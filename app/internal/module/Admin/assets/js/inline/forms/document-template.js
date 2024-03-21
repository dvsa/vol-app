OLCS.ready(function () {
    "use strict";

    OLCS.cascadeForm({
        cascade: false,
    });

    OLCS.cascadeInput({
        source: "#category",
        dest: "#subCategory",
        url: "/list/sub-categories",
        rulesets: {
            "data": "*",
        }
    });
});