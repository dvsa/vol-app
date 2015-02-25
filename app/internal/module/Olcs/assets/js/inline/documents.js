OLCS.ready(function() {
  var form = "[name=documents-home]";

  OLCS.formHandler({
    form: form,
    hideSubmit: true,
    container: ".table__form",
    filter: ".table__form"
  });

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #documentSubCategory",
    url: "/list/document-sub-categories"
  });
});
