var OLCS = OLCS || {};

/**
 * Select box
 *
 * Makes radios and checkboxes inputs more selectable by providing
 * a bigger hit area
 */

OLCS.selectBox = (function(document, $, undefined) {

  'use strict';

  return function init() {

    var activeClass = 'selected';
    var disabledClass = 'disabled';

    var checkboxSelector = 'input[type="checkbox"]';
    var radioSelector    = 'input[type="radio"]';

    function setup() {
      $(checkboxSelector + ':checked, ' + radioSelector + ':checked')
      .parent('label')
      .addClass(activeClass);

      $(checkboxSelector + ':disabled, ' + radioSelector + ':disabled')
      .parent('label')
      .addClass(disabledClass);
    }

    function select(selector) {
      $(selector).parent('label').addClass(activeClass);
    }

    function deselect(selector) {
      $(selector).parent('label').removeClass(activeClass);
    }

    $(document).on('change', radioSelector, function() {

      var groupSelector = radioSelector + '[name="' + $(this).attr('name') + '"]';

      deselect(groupSelector);
      select(groupSelector + ':checked');
    });

    $(document).on('change', checkboxSelector, function() {
      if ($(this).is(':checked')) {
        select(this);
      } else {
        deselect(this);
      }
    });

    setup();

    OLCS.eventEmitter.on('render', setup);
  };

}(document, window.jQuery));
