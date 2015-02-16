$(function() {

  "use strict";

  // get a nicer alias for our form helper
  var F = OLCS.formHelper;
  
  // cache some input lookups
  var niFlag = F('operator-location', 'niFlag');
  var operatorType = F('operator-type', 'goodsOrPsv');
  var licenceType = F('licence-type', 'licenceType');
  var discSequence = F('prefix', 'discSequence');
  var startNumber = F('discs-numbering', 'startNumber');
  var endNumber = F('discs-numbering', 'endNumber');
  var endNumberIncreased = F('discs-numbering', 'endNumberIncreased');
  var niFlagText = niFlag.filter(':checked').parent().text();
  var operatorTypeText = operatorType.filter(':checked').parent().text();
  var licenceTypeText = licenceType.filter(':checked').parent().text();
  var confirmDiscPrintingUrl = '/admin/disc-printing/confirm-disc-printing';
  var discPrintingUrl = '/admin/disc-printing';
 
  // two variations for the message
  var discsVoided;
  if (endNumberIncreased.val() === endNumber.val()) {
      discsVoided = ' with no discs voided.</p>';
  } else {
      discsVoided = ' with discs ' + (parseInt(endNumberIncreased.val()) + 1) + ' to ' + endNumber.val() + ' voided.</p>';
  }
  
  // custom confirmation modal window, we need separate component for this, ideally
  var message = [
    '<div id="popupMessage">',
      '<p>Printing submitted for location "' + niFlagText + '", operator type "' + operatorTypeText,
      '", licence type "' + licenceTypeText + '".</p>',
      '<p>The print run was between ' + startNumber.val() + ' and ' + endNumber.val() + discsVoided,
      '<p>Did the discs print successfully?</p>',
      '<div class="action-container">',
        '<a href id="discPrintingOk" class="action--primary large popupButtons">Yes</a>',
        '<a href id="discPrintingFailed" class="action--secondary large popupButtons">No</a>',
      '</div>',  
    '</div>'
  ].join('\n');
  
  OLCS.modal.show(message,'Discs printing');
  
  $('.popupButtons').click(function() {
    $('.popupButtons').attr('disabled', 'disabled');
    var isSuccessfull;
    if ($(this).attr('id') === 'discPrintingOk') {
      isSuccessfull = 1;
    } else {
      isSuccessfull = 0;
    }
    var data = {
      'isSuccessfull': isSuccessfull,
      'niFlag': niFlag.filter(':checked').val(),
      'operatorType': operatorType.filter(':checked').val(),
      'licenceType': licenceType.filter(':checked').val(),
      'discPrefix': $('#discSequence :selected').text(),
      'discSequence': discSequence.val(),
      'endNumber': endNumber.val(),
      'startNumberEntered': startNumber.val()
    };
    
    $('#confirmDiscWarning').remove();
    $.post(confirmDiscPrintingUrl, data, function(response) {
      $('.popupButtons').removeAttr('disabled');
      if (response.status === 500) {
          $('#popupMessage').prepend('<div class="notice--warning" id="confirmDiscWarning"><p>Error proccessing discs numbering</p></div>');
      } else {
        OLCS.modal.hide();
        window.location.href = discPrintingUrl + '/success/' + isSuccessfull;
      }
    });
    return false;  
  });
});
