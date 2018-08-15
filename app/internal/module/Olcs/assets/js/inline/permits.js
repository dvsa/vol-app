OLCS.ready(function() {
  "use strict";

  var form = "[name=permits-home]";

  OLCS.formHandler({
    form: form,
    hideSubmit: true,
    container: ".js-body",
    filter: ".js-body"
  });


});