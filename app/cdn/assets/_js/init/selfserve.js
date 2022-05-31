var module = window.module || {};
OLCS.ready(function() {

  "use strict";

  OLCS.toggleElement({
    triggerSelector: ".proposition__toggle",
    targetSelector: ".proposition-nav"
  });

  OLCS.tooltip({
    parent: ".tooltip-parent"
  });

  OLCS.accessibility();

  OLCS.searchFilter();

  //load govuk frontend
  window.GOVUKFrontend.initAll();
  module.exports = window.cookieManager;
  //window.cookieManager.init(window.cookieConfig);
  OLCS.GOVUKversion = "4.1.0";
});
