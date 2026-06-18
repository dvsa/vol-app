$(function() {
  "use strict";

  // quick helper to DRY up our definitions a bit
  function checked(fieldset, field) {
    return function() {
      return OLCS.formHelper.isChecked(fieldset, field);
    };
  }

  OLCS.cascadeForm({
    form: "form",
    cascade: false,
    rulesets: {
      "data[prevHasLicence-table]":         checked("data", "prevHasLicence"),
      "data[prevHadLicence-table]":         checked("data", "prevHadLicence"),
      "data[prevBeenDisqualifiedTc-table]": checked("data", "prevBeenDisqualifiedTc"),
      "eu[prevBeenRefused-table]": checked("eu", "prevBeenRefused"),
      "eu[prevBeenRevoked-table]": checked("eu", "prevBeenRevoked"),
      "pi[prevBeenAtPi-table]": checked("pi", "prevBeenAtPi"),
      "assets[prevPurchasedAssets-table]": checked("assets", "prevPurchasedAssets")
    }
  });
});
