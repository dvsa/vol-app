var OLCS = OLCS || {};

/**
 * Table rows
 */
OLCS.tableRows = (function(document, $, undefined) {

  'use strict';

  return function init() {

    var tableRowSelector = 'tbody tr';
    var selectBox = '[type=checkbox]:not(:disabled), [type=radio]:not(:disabled)';
    
    // If a table contains rows that contain a select/check box, and add a special class
    $('table').find(selectBox).parents('table').addClass('js-rows');
    
    var lastChecked = null;
    var ctrlPressed = false;
    
    // Prevent ctrl + click from opening the context menu within our special table
    $(document).on('keydown', function(event) {
      if (event.ctrlKey) {
        $('.js-rows').unbind('contextmenu').bind('contextmenu', function(event) {
          event.preventDefault();
          // simulate a click otherwise we can't capture it
          event.target.click();
        });
        ctrlPressed = true;
      }
    }).on('keyup', function() {
      ctrlPressed = false;
    });

    // On click of a table row
    $(document).on('click touchstart', tableRowSelector, function(event) {
      
      var target          = $(event.target);
      var targetSelectBox = target.children(selectBox);
      
      // allow multiple rows to be selected by using the 'shift' key
      if ($(this).find('[type="checkbox"]').length) {

        // if the row was clicked whilst holding the 'shift' key
        if (event.shiftKey && !event.ctrlKey) {
          
          // reset the whole thing when shift is released
          $(document).on('keyup', function() {
            lastChecked = null;
          });
          
          // add a class to prevent accidental text highlighting when clicking row
          $(this).parents('table').addClass('table--no-select');
              
          // If we aren't selecting multiple ones, only toggle the target
          if(!lastChecked) {
            if ($(this).parents('tr').find('[type="checkbox"]').prop('checked', true)) {
              lastChecked = $(this);
            }
            return;
          }

          return;
        }
      
        lastChecked = $(this);
        
        // if the row was clicked whist holding the 'ctrl' key
        if (ctrlPressed && !targetSelectBox.length) {
          // cache current select state
          var ctrlState = target.parents('tr').find(selectBox).prop('checked');
          target.parents('tr').toggleClass('checked');
          target.parents('tr').find(selectBox).prop('checked', !ctrlState);
        }  
      }

      // Return if target is a link (or is inside a link), this is to prevent
      // the target from being "clicked" twice
      if (target.closest('a').length) {
          return;
      }
    });

  };

}(document, window.jQuery));
