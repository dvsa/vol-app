$(function() {
  var form = "form[name=generate-document]";
  var F = OLCS.formHelper;

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #documentSubCategory",
    url: "/list/document-sub-categories",
    emptyLabel: "Please select"
  });

  OLCS.cascadeInput({
    source: form + " #documentSubCategory",
    dest: form + " #documentTemplate",
    url: "/list/document-templates",
    emptyLabel: "Please select"
  });

  /**
   * @TODO move this into a component if we can standardise it a bit
   */
  $(document).on("change", form + " #documentTemplate", function(e) {
    e.preventDefault();
    var value = $(this).val();

    $.get("/list-template-bookmarks/" + value, function(response) {
      var content = $(response).find("fieldset[data-group=bookmarks]");
      F("bookmarks").replaceWith(content);
    });
  });
});
