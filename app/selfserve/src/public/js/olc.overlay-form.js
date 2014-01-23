(function ($) {
    olcs.overlayForm = olcs.overlayForm || {};

    var columnClassPattern = /\bcolumn-([\w-]+)\b/,
        convertObjectToData,
        mapDataToField,
        getMappableFields,
        getRowAsData,
        addRowToIdList,
        mapRowToField;

    convertObjectToData = function (data) {
        var converted;

        if (data.columns === undefined) {
            converted = {
                columnClasses : [],
                columns : [],
                columnNames : []
            };
            $.each(data, function (key, value) {
                if (key === 'id') {
                    converted.id = value;
                } else {
                    converted.columns.push(value);
                    converted.columnNames.push(key);
                }
            });
            data = converted;
        }

        return data;
    };

    olcs.overlayForm.mapDataToField = mapDataToField = function (fieldgroup, data, prefix, unmap) {
        var $fieldgroup = $(fieldgroup);

        prefix = prefix ? prefix + '-' : '';
        unmap = (unmap === true);
        data = convertObjectToData(data);

        $.each(data.columnNames, function (i, columnName) {
            $fieldgroup.find('.column-' + prefix + columnName).each(function () {
                var $input = $(this), $displayElement;

                if ($input.data('render-value-as')) {
                    $displayElement = $($input.data('render-value-as'));

                    if (unmap) {
                        
                        $displayElement.remove();
                        // This needs to be reworked to remove elements that 
                        // have been displayed during  page render and not via 
                        // js
                        //$('#operatorNameText').remove();
                    } else {
                        $displayElement = $($input.data('render-value-as'));
                        $displayElement.find('.value').text(data.columns[i]);
                        $input.data('render-value-as', $displayElement).after($displayElement);
                    }
                }

                if ($input.is(':input')) {
                    if (!$input.is('.' + prefix + 'reset-after-mapping')) {
                        $input.val(data.columns[i]).change();
                    }
                } else {
                    $input.text(data.columns[i]);
                }
            });
        });

        if (unmap) {
            $fieldgroup.find('.primary-' + prefix + 'id').filter(':input').val('');
            $fieldgroup.find('.primary-' + prefix + 'version').filter(':input').val('');
        } else {
            if (data.id !== undefined) {
                $fieldgroup.find('.primary-' + prefix + 'id').filter(':input').val(data.id);
            }
            if (data.version !== undefined) {
                $fieldgroup.find('.primary-' + prefix + 'version').filter(':input').val(data.version);
            }
        }

        $fieldgroup.find('.' + prefix + 'show-after-mapping').toggle(unmap === false).removeClass('hidden');
        $fieldgroup.find('.' + prefix + 'hide-after-mapping').toggle(unmap === true);

        if (!unmap) {
            $fieldgroup.find('.' + prefix + 'reset-after-mapping').val('').change();
        }
    };

    olcs.overlayForm.unmapDataToField = function (fieldgroup, data, prefix) {
        mapDataToField(fieldgroup, data, prefix, true);
    };

    olcs.overlayForm.getMappableFields = getMappableFields = function (fieldgroup, prefix) {
        var targets = [];

        prefix = prefix ? prefix + '-' : '';

        $(fieldgroup).find(':input, div, span').each(function () {
            var classes = $(this).attr('class'),
                matches = classes ? columnClassPattern.exec(classes) : false;

            if (matches && matches[1].indexOf(prefix) !== -1) {
                matches[1] = matches[1].substr(prefix.length);
                if ($.inArray(matches[1], targets) === -1) {
                    targets.push(matches[1]);
                }
            }
        });

        return targets;
    };

    getRowAsData = function (cell, targets) {
        var $cell = $(cell).closest('td'),
            $row = $cell.closest('tr'),
            $table = $row.closest('table'),
            $list = $($table.data('id-list')).children('table'),
            data = {
                id : $cell.data('entity-id'),
                version : $cell.data('entity-version'),
                columnClasses : [],
                columns : [],
                columnNames : []
            };

        $.each(targets, function (i, columnName) {
            if (columnName) {
                var $source = $row.find('.column-' + columnName);
                data.columnClasses.push($source.attr('class'));
                data.columns.push($source.text());
                data.columnNames.push(columnName);
            }
        });

        return data;
    };

    addRowToIdList = function (e) {
        var $cell = $(this),
            $list = $($cell.closest('table').data('id-list')).children('table'),
            data = getRowAsData($cell, olcs.list.getIdListColumns($list));

        olcs.list.addIdListRow($list, data);

        e.preventDefault();
        $.magnificPopup.instance.close();
    };

    mapRowToField = function (e) {
        var $cell = $(this),
            fieldgroup = $cell.closest('table').data('mapped-fields'),
            prefix = $cell.closest('table').data('fields-prefix'),
            targets = [],
            data;

        targets = getMappableFields(fieldgroup, prefix);
        data = getRowAsData($cell, targets);

        mapDataToField(fieldgroup, data, prefix);

        e.preventDefault();
        $.magnificPopup.instance.close();
    };

    var initiateOverlayForm = function () {
        var $popupContent = $(this);

        $popupContent.find('.btn-cancel').click(function (e) {
            e.preventDefault();
            $.magnificPopup.instance.close();
        });

        $popupContent.find('form.overlay-form').submit(function (e) {
            e.preventDefault();

            var $form = $(this),
                action = $form.attr('action');

            $.magnificPopup.instance.open($.extend({
                items: {
                    src: action + (action.indexOf('?') === -1 ? '?' : '&') + $form.serialize()
                }
            }, olcs.config.magnificConfig));
        });

        $popupContent.find('.tPagBar a').click(function () {
            $.magnificPopup.instance.open($.extend({
                items: {
                    src: $(this).attr('href')
                }
            }, olcs.config.magnificConfig));
            return false;
        });

        $popupContent.find('table.olcs-list-table').each(function () {
            var $table = $(this);
            if ($table.data('id-list') || $table.data('mapped-fields')) {
                $table.find('td.primary-column')
                    .wrapInner('<a />').find('a')
                    .attr('href', '#').click($table.data('id-list') ? addRowToIdList : mapRowToField);
            }
            olcs.list.initiate($table);
            olcs.list.maxHeight($table, Math.floor($(window).height() * 0.9), $popupContent.closest('.popup-overlay'));
        });
    };

    olcs.jsToContext.push(function ($context) {
        $('.popup-content, .embedded-overlay-form', $context).each(initiateOverlayForm)
    });
}(jQuery));
