$(function () {
    "use strict";

    var fieldset = 'fields';
    var chargeableCopiesRequiredField = 'copiesRequired';
    var nonChargeableCopiesRequiredField = 'copiesRequiredNonChargeable';
    var totalCopiesRequiredField = 'copiesRequiredTotal';

    var recalculateTotal = function () {
        var chargeableCopies = parseInt(OLCS.formHelper(fieldset, chargeableCopiesRequiredField).val()) || 0;
        var nonChargeableCopies = parseInt(OLCS.formHelper(fieldset, nonChargeableCopiesRequiredField).val()) || 0;

        var totalCopies = chargeableCopies + nonChargeableCopies;
        OLCS.formHelper(fieldset, totalCopiesRequiredField).val(totalCopies);
    };

    OLCS.formHelper(fieldset, chargeableCopiesRequiredField).on("keyup", recalculateTotal);
    OLCS.formHelper(fieldset, nonChargeableCopiesRequiredField).on("keyup", recalculateTotal);
});
