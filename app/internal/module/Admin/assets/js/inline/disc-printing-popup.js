/**
 * @todo Not sure why this section has been done differently. This JS file is essentially constructing a view which is
 * the controllers job. Think this may be due to the fact that the previous page POSTs back to the controller and a
 * modal is optionally shown. This should be handled in the same way as type-of-licence i.e. we submit the previous form
 * using js which means we can catch the response and display in a modal.
 */
$(function () {

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
    var queueId = $('#queueId');
    var securityToken = document.getElementById("security").value;

  // two variations for the message
    var discsVoided;
    if (endNumberIncreased.val() === endNumber.val()) {
        discsVoided = ' with no discs voided.</p>';
    } else {
        discsVoided = ' with discs ' + (parseInt(endNumberIncreased.val()) + 1) + ' to ' + endNumber.val() + ' voided.</p>';
    }

  // custom confirmation modal window, we need separate component for this, ideally
    if (niFlag.filter(':checked').val() === 'Y') {
        var message = [
        '<div id="popupMessage">',
        '<p>Printing submitted for location "' + niFlagText + '", ',
        'licence type "' + licenceTypeText + '".</p>',
        '<p>The print run was between ' + startNumber.val() + ' and ' + endNumber.val() + discsVoided,
        '<p>Did the discs print successfully?</p>',
        '<div class="action-container">',
          '<a href id="discPrintingOk" role="button" draggable="false" class="govuk-button large popupButtons" data-module="govuk-button">Yes</a>',
          '<a href id="discPrintingFailed" role="button" draggable="false" class="govuk-button large popupButtons" data-module="govuk-button">No</a>',
        '</div>',
        '</div>'
        ].join('\n');
    } else {
        var message = [
        '<div id="popupMessage">',
        '<p>Printing submitted for location "' + niFlagText + '", operator type "' + operatorTypeText,
        '", licence type "' + licenceTypeText + '".</p>',
        '<p>The print run was between ' + startNumber.val() + ' and ' + endNumber.val() + discsVoided,
        '<p>Did the discs print successfully?</p>',
        '<div class="action-container">',
          '<a href id="discPrintingOk" role="button" draggable="false" class="govuk-button large popupButtons" data-module="govuk-button">Yes</a>',
          '<a href id="discPrintingFailed" role="button" draggable="false" class="govuk-button govuk-button--secondary large popupButtons" data-module="govuk-button">No</a>',
        '</div>',
        '</div>'
        ].join('\n');
    }

    OLCS.modal.show(message,'Discs printing');

    $('.popupButtons').click(function () {
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
            'startNumberEntered': startNumber.val(),
            'queueId': queueId.val(),
            'security': securityToken
        };

        $('#confirmDiscWarning').remove();
        $.post(confirmDiscPrintingUrl, data, function (response) {
            $('.popupButtons').removeAttr('disabled');
            if (response.status === 500) {
                $('#popupMessage').prepend('<div class="notice--warning" id="confirmDiscWarning"><p>Error proccessing discs numbering</p></div>');
            } else {
                OLCS.modal.hide();
                window.location.href = discPrintingUrl + 'success/' + isSuccessfull;
            }
        });
        return false;
    });
});
