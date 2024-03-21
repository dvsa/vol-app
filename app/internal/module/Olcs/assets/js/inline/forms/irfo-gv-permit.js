$(function () {
    'use strict';

    var calculatedExpiryDateText = $('#calculatedExpiryDateText');

    function addToInForceDate(noOfMonths)
    {
      // add noOfMonths to the in force date
        var inForceDateDay = parseInt($('[name="fields\\[inForceDate\\]\\[day\\]"]').val());
        var inForceDateMonth = parseInt($('[name="fields\\[inForceDate\\]\\[month\\]"]').val());
        var inForceDateYear = parseInt($('[name="fields\\[inForceDate\\]\\[year\\]"]').val());

        if (!isNaN(inForceDateDay) && !isNaN(inForceDateMonth) && !isNaN(inForceDateYear)) {
            var date = new Date(inForceDateYear, inForceDateMonth - 1, inForceDateDay);
            date.setMonth(date.getMonth() + noOfMonths);
            date.setDate(date.getDate() - 1);

            return ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
        }

        return null;
    }

    function calculateExpiryDate()
    {
        var calculated;

      // get current values
        var irfoGvPermitType = parseInt(OLCS.formHelper('fields', 'irfoGvPermitType').val());
        var yearRequired = parseInt(OLCS.formHelper('fields', 'yearRequired').val());

        switch (irfoGvPermitType) {
            case 1:  // ECMT 100% (Jan to Mar)
            case 2:  // ECMT 75% (Apr to Jun)
            case 3:  // ECMT 50% (Jul to Sep)
            case 4:  // ECMT 25% (Oct to Dec)
            case 7:  // Georgia
            case 8:  // Germany 3rd Country
            case 9:  // Kazakhstan
            case 11: // Morocco Multi Journey x 15
            case 13: // Russia
            case 14: // Tunisia
            case 15: // Turkey 3rd Country
            case 16: // Turkey Multi Journey x 4
            case 18: // Turkey Single Journey
            case 19: // Turkey 3rd Country Non-Transit
            case 20: // Serbia
                if (!isNaN(yearRequired)) {
                  // end of the required year
                    calculated = '31/12/' + yearRequired;
                }
            break;

            case 6:  // Belarus
            case 12: // Romania 3rd Country
            case 17: // Ukraine
                if (!isNaN(yearRequired)) {
                    // end of Jan in a year following the required year
                    calculated = '31/01/' + (yearRequired + 1);
                }
            break;

            case 5:  // ECMT Community Removal
              // 1 year from the In force date
                calculated = addToInForceDate(12);
            break;

            case 10: // Morocco Single Journey
            case 21: // Morocco Hors contingent
            case 22: // Morocco empty entry
              // 3 months from the In force date
                calculated = addToInForceDate(3);
            break;
        }

        calculatedExpiryDateText.html(calculated || 'unknown');
    }

  // calculate on init
    calculateExpiryDate();

  // watch all relevant form fields
    OLCS.formHelper('fields', 'irfoGvPermitType').on('change', calculateExpiryDate);
    OLCS.formHelper('fields', 'yearRequired').on('change', calculateExpiryDate);
    $('[name="fields\\[inForceDate\\]\\[day\\]"]').on('change', calculateExpiryDate);
    $('[name="fields\\[inForceDate\\]\\[month\\]"]').on('change', calculateExpiryDate);
    $('[name="fields\\[inForceDate\\]\\[year\\]"]').on('change', calculateExpiryDate);
});
