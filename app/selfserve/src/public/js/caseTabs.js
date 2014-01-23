/**
 * Accordion for pages.
 * If you add the class "accordion" to the parent div then it will collapse all. Otherwise it
 * will open and close one at a time.
 */
jQuery(function () {
    
    $( "#caseDetailsTabs" ).delegate( "li", "click", function(event) {
        event.preventDefault();
        if ($(this).attr('data-url')) {
            var url = $(this).attr('data-url');
            var caseId = $(this).attr('data-caseId');
            //window.location.href=url+'?caseId='+caseId;
            window.location.href=url;
        }
        $( "#caseDetailsTabs li" ).removeClass('active');
        $( "#caseDetailsTabs li a img" ).removeClass('active');
        var link = $(this).find('a');
        if ($(link).hasClass('tab-dashboard')) {
            var img = $(link).find('img');
            $(img).addClass('active');
        }
        $(this).addClass('active');
    });
    
});


