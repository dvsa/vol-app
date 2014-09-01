$(function() {
  var form = "form[name=generate-document]";

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
});
