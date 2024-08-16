OLCS.ready(function () {
    "use strict";

    var form = "form[name=task]";

    OLCS.cascadeInput({
        source: form + " #assignedToTeam",
        dest: form + " #assignedToUser",
        url: "/list/users-internal-exclude-limited-read-only"
    });

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #subCategory",
        url: "/list/task-sub-categories",
        emptyLabel: "Please select"
    });
});
