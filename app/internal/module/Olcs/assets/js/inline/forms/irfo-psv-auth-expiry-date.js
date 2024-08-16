$(function () {
    'use strict';

    var id = OLCS.formHelper('fields', 'id').val();
    var calculatedExpiryDateText = $('#calculatedExpiryDateText');

    function isRenewal()
    {
        var status = OLCS.formHelper('fields', 'status').val();

        return (status === 'irfo_auth_s_renew');
    }

    function addYearsToFieldValue(fieldName, noOfYears)
    {
      // add noOfYears to a value of the fieldName
        var day = parseInt($('[name="fields\\[' + fieldName + '\\]\\[day\\]"]').val());
        var month = parseInt($('[name="fields\\[' + fieldName + '\\]\\[month\\]"]').val());
        var year = parseInt($('[name="fields\\[' + fieldName + '\\]\\[year\\]"]').val());

        if (!isNaN(noOfYears) && !isNaN(day) && !isNaN(month) && !isNaN(year)) {
            var date = new Date(year, month - 1, day);
            date.setFullYear(date.getFullYear() + noOfYears);
            date.setDate(date.getDate() - 1);

            return ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
        }

        return null;
    }

    function calculateExpiryDate()
    {
        var calculated;

      // get current values
        var validityPeriod = parseInt(OLCS.formHelper('fields', 'validityPeriod').val());

        if (isRenewal()) {
          // renewal
            calculated = addYearsToFieldValue('expiryDate', validityPeriod);
        } else {
            calculated = addYearsToFieldValue('inForceDate', validityPeriod);
        }

        calculatedExpiryDateText.html(calculated || 'unknown');
    }

    if (!id || isRenewal()) {
      // calculate on init
        calculateExpiryDate();

      // watch all relevant form fields
        OLCS.formHelper('fields', 'validityPeriod').on('change', calculateExpiryDate);
        $('[name="fields\\[inForceDate\\]\\[day\\]"]').on('change', calculateExpiryDate);
        $('[name="fields\\[inForceDate\\]\\[month\\]"]').on('change', calculateExpiryDate);
        $('[name="fields\\[inForceDate\\]\\[year\\]"]').on('change', calculateExpiryDate);
    } else {
      // hide the hint
        calculatedExpiryDateText.parent().hide();
    }
});
