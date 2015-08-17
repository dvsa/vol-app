OLCS.ready(function() {

  $('a[data-file-url]').each(function() {

    var fileUrl = $(this).data('file-url');

    if (!OLCS.browser.isIE) {
      $(this).attr('href', '#');
      $(this).parent().append('<div class="clear"><div class="guidance">Please copy the following link into Internet Explorer to open the file:<br /><strong>' + fileUrl + '</strong></div></div>');
      $(this).replaceWith('<span>' + $(this).html() + '</span>');
      return;
    }

    $(this).attr('href', fileUrl);
  });

});
