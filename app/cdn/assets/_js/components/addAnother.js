var OLCS = OLCS || {};

/**
 * Add another
 *
 * Duplicates the last field in a fieldset, increments
 * the name and ID if needed and appends it to the end
 * of the fieldset.
 */
OLCS.addAnother = (function(document, $, undefined) {

  'use strict';

  return function init(custom) {

    var options = $.extend({
      container       : '.add-another',
      triggerSelector : '.hint input[type="submit"]',
      targetSelector  : 'fieldset',
    }, custom);

    OLCS.eventEmitter.on('render', function() {

      // Run the plugin on each container
      $(options.container).each(function() {

        var container = $(this);
        var triggerSelector = container.find(options.triggerSelector);

        function incrementString(string) {
          string = string || '';
          var newString = string.replace(/\[(\d+)\]/, function(match, number) {
            return '[' + (parseInt(number, 10) + 1) + ']';
          });
          return newString;
        }

        function updateValues(element) {
          var field = $(element);
          var input = field.find('input');

          field.attr({
            'data-group': incrementString(field.data('group'))
          });

          for (var i = 0; i < input.length; i++) {
            var name = incrementString(input[i].getAttribute('name'));
            var id = incrementString(input[i].id);
            input[i].setAttribute('name', name);
            input[i].id = id;
            input[i].value = '';
          }


          return field;
        }

        // Replace the trigger element to prevent it from submitting the form
        if (triggerSelector.is('input[type="submit"]')) {
          var value = triggerSelector.val();
          triggerSelector.parent().html('<a href="#" class="add-another-trigger govuk-link">' + value + '</a>');
          triggerSelector = container.find('.add-another-trigger');
        }

        triggerSelector.on('click', function(e) {
          e.preventDefault();

          var lastField = container.find(options.targetSelector).last().clone();
          var newField  = updateValues(lastField);

          // remove any error messages
          $(newField).find('ul').remove();

          $(newField).insertBefore(container.find('.hint'));
        });

      });

    });

  };

}(document, window.jQuery));
