OLCS.ready(function() {
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
    url: "/list/document-sub-categories",
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

  /**
   * @TODO move this into a component if we can standardise it a bit
   */
  $(document).on("change", form + " #documentTemplate", function(e) {
    e.preventDefault();
    var value = $(this).val();

    if (value === "") {
      return F("bookmarks").hide();
    }

    $.get("/list-template-bookmarks/" + value, function(response) {
      var content = $(response).find("fieldset[data-group=bookmarks]");
      F("bookmarks").replaceWith(content).show();
    });
  });
});
