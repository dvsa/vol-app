var OLCS = OLCS || {};

/**
 * Postcode search
 */

OLCS.postcodeSearch = (function(document, $, undefined) {

  'use strict';

  // jshint newcap:false

  return function init(options) {

    var container = options.container;

    // store a list of fields considered to make up the
    // 'address' part of a postcode component
    var fields = options.fields || [
      'addressLine1',
      'addressLine2',
      'addressLine3',
      'addressLine4',
      'town',
      'postcode',
      'countryCode'
    ];

    // store a list of possible root elements within which we'll render
    // the result of our lookup. These should be in order of likely DOM
    // proximity because the first match will win
    var roots = [
      // if we're in a modal, render in there...
      '.modal__content',
      // ... otherwise, fall back to one of the top-level body selectors
      '.js-body__main',
      '.js-body'
    ];

    var selectClass = '.address__select';

    var inputSelector  = container + ' .js-input';
    var submitSelector = container + ' button';
    var selectSelector = container + ' ' + selectClass;
    var manualSelector = container + ' .hint a';

    var F = OLCS.formHelper;

    //Does the component contain any data or errors?
    function isClean(component) {
      var group = $(component).data('group');

      for (var i = 0, j = fields.length; i < j; i++) {
        var input = F(group, fields[i]);
        if (input.attr('type') !== 'text') {
          continue;
        }
        if (input.val() !== '') {
          return false;
        }
      }

      return $(component)
      .children('.validation-wrapper')   // find any errors wrappers...
      .children('.field').length === 0;  // which *also* have direct field children
    }

    // Is the component currently in its interim state of showing the
    // user a list of matching addresses?
    function inProgress(component) {
      return $(component).find(selectClass).length > 0;
    }

    // Has the user requested to enter the address manually?
    function manualSpecified(component) {
      return $(component).hasClass('manual-address');
    }

    function hasSearchField(component) {
      return $(component).find('.js-find').length > 0;
    }

    function formatUKPostcode(element) {
      var val =  element.val().toUpperCase();

      element.val(val);

      var parts = val.match(/^([A-Z]{1,2}\d{1,2}[A-Z]?)\s*(\d[A-Z]{2})$/);

      if (val.indexOf(' ') >= 0) {
        return;
      } else if (parts) {
        parts.shift();
        element.val(parts.join(' '));
      } else {
        return;
      }

    }

    // Work out what our most appropriate root element is in which
    // we should render our AJAX response
    function getRootSelector(component) {
      var root = null;
      var distance = Infinity;

      for (var i = 0, j = roots.length; i < j; i++) {
        var cRoot = roots[i];

        // have to check existence first; parentsUntil will just return
        // the <html> tag if there's no matching selector
        if ($(component).parents(cRoot).length) {
          var dist = $(component).parentsUntil(cRoot).length;
          if (dist < distance) {
            distance = dist;
            root = cRoot;
          }
        }
      }
      if (root !== null) {
        return root;
      }
      throw new Error('No valid root selector found');
    }

    // Handle either the click of the 'find' button or the change
    // of the 'select' input; either way, we perform the same action
    function handleInput(selector) {
      return function(e) {
        e.preventDefault();

        // we have to prevent this event bubbling; not only to ancestors
        // but also to listeners with the same specifity. This is because
        // postcode search is *the* most specific 'click' or 'submit'
        // handler and doesn't want to trigger wider events (like form
        // submission)
        e.stopImmediatePropagation();

        var fieldset = $(this).parents(container);
        var button   = fieldset.find(selector);
        var form     = fieldset.parents('form');
        var input    = fieldset.find('.js-input');

        // ensure the backend knows which button was pressed
        F.pressButton(form, button);

        if (input.length) {
          formatUKPostcode(input);
        }

        OLCS.submitForm({
          form: form,
          success: OLCS.normaliseResponse(function(response) {
            var root = getRootSelector(fieldset);
            F.render(root, response.body);
            if(response.hasErrors === false) {
              var rootElem = document.querySelector(root);
              var fieldsetSelector = 'fieldset[data-group="' + fieldset[0].getAttribute('data-group') + '"]';
              var fieldSetElement = rootElem.querySelector(fieldsetSelector); 
              var selectElement = fieldSetElement.querySelector('.address__select select');
              if (null === selectElement) {
                var nextInputElement = fieldSetElement.querySelector('input:not([type="hidden"]):not([id^="postcodeInput"])'); 
                nextInputElement.focus();
              } else {
                selectElement.focus();
              }
            }
          }),
          error: OLCS.normaliseResponse(function() {
            lookupError();
          }),
        });

      };
    }

    // Simple method to iterate through all address components on the page
    // and initialise their state. We call this on initialisation and on
    // each render to keep things in sync
    function setup() {
      $(container).each(function(i, component) {
        if (!hasSearchField(component)) {
          return;
        }

        // we hide all address fields if a search is in progress or the
        // address data is currently empty and valid
        if ((inProgress(component) || isClean(component)) && !manualSpecified(component)) {
          // this selector looks a bit loose but it works fine; we use children
          // rather than find which is equivalent to foo > bar.
          $(component).children('.field').hide();
        } else {
          // otherwise we hide the 'enter address manually' button
          $(component).find('.hint').hide();
        }
      });
    }

    // if we can't connect to the postcode API we need to let the user know
    function lookupError() {
      $(selectClass).hide();
      $('.postcode-connectionLost').removeClass('govuk-visually-hidden');
    }

    // Ensure any time the page is re-rendered we resolve our components' state
    // properly
    OLCS.eventEmitter.on('render', setup);

    // when we click 'find'...
    $(document).on('click', submitSelector, function(e) {
      $(this).addClass('js-active');
      handleInput('.js-find').call(this, e);
    });

    // or we hit enter within the postcode input...
    $(document).on('keypress', inputSelector, function(e) {
      // keyCode is normalised; 13 is always enter
      if (e.keyCode === 13) {
        // we need .call here because it relies on 'this' for context
        handleInput('.js-find').call(this, e);
      }
    });

    // when we select an address from the dropdown...
    $(document).on('change', selectSelector, function(e) {
       $(this)
        .closest('.field')
        .prev('.field')
        .find('.js-find')
        .addClass('js-active');

       handleInput('.js-select').call(this, e);
    });

    // when we click the 'enter address manualy' button...
    $(document).on('click', manualSelector, function(e) {
      e.preventDefault();

      var fieldset = $(this).parents(container);

      // We add a class to the parent to let the component know we
      // are entering the address manually
      fieldset.addClass('manual-address');

      // we have to show our pristine address fields
      var inputs = fieldset.children('.field');
      inputs.find('[type=text]').val('');
      inputs.show();

      // ditch the address options, if present...
      fieldset.find(selectClass).remove();

      // and finally, remove this button's container
      $(this).parent().hide();
    });

  };

}(document, window.jQuery));
