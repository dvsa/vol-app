/**
 * Toggles part and full column data in lists
 */
jQuery(function () {
    $( ".olcs-list-table" ).delegate( ".colToggle", "click", function() {
            $(this).find('img').toggleClass('arrRt')
                                                .toggleClass('arrDn');
            $(this).find('.theRest').toggleClass('hide');
    });
});