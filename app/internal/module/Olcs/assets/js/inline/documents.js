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

  OLCS.conditionalButton({
    form: ".table__form",
    label: "Delete",
    predicate: function(length, callback) {
      callback(length < 1);
    }
  });
});
