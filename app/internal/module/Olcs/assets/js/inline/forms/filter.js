OLCS.ready(function() {
  /**
   * @NOTE: This is a generic form handler which just binds
   * to forms with a known class. It should be reusable wherever
   * filters are needed
   */

  OLCS.formHandler({
    // the form to bind to
    form: ".form__filter",
    // make sure the primary submit button is hidden
    hideSubmit: true,
    // where we'll render any response data to
    container: ".table__form",
    // filter the data returned from the server to only
    // contain content within this element
    filter: ".table__form"
  });
});
