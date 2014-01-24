/* 
 * All Javascript code relating to the application process -> licence details tab
 */

$(document).ready(function(){
    
    
    // hide establishment form unless establishementAddressYN is set to 'no'
    $('#establishmentAddressForm').hide();
    $('BODY').on("change","#establishmentAddressYN", function(e){
        if ($('#establishmentAddressYN').val() == 'yes') {
            $('#establishmentAddressForm').hide();
        } else {
            $('#establishmentAddressForm').show();
        }
    });
    
    // activate find address button when postcode is entered
    $('BODY').on("keyup","#correspondence\\[postcode\\]", function(e){
        if ( $("#correspondence\\[postcode\\]").val() == "" ) {
            $("#correspondence\\[findaddressbutton\\]").prop('disabled', true).toggleClass('disabled', true);
        } else {
            $("#correspondence\\[findaddressbutton\\]").prop('disabled', false).toggleClass('disabled', false);
        }
    });
});


    
/*(function ($) {
    olcs.licence = olcs.licence || {};
    console.log($("#establishmentAddressYN").val());
    $('BODY').on("change","#establishmentAddressYN", function(e){

        alert('here');
        
    });

}(jQuery));*/