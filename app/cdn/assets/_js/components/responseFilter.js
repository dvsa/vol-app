var OLCS = OLCS || {};

/**
 * Filter response
 */

OLCS.filterResponse = (function(document, $, undefined) {

  "use strict";

  return function init(filter, container) {

    if (!container) {
      throw new Error("OLCS.filterResponse requires a container argument");
    }

    return OLCS.normaliseResponse(function(response) {
      var content = response.body;

      if (filter) {

        // we MUST wrap the plain content in a container so that
        // .find() works consistently; without this if the top-level
        // element was the one we wanted to filter the find would fail
        //
        // Adding this container has no impact since we never actually
        // mutate content with it; we're just temporarily adding it
        // so we can search its children
        var filtered = $("<div>")
          .append(content)
          .find(filter)
          .html();

        if (filtered) {
          content = filtered;
        }
      }

      OLCS.formHelper.render(container, content);
      OLCS.formHelper.renderModalTitle(response.title);
    });
  };

}(document, window.jQuery));
