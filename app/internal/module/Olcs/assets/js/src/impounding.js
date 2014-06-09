/**
 * Javascript for impoundings page.
 * Author: Ian Lindsay
 */

function checkVenueOther() {
    var venue = $('#piVenue').val();

    if(venue === '' || venue > 0) {
        toggleVenueOther('hide');
    } else {
        toggleVenueOther('show');
    }
}

function checkImpoundingType() {
    var impoundingType = $('#impoundingType').val();
    
    switch(impoundingType){
        case 'impounding_type.1':
            toggleHearingFieldset('show');
            break;
        default:
            toggleHearingFieldset('hide');
            break;
    }
}

function toggleVenueOther(action) {
    if(action === 'show'){
        $('#piVenueOther').show();
        $('label[for="piVenueOther"]').show();
    }
    else{
        $('#piVenueOther').val('');
        $('#piVenueOther').hide();
        $('label[for="piVenueOther"]').hide();
    }
}

function toggleHearingFieldset(action) {
    if(action === 'show'){
        $( "fieldset:eq(1) select, fieldset:eq(1) input" ).prop('disabled', false);
        $( "fieldset:eq(1)" ).show();
    }
    else{
        $( "fieldset:eq(1) select, fieldset:eq(1) input" ).prop('disabled', 'disabled');
        $( "fieldset:eq(1)" ).hide();
    } 
}
