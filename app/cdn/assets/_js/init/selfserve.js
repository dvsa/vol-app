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
  module.exports = window.cookieManager;
  //window.cookieManager.init(window.cookieConfig);
  OLCS.GOVUKversion = "5.4.1";
});
