$(function () {
    'use strict';
    $('input[type=radio][name=fields\\[tcOrOther\\]]').change(function () {
        if ($(this).val() === 'tc') {
            $('.otherUser').addClass('js-hidden');
            $('.tcUser').removeClass('js-hidden');
        } else {
            $('.otherUser').removeClass('js-hidden');
            $('.tcUser').addClass('js-hidden');
        }
    });
});
