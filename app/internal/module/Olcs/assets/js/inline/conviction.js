jQuery(function () 
{
    $( document ).ready(function() {
        showDependantTypeFields($('#defType'));
        checkCategories();               
        
        $('body').on("change","#defType", function(e) {
            showDependantTypeFields(this);
        });

        $('body').on("change","#parentCategory", function(e) {
            getSubCategory($('#parentCategory').val());
        });

        $('body').on("change", "#category", function(e) {
            getDescription();
        });
        
        $('body').on("click", "#conviction", function(e) {
            $('#categoryText').prop('disabled', false);
        });
    });
});
