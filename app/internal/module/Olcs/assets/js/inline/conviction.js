jQuery(function () 
{
    $( document ).ready(function() {
        checkCategories();               
        
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
