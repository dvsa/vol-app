$(function() {
  var form = "[name=bus-reg-list]";

  OLCS.formHandler({
    form: form,
    hideSubmit: true,
    container: ".table__form",
    filter: ".table__form"
  });
});
