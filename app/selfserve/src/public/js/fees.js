/**
 * Javascript for sorting pages on data lists.
 *  Implemented as page load with window.location.href
 */
jQuery(function () {
    
    // Open popup if a payment status is set
    if (olcs.paymentStatus != undefined) {
        $.magnificPopup.open({
            items: {
                src: $('#paymentDialog')
            },
            type: 'inline',
            closeOnContentClick: false,
            closeOnBgClick: false,
            enableEscapeKey: false,
            callbacks: {
                close: function() {
                    window.location.href = window.location.pathname;
                }
            }
        });
    }
    
    // Sets the list to be scrollable
    var $table = $('#fees-list').find('table');
    olcs.list.maxHeight($table, 200);
    
    $(document).on("click", "#paymentOverlayCancelbutton", function() {
        $.magnificPopup.close();
        // redirects to fees page without the query string
        window.location.href = window.location.pathname;
    });
    
    $(document).on("click", "#cardPaymentCancelButton", function() {
        window.location.href = window.location.pathname;
    });
    
    $(document).on("change", "#paymentTypeSelect", function() {
        var frm = $('#paymentType');
            switch(this.value) {
                case 'card-payment':
                  olcs.ajax.request(this, frm.serialize(),  'getProviderCardForm');
                  $('#fees-list table td input').attr('disabled', 'disable')
                  break;
                case 'receipt':
                case 'account-balance':
                  olcs.ajax.request(this, frm.serialize(),  'getPaymentForm');
                  break;
            }
    });
    
    $(document).on("click", "#payCancel", function() {
        $('#payGroup').remove();
    });
    
    $(document).on("click", "#fees-list table td input", function() {
        if ($('#fees-list table td input:checked').length) {
            $('#paymentTypeSelect').removeAttr('disabled')
        } else {
            $('#paymentTypeSelect').attr('disabled', 'disable');
        }
    });
    
    // Initialise sorting on the fees page
    olcs.list.initiate($('#fees-list table'));
    
});

function getProviderCardForm() {
    if ($( "#payGroup" ).length)  $( "#payGroup" ).remove();
    $("#cardPaymentFormContainer").replaceWith(olcs.ajax.response);
}

function getPaymentForm() {
    if ($( "#payGroup" ).length) {
        $( "#payGroup" ).replaceWith(olcs.ajax.response);
    } else {
        $( "#paymentFormExtras" ).after(olcs.ajax.response);
    }
    
}