(function ($) {
    $.fn.multiselect = function () {
        this.each(function () {
            var $select = $(this),
                $optgroups = $select.find('optgroup'),
                $multiselect = $('<ul />').addClass('multiselect'),
                isMulti = $select.prop('multiple');

            if (!$optgroups.length) {
                $optgroups = $select;
            }

            $optgroups.each(function () {
                var $optgroup = $(this),
                    $options = $optgroup.children('option'),
                    $groupcontainer = $('<ul />').addClass('options').hide(),
                    $selectioncontainer = isMulti ? $('<ul />').addClass('selections') : false;
                $options.each(function () {
                    var $option = $(this),
                        $element = $('<a />', { href : '#' });
                    $option.data('multiselect-element', $element);
                    $element
                        .text($option.text() || '\xA0')
                        .appendTo($groupcontainer)
                        .wrap('<li />')
                        .click(function (e) {
                            e.preventDefault();
                            if (!$(this).hasClass('selected')) {
                                var values = $select.val();
                                if (isMulti) {
                                    values = values || [];
                                    values.push($option.val());
                                } else {
                                    values = $option.val();
                                }
                                $select.val(values).change();
                            }
                        });
                });
                $groupcontainer.appendTo($multiselect).wrap('<li />');
                $('<a />', { href : '#' })
                    .text($optgroup.attr('label') || '\xA0')
                    .insertBefore($groupcontainer)
                    .click(function (e) {
                        e.preventDefault();
                        if ($groupcontainer.is(':hidden')) {
                            $groupcontainer.stop().show();
                            setTimeout(function () {
                                $(document).one('click', function () {
                                    $groupcontainer.stop().hide();
                                });
                            }, 1);
                        }
                    });
                $groupcontainer.parent().wrapInner('<div />');
                if ($selectioncontainer) {
                    $groupcontainer.parent().parent().append($selectioncontainer);
                    $optgroup.data('multiselect-selections', $selectioncontainer);
                }
            });

            // Sync the presentation of selected values on a change
            $select.after($multiselect).change(function () {
                $multiselect.find('ul.selections').empty();
                $multiselect.find('ul.options .selected').removeClass('selected');
                $select.find(':selected').each(function () {
                    var $option = $(this),
                        $selectioncontainer = $option.parent().data('multiselect-selections'),
                        $removeButton;

                    $option.data('multiselect-element').addClass('selected');

                    if (!$selectioncontainer) {
                        $option.data('multiselect-element')
                            .closest('div')
                            .children('a:first')
                            .text($option.text() || '\xA0');
                    } else {
                        $removeButton = $('<a />', {
                                href : '#',
                                text : 'X'
                            })
                            .click(function (e) {
                                e.preventDefault();
                                var values = $select.val() || [],
                                    key = $.inArray($option.val(), values);
                                if (key !== -1) {
                                    values.splice(key, 1);
                                }
                                $select.val(values).change();
                            });
                        $('<li />')
                            .text(($option.text() || '\xA0') + ' ')
                            .append($removeButton)
                            .appendTo($selectioncontainer);
                    }
                });
            });

            // Initiate the current values
            $select.change();
        });
        return this;
    };
}(jQuery));
