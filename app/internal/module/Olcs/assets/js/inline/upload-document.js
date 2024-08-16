OLCS.ready(function () {
    "use strict";

    var form = "form[name=upload-document]";

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #documentSubCategory",
        url: "/list/document-sub-categories",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });
});
