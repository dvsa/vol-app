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
    if (isEditing) {
      if (!confirm("Are you sure you want to edit details of existing stock?")) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    }
  });
});
