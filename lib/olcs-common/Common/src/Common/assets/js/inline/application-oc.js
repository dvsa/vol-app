OLCS.ready(function() {
  "use strict";

  OLCS.cascadeInput({
    source: "#dataTrafficArea\\[trafficArea\\]",
    dest: "#dataTrafficArea\\[enforcementArea\\]",
    url: "/list/enforcement-area"
  });
});
