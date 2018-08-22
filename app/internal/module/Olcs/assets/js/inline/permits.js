OLCS.ready(function() {
  "use strict";

  var form = "[name=permits-home]";

  OLCS.formHandler({
    form: form,
    hideSubmit: true,
    container: ".js-body",
    filter: ".js-body"
  });

  if(!$(".js-rows").length) $(".filters").hide();

  // Add event handler for Permits Form Back button click. Prevent default on Cancel, allow to continue on OK.
  $("#permit-cancel").click(function(e){
    if(!confirm("Going back will loose any unsaved changes. Are you sure? ")){
        e.preventDefault();
    }
  })

});