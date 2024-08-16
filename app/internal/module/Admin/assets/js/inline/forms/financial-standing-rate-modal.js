$(function () {
    "use strict";

    var operatorTypesSelector = 'input[name="details[goodsOrPsv]"]';
    var licenceTypesSelector = 'input[name="details[licenceType]"]';
    var selectedOperatorTypeSelector = operatorTypesSelector + ":checked";
    var selectedLicenceTypeSelector = licenceTypesSelector + ":checked";

    var operatorTypes = $(operatorTypesSelector);
    var licenceTypes = $(licenceTypesSelector);
    var vehicleTypeFieldset = $('#fieldset-vehicle-type');

    function toggle()
    {
        var selectable = shouldVehicleTypeBeSelectable();
        var $naRadioButton = vehicleTypeFieldset.find('input[value="fin_sta_veh_typ_na"]');

        if (selectable) {
            if ($naRadioButton.is(":checked")) {
                $naRadioButton.prop('checked', false);
            }
        } else {
            $naRadioButton.prop('checked', true);
            vehicleTypeFieldset.find('input[value!="fin_sta_veh_typ_na"]').prop("checked", false);
        }

        $naRadioButton.prop("disabled", selectable);
        vehicleTypeFieldset.find('input[value!="fin_sta_veh_typ_na"]').prop("disabled", !selectable);
    }

    function shouldVehicleTypeBeSelectable()
    {
        var selectedOperatorType = $(selectedOperatorTypeSelector).val();
        var selectedLicenceType = $(selectedLicenceTypeSelector).val();
        return selectedOperatorType == "lcat_gv" && selectedLicenceType == "ltyp_si";
    }

    operatorTypes.change(function () {
        toggle();
    });

    licenceTypes.change(function () {
        toggle();
    });

    toggle();

    $('form[name="financial-standing-rate"]').submit(function (e) {
        var selectedVehicleType = vehicleTypeFieldset.find('input[name="details[vehicleType]"]:checked').val();

        if (shouldVehicleTypeBeSelectable() && typeof(selectedVehicleType) === 'undefined') {
            alert("You must select a vehicle type when Goods Vehicle and Standard International are selected.");
            return false;
        }

        return true;
    });
});
