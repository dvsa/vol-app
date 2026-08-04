OLCS.ready(function() {
  "use strict";

  OLCS.formHandler({
    // the form to bind to
    form: ".form__search",
    // submit button is not hidden for search form
    hideSubmit: false,
    // where we'll render any response data to
    container: ".js-body"
  });
});
