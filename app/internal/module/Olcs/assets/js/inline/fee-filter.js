OLCS.ready(function() {
  var form = "form[name=fee-filter]";

  OLCS.formHandler({
    form: form,
    hideSubmit: true,
    container: ".table__form",
    filter: ".table__form"
  });

});
