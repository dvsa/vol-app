$(function() {
  "use strict";

  const vehicleSize = OLCS.formHelper.input("limousinesNoveltyVehicles", "size").val();
  let isSmallVehicles = Boolean(false);

  if (vehicleSize === "psvvs_small") {
    isSmallVehicles = true;
  }

  function limoChecked(value) {
    return function() {
      return OLCS.formHelper.isChecked("limousinesNoveltyVehicles", "psvLimousines", value);
    };
  }

  function show15g() {
    return function() {
      return OLCS.formHelper.isChecked("limousinesNoveltyVehicles", "psvLimousines", "Y") && !isSmallVehicles;
    };
  }

  OLCS.cascadeForm({
    cascade: false,
    rulesets: {
      "limousinesNoveltyVehicles": {
        "label:limousinesNoveltyVehicles\\[psvNoLimousineConfirmationLabel\\]": limoChecked("N"),
        "date:limousinesNoveltyVehicles\\[psvNoLimousineConfirmation\\]": limoChecked("N"),
        "label:limousinesNoveltyVehicles\\[psvOnlyLimousinesConfirmationLabel\\]": show15g(),
        "date:limousinesNoveltyVehicles\\[psvOnlyLimousinesConfirmation\\]": show15g(),
      },
    }
  });
});
