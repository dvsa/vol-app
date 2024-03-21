OLCS.ready(function () {

    var template = 'Please click or copy the following link into Internet Explorer to open the file:<br /><strong style="word-wrap: break-word;" class="word-wrap"><a id="letter-link" class="govuk-link" href="%s">%l</a></strong>';

    OLCS.eventEmitter.on('render', function () {

        $('.modal a[data-file-url]').each(function () {

            if (!OLCS.browser.isIE && !OLCS.browser.isFirefox) {
                var fileUrl = $(this).data('file-url');

                var link = template.replace('%s', fileUrl);

                link = link.replace('%l', fileUrl);

                $(this).parent().append('<div class="govuk-inset-text">' + link  + '</div>');

                $(this).replaceWith('<span>' + $(this).html() + '</span>');
            }

        });

    });

    if (!OLCS.browser.isIE && !OLCS.browser.isFirefox) {
        $(document).on('click', 'table a[data-file-url]', function (e) {

            e.preventDefault();

            var fileUrl = $(this).data('file-url');

            var body = template.replace('%s', fileUrl);
            body = body.replace('%l', fileUrl);
            var title = 'Open document';

            OLCS.modal.show(body, title);

            OLCS.eventEmitter.once('hide:modal', function () {
                OLCS.preloader.hide();
            });
        });
    }

});