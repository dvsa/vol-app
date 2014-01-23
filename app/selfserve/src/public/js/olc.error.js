(function ($) {
    olcs.errorMessage = function (messages) {
        var $container = $('<div />').addClass('error-lookup error-lookup-js'),
            $errorList = $('<ul />').appendTo($container);

        messages = $.isArray(messages) ? messages : [messages];

        $.each(messages, function (i, message) {
            $('<li />').text(message).appendTo($errorList);
        })

        return $container;
    };
}(jQuery));