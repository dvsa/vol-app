OLCS.ready(function () {
    "use strict";

    var total;
    var hasInitialRender = false;

  // Calculate the total of the permits when the page is rendered.
    calculateTotal();

  // Bind an event handler to when the contents of a Number input changes (jQuery wrapper for 'keyup').
    $(':input[type="number"]').on('input', function (e) {
      // Prevent the input from being blank.
        if (e.target.value === "") {
            e.target.value = "0";
        }

      // If the user is inputting a number, then we need to clear out the initial '0'.
        if (e.target.value.length > 1 && e.target.value[0] === '0') {
            e.target.value = e.target.value.substring(1);
        }

        calculateTotal();
    });

    function calculateTotal()
    {
      // Reset total to 0 to prevent the value from constantly increasing.
        total = 0;

      // Grab all the number inputs on the page.
        let inputs = $(':input[type="number"]');

      // Iterate over the inputs and add their values to the total.
        inputs.each(function (i) {
            total += parseInt(inputs[i].value, 10);
        });

        updateTable();
    }

  /*
   * Handle updating the table.
   * Only create the DOM element if this is the first table render.
   */
    function updateTable()
    {
        if (!hasInitialRender) {
            drawTableRow();
            hasInitialRender = true;
        } else {
            updateTotalValue();
        }
    }

  // Update the total value in the table.
    function updateTotalValue()
    {
        var children = $('#total-permits').children();
        children[children.length - 1].textContent = total;
    }

  // Draw the total value table row.
    function drawTableRow()
    {
        $('tbody:last-child').append(`
        < tr id = "total-permits" >
        < th colspan = "${getColumnCount() - 1}" > Total < / th >
        < th > ${total} < / th >
        <  / tr >
        `);
    }

  // Gets the number of columns in the table.
    function getColumnCount()
    {
        return $('.table__wrapper').find('tr')[0].cells.length;
    }
});
