var OLCS = OLCS || {};

/**
 * Modal
 *
 * Must be provided with content and an optional title.
 * Currently only allows for one modal to be displayed at
 * a time (may need addressing in future).
 */

OLCS.modal = (function(document, $, undefined) {

  'use strict';

  /**
   * local variable declarations and public export
   */
  var exports = {};

  /**
   * private interface
   */
  var selector  = '.modal';
  var wrapper   = '.modal__wrapper';
  var overlay   = '.overlay';
  var header    = '.modal__title';
  var content   = '.modal__content';
  var bodyClass = 'disable-scroll';
  var inputs    = 'textarea, input, select';
  var modalTabbableElements = '.modal__wrapper *, .modal--alert *';
  var pageTabbableElements = 'a, input, select, textarea, button, body, [tabindex]:not([tabindex^="-"])';

  var closeSelectors = selector + '__close, ' + content + ' #cancel';

  var template = [
    '<div class="overlay" style="display:none;"></div>',
    '<div class="modal__wrapper" style="display:none;">',
      '<div class="modal" role="dialog" aria-labelledby="modal-title" tabindex="1">',
        '<div class="modal__header">',
          '<h1 class="modal__title" id="modal-title"></h1>',
        '</div>',
        '<div class="modal__content"></div>',
        '<a href="#" class="modal__close" aria-label="close">Close</a>',
      '</div>',
    '</div>'
  ].join('\n');

    function restrictTabFocus() {

        $(pageTabbableElements).not(modalTabbableElements).attr('tabIndex', -1);

    }

    function restoreTabFocus() {

        $(pageTabbableElements).not(modalTabbableElements).removeAttr('tabIndex');

    }

  /**
   * public interface
   */
  exports.show = function(body, title) {

    // Prevents scrolling issues on mobile Safari
    if ('ontouchstart' in window) {
      $(document).on('focus', inputs, function() {
        $(wrapper).css('position', 'absolute');
      }).on('blur', inputs, function() {
        $(wrapper).css('position', '');
      });
    }

    // if there isn't a modal showing already, insert the
    // template and give the body a special class
    if ($('body').find(overlay).length === 0) {
      $('body')
        .prepend(template)
        .addClass(bodyClass);
    }

    // insert the title and content into the modal
    $(header).html(title || '');
    $(content).html(body);

    // now we've got everything we need it's time to show it
    $(wrapper + ',' + overlay).show();

    // focus on the modal itself
    $(selector).focus();

    OLCS.eventEmitter.emit('show:modal');

    // let other potentially interested components know
    // there's been a render event
    OLCS.eventEmitter.emit('render');

    // if we've previously opened a modal and scrolled it our modal wrapper
    // needs resetting
    $(wrapper).scrollTop(0);

    $(document).keyup(function(e) {
      if (e.keyCode === 27 && exports.isVisible()) {
        e.preventDefault();
        exports.hide();
      }
    });

    // Set the aria-hidden attribute of all other content to 'true'
    // whilst the modal is open
    $('.page-wrapper').attr('aria-hidden', 'true');

    /**
     * Attempt to dynamically re-size a chosen select dropdown if the modal
     * is too small to contain it
     */
    if ($(selector).find('.chosen-container').length) {
      var modalPos = $(selector).position().top + $(selector).outerHeight(true);
      var chosenPos = $('.chosen-container').position().top + $('.chosen-container').outerHeight(true);

      if ((modalPos - chosenPos) < 450) {
        $(selector).find('.chosen-results').height('105px');
      }
    }

    // restrict tabbing to modal
    restrictTabFocus();

  };

  exports.hide = function() {
    // sometimes we want to trigger a different action when we
    // hide the modal, such as showing a confirmation box.
    var form = $(content).find('form[data-close-trigger]');

    if (form.length) {
      var selector = form.data('close-trigger');
      $(selector).trigger('click');
      return;
    }

    // clean things up
    $('body').removeClass(bodyClass);
    $(wrapper +','+overlay).remove();

    // Set the aria-hidden attribute of all other content to 'false'
    // when the modal closes
    $('.page-wrapper').attr('aria-hidden', 'false');

    // restore tabbing to page elements
    restoreTabFocus();

    // let other components know that the modal is hidden
    OLCS.eventEmitter.emit('hide:modal');

  };

  exports.isVisible = function() {
    return $(wrapper).is(':visible');
  };

  exports.updateBody = function(body) {
    var position = $(wrapper).scrollTop();
    OLCS.formHelper.render(content, body);
    $(wrapper).scrollTop(position);
  };

  $('body').on('click', closeSelectors, function(e) {
    e.preventDefault();
    exports.hide();
  });

  OLCS.eventEmitter.on('render', function () {
    // restore focus to last focused element
    // if that was removed set focus on the next element
    if (typeof exports.lastFocus !== 'undefined' && !exports.isVisible()) {
      exports.nextFocusableSelector = false;
      for ( var i = 0; i < exports.nextFocusables.length; i++ ) {
        exports.nextFocusableSelector = OLCS.generateCSSSelector($(exports.nextFocusables[i]));
        if ($(exports.nextFocusableSelector).length) {
          break;
        }
      }
      var focusSelector = $(exports.lastFocusSelector).length ?
          exports.lastFocusSelector :
          exports.nextFocusableSelector;
      if(focusSelector && typeof focusSelector !== 'undefined') {
        $(focusSelector).focus().addClass('focused');
      }
    }
    // cache the original overflow value
    var overflow = $(selector).css('overflow');
    // change the modal's overflow when enhanced dropdown is active
    $('[class*="chosen-select"]').on('chosen:showing_dropdown', function () {
      $(this).parents(selector).css('overflow', 'visible');
    });
    // revert overflow when enhanced dropdown is deactive
    $('[class*="chosen-select"]').on('chosen:hiding_dropdown', function () {
      $(this).parents(selector).css('overflow', overflow);
    });
  });

  return exports;

}(document, window.jQuery));
