var OLCS = OLCS || {};

/**
 * Notices
 *
 * Allows the user to close flash messages, and fades them out
 * automatically after a few seconds
 */

OLCS.notices = (function(document, $, undefined) {

  'use strict';

  var closeLinkClass          = 'notice__close';
  var noticeContainerSelector = '.notice-container';
  var noticeSelector          = 'div[class^="notice--"]';

  return function init() {

    function addCloseButton(element) {
      element.prepend('<a href="" aria-label="Close notification" class="' + closeLinkClass + '">Close</a>');  
    }

    function remove(element) {
      $(element).remove();
    }

    function fadeOut(element) {
      $(element)
        .delay(10000)
        .fadeOut(300, function() {
          remove(element);
      });
    }

    $(document).on('click', '.' + closeLinkClass, function(e) {
      e.preventDefault();
      // If there is more than one notice, remove itself
      if ($(this).parents(noticeSelector).siblings().length) {
        remove($(this).parents(noticeSelector));
      } else {
        // Otherwise remove the whole notice container
        remove($(this).parents(noticeContainerSelector));
      }
    });

    OLCS.eventEmitter.on('render', function() {

      // Add a close button to each notice, but only
      // if it doesn't have one
      $(noticeSelector).each(function() {
        if (!$(this).find('.' + closeLinkClass).length) {
          addCloseButton($(this));
        }
      });

      // fade out any notice containers on render,
      // so long as they're not in a modal or in the
      // right hand column
      $('.internal ' + noticeContainerSelector).each(function() {
        if (!$(this).parents().is('.modal, .sidebar--right')) {
          fadeOut($(this));
        }
      });

    });

  };

}(document, window.jQuery));
