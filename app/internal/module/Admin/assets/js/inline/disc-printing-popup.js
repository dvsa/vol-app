$(function() {

  "use strict";

  // get a nicer alias for our form helper
  var F = OLCS.formHelper;
  
  // cache some input lookups
  var niFlag = F("operator-location", "niFlag");
  var operatorType = F("operator-type", "goodsOrPsv");
  var licenceType = F("licence-type", "licenceType");
  var startNumber = F("discs-numbering", "startNumber");
  var endNumber = F("discs-numbering", "endNumber");
  var originalEndNumber = F("discs-numbering", "originalEndNumber");
  var niFlagText = niFlag.filter(":checked").parent().text();
  var operatorTypeText = operatorType.filter(":checked").parent().text();
  var licenceTypeText = licenceType.filter(":checked").parent().text();
 
  // two variations for the message
  var discsVoided;
  if (originalEndNumber.val() === endNumber.val()) {
      discsVoided = ' with no discs voided.</p>';
  } else {
      discsVoided = ' with discs ' + originalEndNumber.val() + ' to ' + endNumber.val() + ' voided.</p>';
  }
  
  // custom confirmation modal window, we need separate component for this, ideally
  var message = [
        '<div>',
          '<p>Printing submitted for location "' + niFlagText + '", operator type "' + operatorTypeText,
          '", licence type "' + licenceTypeText + '".</p>',
          '<p>The print run was between ' + startNumber.val() + ' and ' + endNumber.val() + discsVoided,
          '<p>Did the discs print successfully?</p>',
          '<div class="action-container">',
            '<a href id="discPrintingOk" class="action--primary large">Yes</a>',
            '<a href id="discPrintingFailed" class="action--secondary large">No</a>',
          '</div>',  
        '</div>'
  ].join('\n');

  OLCS.modal.show(message,'Discs printing');
  
});
