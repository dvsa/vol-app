jQuery(function () 
{
    $( document ).ready(function() {
        checkImpoundingType(); 
        checkVenueOther();
        
        $('body').on("change", "#impoundingType", function() {
            checkImpoundingType();
        });
        
        $('body').on("change", "#piVenue", function() {
            checkVenueOther();
        });
    });
});
