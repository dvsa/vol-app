OLCS.ready(function () {
    "use strict";

    console.log('[VOL-5516] letter-generation.js loaded');

    // Handle individual section toggles
    $('body').on('click', '.letter-section__header', function(e) {
        e.preventDefault();
        var $section = $(this).closest('.letter-section');
        var $content = $section.find('.letter-section__content');
        var $button = $(this).find('.letter-section__toggle');

        if ($content.is(':visible')) {
            $content.hide();
            $button.text('Show');
            $section.removeClass('letter-section--expanded');
        } else {
            $content.show();
            $button.text('Hide');
            $section.addClass('letter-section--expanded');
        }

        console.log('[VOL-5516] Toggled section:', $section.find('.letter-section__title').text());
    });

    // Handle "Show All Sections" / "Hide All Sections" toggle
    $('body').on('click', '#toggle-all-sections', function(e) {
        e.preventDefault();
        var $allSections = $('.letter-section');
        var $allContent = $('.letter-section__content');
        var allVisible = $allContent.filter(':visible').length === $allContent.length;

        if (allVisible) {
            $allContent.hide();
            $('.letter-section__toggle').text('Show');
            $allSections.removeClass('letter-section--expanded');
            $(this).text('Show All Sections');
            console.log('[VOL-5516] Collapsed all sections');
        } else {
            $allContent.show();
            $('.letter-section__toggle').text('Hide');
            $allSections.addClass('letter-section--expanded');
            $(this).text('Hide All Sections');
            console.log('[VOL-5516] Expanded all sections');
        }
    });

    console.log('[VOL-5516] Letter generation accordion handlers initialized');
});
