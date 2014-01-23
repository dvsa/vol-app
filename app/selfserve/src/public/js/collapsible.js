/**
 * Accordion for pages.
 * If you add the class "accordion" to the parent div then it will collapse all will open one at a time.
 * Add "accordionAll" to expand each item individually.
 * will open and close one at a time.
 */
jQuery(function () {
        $("a.collapsible").click(function (e) {
            e.preventDefault();
            var accordion = $(this).closest('.accordion');
            if (accordion.length) {
                var listDiv = ($(this).hasClass('acc-buttons')) ? $(this).parent().next('div') : $(this).next('div');
                if (!$(this).hasClass('expanded')) {
                    $(accordion).find('a').removeClass('expanded');
                    $(this).addClass('expanded');
                    $(accordion).find('.accordion-row').hide();
                    listDiv.slideToggle('fast');
                }
            } else {
                var listDiv = ($(this).hasClass('acc-buttons')) ? $(this).parent().next('div') : $(this).next('div');
                listDiv.slideToggle('fast');
                $(this).toggleClass('expanded');
            }
        });
});


