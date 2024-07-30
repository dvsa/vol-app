var OLCS = OLCS || {};

/**
 * Modal link
 *
 * Triggers an AJAX request, the response from which is
 * used to populate a modal
 *
 * Typically invoked by binding a listener on links which
 * want to open in a modal
 *
 * @NOTE: the way this is named is the same as OLCS.submitForm
 * but they behave differently (this binds listeners, that
 * actually fires a form immediately).
 */

OLCS.modalLink = (function(document, $, undefined) {

  'use strict';

  return function init(options) {

    var trigger = options.trigger;

    $(document).on('click', trigger, function(e) {
      e.preventDefault();

      // stop any other things like table rows getting greedy
      // and causing this event to re-trigger
      e.stopPropagation();

      var key = $(this).attr('href');

      OLCS.ajax({
        url: key,
        // bear in mind this component will create a modalForm wrapper
        success: OLCS.modalResponse(),
        preloaderType: 'modal'
      });
    });
  };

}(document, window.jQuery));
