jQuery(function () 
{
    $( document ).ready(function() {
        checkWithdrawn(); 
        
        $('body').on("change", "#isWithdrawn", function() {
            checkWithdrawn();
        });
    });
    
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
            $('label[for="withdrawnDate"]').hide();
        }
    }
});
