OLCS.ready(function() {
  "use strict";

  var F = OLCS.formHelper;

  if (!F.containsErrors(F.fieldset("main"))) {
    var emailAddress = F.input("main", "emailAddress");
    var emailConfirm = F.input("main", "emailConfirm").parent();

    emailConfirm.hide();

    emailAddress.on('keypress', function() {
      emailConfirm.show();
      emailAddress.off('keypress');
    });
  }
});
