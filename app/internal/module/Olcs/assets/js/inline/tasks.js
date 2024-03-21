OLCS.ready(function () {
    "use strict";

    var form = ".form__filter";

    OLCS.cascadeInput({
        source: form + " #assignedToTeam",
        dest: form + " #assignedToUser",
        url: "/list/users"
    });

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #taskSubCategory",
        url: "/list/task-sub-categories"
    });
});
