(function() {
  "use strict";

  OLCS.logger.setLevel("ERROR");

  beforeEach(function() {
    $("body").html('');
    OLCS.eventEmitter.listeners = {};
  });

  afterEach(function() {
    // any global teardown?
  });

}());
