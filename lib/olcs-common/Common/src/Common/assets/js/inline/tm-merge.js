$(function() {
    'use strict';

    $('#toTmId').change(function() {
        $('#toTmName').text('');
        if ($.isNumeric($(this).val())) {
            var ajaxUrl = $(this).data('lookup-url');
            $.getJSON(ajaxUrl +'?transportManager='+ $(this).val(), null ,function(e) {
                if ($('#toTmName').length == 0) {
                    var element = $('<span id="toTmName"></span');
                    $('#toTmId').after(element);
                }
                $('#toTmName').text(e.name);
            });
        }
    });
});
