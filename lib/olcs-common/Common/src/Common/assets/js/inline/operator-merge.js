$(function() {
    "use strict";

    $('#toOperatorId').change(function() {
        $('#toOperatorName').text('');
        if ($.isNumeric($(this).val())) {
            var ajaxUrl = $(this).data('lookup-url');
            $.getJSON(ajaxUrl +'?organisation='+ $(this).val(), null ,function(e) {
                if ($('#toOperatorName').length == 0) {
                    var orgNameElement = $('<span id="toOperatorName"></span');
                    $('#toOperatorId').after(orgNameElement);
                }
                $('#toOperatorName').text(e.name);
            });
        }
    });
});