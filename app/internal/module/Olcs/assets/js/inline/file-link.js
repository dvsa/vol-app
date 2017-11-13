OLCS.ready(function() {

  var template = 'Please copy the following link into Internet Explorer to open the file:<br /><strong style="word-wrap: break-word;" class="word-wrap">%s</strong>';

  OLCS.eventEmitter.on('render', function() {

    $('.modal a[data-file-url]').each(function() {

      if (!OLCS.browser.isIE && !OLCS.browser.isFirefox) {

        var fileUrl = $(this).data('file-url');

        $(this).parent().append('<div class="guidance">' + template.replace('%s', fileUrl) + '</div>');

        $(this).replaceWith('<span>' + $(this).html() + '</span>');
      }

    });

  });

  if (!OLCS.browser.isIE && !OLCS.browser.isFirefox) {

    $(document).on('click', 'table a[data-file-url]', function (e) {

      e.preventDefault();

      var fileUrl = $(this).data('file-url');

      if(fileUrl.charAt(0) === '/') {
          fileUrl = window.location.origin + fileUrl;
      }

      var body = template.replace('%s', fileUrl);
      var title = 'Open document';

      OLCS.modal.show(body, title);

      OLCS.eventEmitter.once('hide:modal', function() {
        OLCS.preloader.hide();
      });
    });
  }

});
