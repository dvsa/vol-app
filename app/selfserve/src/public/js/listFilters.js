/*
 * List left filter javascript. Hides the traffic-area div onload
 */

jQuery(function () {
    //General init area for hiding any list filters
    $('#traffic-area').hide();
    $('#traffic-area').parent().find('a.listFilterItem img').removeClass('sprCollapse').addClass('sprExpand');
    //shows and hides each filter section
    $("a.listFilterItem").click(function (e) {
        e.preventDefault();
        $(this).parent().find('a.listFilterItem img').toggleClass('sprCollapse').toggleClass('sprExpand');
        $(this).parent().next('ul').slideToggle('fast');
        $(this).parent().parent().find('div.clearListFilters').toggleClass('clearListFiltersPad');
        // Added as filtering does page request
        var showHides = $('#leftFilters .filterSection a img');
        $('#leftFilters').attr('data-filters-open', '');
        $(showHides).each(function(index, element) {
            if ($(element).hasClass('sprCollapse')) {
                 $('#leftFilters').attr('data-filters-open',  $('#leftFilters').attr('data-filters-open')+0);
            } else {
                 $('#leftFilters').attr('data-filters-open',  $('#leftFilters').attr('data-filters-open')+1);
            }
        });
    });
    // Clears all the filter
    $('.clearAllListFilters').click(function (e) {
        e.preventDefault();
        $('#leftFilters input').attr('checked', false);
        $('div.clearListFilters').remove();
        getFliteredList();
    });
    //clears filters for the selected section and removes the "clear" line
    $('.filterSection').delegate( ".clearListFilters a", "click", function(e) {
        e.preventDefault();
        $(this).parent().parent().find('input').attr('checked', false);
        $(this).parent().remove();
        getFliteredList();
    });
    // Adds clear if any filter section has inputs checked
    $('#leftFilters ul li input').click(function (e) {
        var hasClearFilter = $(this).parent().parent().parent().next('div.clearListFilters').length;
        var filterLine = $(this).parent().parent().parent();
        if ($(filterLine).find('input').is(':checked')) {
            if (!hasClearFilter) {
                filterLine.after('<div class="filterHead clearListFilters"><a href="#">Clear</a></div>');
            }
        } else {
            filterLine.next('div.clearListFilters').remove();
        }
        getFliteredList();
    });
    
    // Function to trigger ajax call when filter item is checked or unchecked.
    function getFliteredList(activeFilters) {
        var activeFilters = $('#leftFilters input:checked');
        var baseUrl = $("#leftFilters").attr('data-base-url');
        queryString='';
        $(activeFilters).each(function(index, element) {
            if (queryString==='') {
                queryString = "lf"+index+"="+element.id
            } else {
                queryString = queryString+"&lf"+index+"="+element.id
            }
        });
        //queryString = queryString+'&'+$("#leftFilters").attr('data-url');
        queryString = queryString+'&'+$("#leftFilters").attr('data-url')+'&open='+$('#leftFilters').attr('data-filters-open');
        location.href=$("#leftFilters").attr('data-base-url')+'?'+queryString;
        //----- Not used as filters currently do a page regresh
        /*$.ajax({
                type: "GET",
                url: $("#leftFilters").attr('data-base-url'),
                data: queryString,
        }).done(function(data) {
                $("#listTable").replaceWith(data);
                $('.ttips').tooltip('hide');
        });*/
        //------
        delete queryString;
    }
    
});


