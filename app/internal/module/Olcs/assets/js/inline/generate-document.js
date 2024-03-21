OLCS.ready(function () {
    "use strict";

  // jshint newcap:false

    var form = "form[name=generate-document]";
    var F = OLCS.formHelper;

    if (F("bookmarks").find(":input").length === 0) {
        F("bookmarks").hide();
    }

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #documentSubCategory",
        url: "/list/document-sub-categories-with-docs",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: form + " #documentSubCategory",
        dest: form + " #documentTemplate",
        url: "/list/document-templates",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: form + " #documentTemplate",
        dest: "fieldset[data-group=details]",
        filter: "fieldset[data-group=bookmarks]",
        url: "/list-template-bookmarks",
        emptyLabel: "Please select",
        clearWhenEmpty: true,
        append: true,
        disableDestination: false,
        disableSubmit: "form-actions[submit]"
    });

});
