var OLCS = OLCS || {};

/**
 * Table check all
 *
 * Add listeners for the 'check all' checkbox present on
 * various CRUD tables
 */

OLCS.tableCheckAll = (function(document, $, undefined) {

  'use strict';

  return function init(options) {

    var defaultOptions = {
      selector: 'input[name="checkall"]'
    };

    options = $.extend(defaultOptions, options);

    $(document).on('click', options.selector, function() {

      var table = $(this).closest('table');
      var tableRow = table.find('tr');

      $(table).find('input[type="checkbox"]')
        .not(options.selector).not(':disabled')
        .prop('checked', $(this).is(':checked'));      
      
      if ($(this).prop('checked') === true) {
        tableRow.addClass('checked');
      } else {
        tableRow.removeClass('checked');
      }
        
    });

  };

}(document, window.jQuery));
