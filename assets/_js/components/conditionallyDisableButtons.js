var OLCS = OLCS || {};

/**
 * Conditionally Disable Button On Change
 *
 * Helper class to Disable the state of a button
 * based on the value of a piece of data in a button's
 * wrapping <tr>. Example options:
 * {
 *  dataElSelector: '[data-heading="Document status"]',
 *  dataElValToCheck1: 'New',
 *  dataElValToCheck2: 'Generated',
 *  buttonSelector1: '#publish',
 *  buttonSelector2: '#generate',
 *  stateAttr: 'disabled',
 *  stateAttrVal: true
 * }
 *
 */

OLCS.conditionallyDisableButtons = (function (document, $, undefined) {

  'use strict';

  var exports = {};

  return function init(options) {

    if (!options || !options.dataElSelector || !options.dataElValToCheck1 || !options.dataElValToCheck2 || !options.buttonSelector1 || !options.buttonSelector2 || !options.stateAttr || !options.stateAttrVal) {
      throw new Error('OLCS.disableButtonStateOnChange requires a dataElSelector, dataElValToCheck1, dataElValToCheck2, buttonSelector1, buttonSelector2, stateAttrVal and a stateAttr option');
    }

    exports.onChange = function() {
      var $checkedInput = $('input:checked');
      if ($checkedInput.length > 0) {
        var dataElVal = $checkedInput.parents('tr').find(options.dataElSelector).text();
        var disableButton1 = dataElVal === options.dataElValToCheck1;
        var disableButton2 = dataElVal === options.dataElValToCheck2;
        if (disableButton1) {
          return $(options.buttonSelector1).prop(options.stateAttr, options.stateAttrVal);
        }
        if (disableButton2) {
          return $(options.buttonSelector2).prop(options.stateAttr, options.stateAttrVal);
        }
        return OLCS.logger.warn('Data options supplied to OLCS.conditionallyDisableButtons (dataElValToCheck1/dataElValToCheck2) did not match value found in DOM element: ' + options.dataElSelector);
      }
    };

    return exports;

  };

}(document, window.jQuery));