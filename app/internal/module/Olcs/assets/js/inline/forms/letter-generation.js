OLCS.ready(function () {
    "use strict";

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
        } else {
            $allContent.show();
            $('.letter-section__toggle').text('Hide');
            $allSections.addClass('letter-section--expanded');
            $(this).text('Hide All Sections');
        }
    });

    // Function to update button state based on checkbox selection
    function updateCreateButtonState() {
        var $checkboxes = $('input[name="letterIssues[]"]:checked');
        var $createBtn = $('#create-letter-btn');

        if ($checkboxes.length > 0) {
            $createBtn.prop('disabled', false).removeClass('govuk-button--disabled');
        } else {
            $createBtn.prop('disabled', true).addClass('govuk-button--disabled');
        }
    }

    // Create letter button click - show placeholder message
    $('body').on('click', '#create-letter-btn', function(e) {
        e.preventDefault();

        var $checkboxes = $('input[name="letterIssues[]"]:checked');
        var $errorDiv = $('#validation-error');
        var $placeholderMsg = $('#placeholder-message');

        if ($checkboxes.length === 0) {
            $errorDiv.show();
            $errorDiv[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            $placeholderMsg.hide();
            return false;
        }

        // Hide error and show placeholder message
        $errorDiv.hide();
        $('#selected-issues-count').text($checkboxes.length);
        $placeholderMsg.show();
        $placeholderMsg[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // Update button state and hide messages when checkbox changes
    $('body').on('change', 'input[name="letterIssues[]"]', function() {
        updateCreateButtonState();
        var $checkboxes = $('input[name="letterIssues[]"]:checked');
        if ($checkboxes.length > 0) {
            $('#validation-error').hide();
        }
        $('#placeholder-message').hide(); // Hide placeholder when selection changes
    });

    // Cancel button - close modal
    $('body').on('click', '#cancel-letter-btn', function(e) {
        e.preventDefault();
        if (typeof OLCS !== 'undefined' && OLCS.modal && OLCS.modal.hide) {
            OLCS.modal.hide();
        }
    });

    // Set initial button state on page load
    updateCreateButtonState();
});
