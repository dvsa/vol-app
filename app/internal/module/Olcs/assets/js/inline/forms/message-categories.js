OLCS.ready(function () {
    "use strict";

    var form = "form[name=NewMessage]";

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #subCategory",
        url: "/list/task-sub-categories",
        emptyLabel: "Please select"
    });
});
