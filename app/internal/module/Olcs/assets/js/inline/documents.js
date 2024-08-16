OLCS.ready(function () {
    "use strict";

  // explicitly whitelist file extensions which can be viewed in split screen
    var splitscreenWhitelist = ["pdf", "html"];

    var form = "[name=documents-home]";

    OLCS.formHandler({
        form: form,
        hideSubmit: true,
        container: ".js-body",
        filter: ".js-body"
    });

    OLCS.cascadeInput({
        source: form + " #category",
        dest: form + " #documentSubCategory",
        url: "/list/document-sub-categories"
    });

    OLCS.conditionalButton({
        container: ".table__form",
        label: "Split",
        predicate: {
            max: 1,
            attr: function (row) {
                var filename = $(row).data("filename");
                var extPos = filename.lastIndexOf(".");
                if (extPos === -1) {
                    return;
                }
                return filename.toLowerCase().substr(extPos + 1);
            },
            allow: splitscreenWhitelist
        }
    });
});