jQuery(function () {
    $('.submission-edit-section').wrapInner($('<div />').addClass('submission-edit-content')).each(function () {
        $('<a />').attr('href', '#').addClass('action-edit').text('Edit').appendTo(this);
    });
    $('.submission-edit-section > a.action-edit').click(function (e) {
        e.preventDefault();

        var $link = $(this),
            $editSection = $link.parent('.submission-edit-section'),
            $sectionContent = $editSection.children('.submission-edit-content'),
            sectionKey = $editSection.attr('id').replace('submission-', ''),
            sectionUrl = (window.location + '')
                .replace('/submission', '/submission/section')
                .replace('?', '?section=' + encodeURIComponent(sectionKey) + '&');

        $link.addClass('loading');
        $.get(sectionUrl, function (data) {
            if (data.html) {
                $link.hide();

                var $textarea = $('<textarea />').val(data.html);

                $sectionContent
                    .data('section-version', data.version)
                    .data('section-html', data.html)
                    .empty().append($textarea);

                $('<button />').text('Save').addClass('btn btn-save btn-small').insertAfter($textarea).click(function () {
                    var $button  = $(this).addClass('loading');

                    tinyMCE.triggerSave();

                    $.post(sectionUrl, {
                            version : $sectionContent.data('section-version'),
                            html : $textarea.val()
                        }, function (data) {
                            $sectionContent.html(data.html);
                            olcs.applyJSToContext($sectionContent);
                            $link.show();
                        }, 'json')
                        .fail(function () {
                            $sectionContent.find('.error-lookup').remove();
                            olcs.errorMessage('An error occured').insertAfter($textarea);
                        })
                        .complete(function () {
                            $button.removeClass('loading');
                        });
                });
                $('<button />').text('Cancel').addClass('btn btn-cancel').insertAfter($textarea).click(function () {
                    $sectionContent.html($sectionContent.data('section-html'));
                    olcs.applyJSToContext($sectionContent);
                    $link.show();
                });

                tinymce.init($.extend({
                    selector: '#submission-' + sectionKey + ' textarea',
                    height: 400,
                    content_css: '/css/bootstrap.min.css,/css/bootstrap-theme.min.css,/css/style.css'
                }, olcs.config.tinyMceConfig));
            }
        })
        .fail(function () {
            $sectionContent.find('.error-lookup').remove();
            olcs.errorMessage('An error occured').appendTo($sectionContent);
        })
        .complete(function () {
            $link.removeClass('loading');
        });
    });
});
