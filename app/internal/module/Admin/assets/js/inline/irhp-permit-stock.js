OLCS.ready(function () {
    "use strict";

    var isEditing = false;

  // Used to prevent the popup from showing if the user adds a new stock as the buttons used are the same.
    $("#edit").click(function () {
        isEditing = true;
    });

  // Reset the isEditing variable if the user tries to Add a Permit Stock.
    $("#add").click(function () {
        isEditing = false;
    });

  // Add an on click handler to the save button to display a popup if the user is editing the form.
    $(document).on("click", "#save", function (e) {
        showEditAlert(e);
    });

  // Bind a keypress handler for when the user presses the "ENTER" key instead of clicking the submit button.
    $(document).keypress(function (event) {
        if (event.which === 13) {
            showEditAlert(event);
        }
    });

    $("input[type='radio']").click(function (e) {
      /*
       * Find the closest 'tr' tag (the row associated with the radio button) and then find the dataset for the
       * first child.
       */
        var data = e.target.closest('tr').children[0].firstElementChild.dataset;

      // Set the appropriate value for the dataset on the radio button if the Permit Stock is able to be deleted
        $("#delete")[0].dataset.canDeleteRow = (data.stockDelete === "1");
    });

  // Show an alert box to the user when they try to edit an IRHP Permit Stock.
    function showEditAlert(e)
    {
        if (isEditing) {
            if (
            !confirm("Are you sure you want to edit details of existing stock?")
            ) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        }
    }
});
