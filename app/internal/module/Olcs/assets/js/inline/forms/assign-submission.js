$(function () {
    'use strict';
    $("button[name='form-actions\[submit]'\]").attr('disabled', 'disabled');
    $('input[type=radio][name=fields\\[tcOrOther\\]]').change(function () {
        $("button[name='form-actions\[submit]'\]").attr('disabled', false);
        if ($(this).val() === 'tc') {
            $('.otherUser').addClass('js-hidden');
            $('.tcUser').removeClass('js-hidden');
        } else {
            $('.otherUser').removeClass('js-hidden');
            $('.tcUser').addClass('js-hidden');
        }
    });

    // Force a selection of TC or Other User from the toggleable select boxes.
    $("button[name='form-actions\[submit]'\]").click(function (e) {
        if ($('.result-selected').length < 1) {
            e.preventDefault();
            alert('You must select a TC/DTC or Other Recipient');
        }
    });
});
