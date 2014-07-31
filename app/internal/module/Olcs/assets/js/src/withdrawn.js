function checkWithdrawn() {
    if ($('#isWithdrawn').prop('checked')) {
        toggleWithdrawnDate('show');
    } else {
        toggleWithdrawnDate('hide');
    }
}

function toggleWithdrawnDate(action) {
    if(action === 'show'){
        $( "select[name*='[withdrawnDate]']" ).show();
        $('label[for="withdrawnDate"]').show();
    }
    else{
        $( "select[name*='[withdrawnDate]']" ).hide();
        $( "select[name*='[withdrawnDate]']" ).val('');
        $('label[for="withdrawnDate"]').hide();
    }
}



