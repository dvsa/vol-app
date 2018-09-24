OLCS.ready(function() {
  "use strict";

  var isEditing = false;

  // Used to prevent the popup from showing if the user adds
  // a new stock as the buttons used are the same.
  $('#edit').click(function (e) {
    isEditing = true;
  });

  // Add an on click handlder to the save button to display a popup
  // if the user is editing the form.
  $(document).on("click", '#save', function (e) {
    showEditAlert(e);
  });

  // Bind a keypress handler forw when the user presses the 'ENTER'
  // key instead of clicking the submit button.
  $(document).keypress(function (event) {
    if (event.which === 13) {
      showEditAlert(event);
    }
  });

  // Show an alert box to the user when they try to edit an IRHP Permit
  // Stock.
  function showEditAlert(e) {
    if (isEditing) {
      if (!confirm("Are you sure you want to edit details of existing stock?")) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }
  }
});
