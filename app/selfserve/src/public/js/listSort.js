/**
 * Javascript for sorting pages on data lists.
 *  Implemented as page load with window.location.href
 */
jQuery(function () {
    
    $(document).on("click", ".tableContent table.olcs-list-table:not(.id-list-table) .columnSortable", function() {
        var thisId = this.id,
            $column = $(this),
            $table = $column.closest("table.olcs-list-table"),
            $dirt = $column.find('img'),
            dirt;

        $table.find('.columnSortable').not($column)
            .find('img').removeClass('arrDn').addClass('arrUp');

        $dirt.toggleClass('arrUp').toggleClass('arrDn');

        if ($dirt.hasClass('arrUp')) {
            dirt = 'up';
        } else {
            dirt = 'dn';
        }

        var baseUrl = 'dir='+dirt+'&'+'column='+this.id+(
            $table.attr('data-base-url') ?
                '&' + $table.attr('data-base-url') :
                ''
            );

        if ($column.closest('.mfp-container').length) {
            $.magnificPopup.instance.open($.extend({
                items: {
                    src: $table.attr('data-url')+'?'+baseUrl
                }
            }, olcs.config.magnificConfig));
        } else {
            window.location.href = $table.attr('data-url')+'?'+baseUrl;
        }
    });
    
});
