/**
 * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
 */
OLCS.ready(function() {
  "use strict";

  OLCS.cascadeInput({
    source: "#category",
    dest: "#subCategory",
    process: process("/list/sub-categories")
  });

  function process(url) {
    /**
     * We use the outer closure to bind the URL to fetch from;
     * all other behaviour is the same
     */
    return function(value, callback) {
      $.get(url + "/" + value, function(result) {
        if (result[0] && result[0].value === "") {
          // always shift off the first empty value
          result.shift();
        }
        callback(result);
      });
    };
  }
});
