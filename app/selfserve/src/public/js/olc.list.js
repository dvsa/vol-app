(function ($) {
    olcs.list = olcs.list || {};

    var idListClassPattern    = /\bentity-id-(\d+)\b/,
        idNewListClassPattern = /\bentity-id-(\d+N)\b/,
        columnClassPattern = /\bcolumn-(\w+)\b/,
        urlTypePattern = /type=([^&]+)/,
        alphanumSort;

    alphanumSort = function(arrayToSort, caseInsensitive) {
        var z, t;

        for (z = 0, t; t = arrayToSort[z]; z++) {
            t = t.text;
            arrayToSort[z].text = [];
            var x = 0, y = -1, n = 0, i, j;

            while (i = (j = t.charAt(x++)).charCodeAt(0)) {
                var m = (i == 46 || (i >=48 && i <= 57));
                if (m !== n) {
                    arrayToSort[z].text[++y] = "";
                    n = m;
                }
                arrayToSort[z].text[y] += j;
            }
        }

        arrayToSort.sort(function(a, b) {
            for (var x = 0, aa, bb; (aa = a.text[x]) && (bb = b.text[x]); x++) {
                if (caseInsensitive) {
                    aa = aa.toLowerCase();
                    bb = bb.toLowerCase();
                }
                if (aa !== bb) {
                    var c = Number(aa), d = Number(bb);
                    if (c == aa && d == bb) {
                        return c - d;
                    } else {
                        return (aa > bb) ? 1 : -1;
                    }
                }
            }
            return a.text.length - b.text.length;
        });

        for (z = 0; z < arrayToSort.length; z++) {
            arrayToSort[z].text = arrayToSort[z].text.join('');
        }
    };

    olcs.list.getIdListColumns = function (table) {
        var columns = [];

        $(table).find('thead th').each(function () {
            var classes = $(this).attr('class'),
                matches = classes ? columnClassPattern.exec(classes) : [];

            columns.push(matches ? matches[1] : false);
        });

        return columns;
    };

    olcs.list.addIdListRow = function (table, data) {
        var $table = $(table),
            $form = $table.data('olcs-id-list-form'),
            id = parseInt(data.id, 10),
            rowData = {
                columnClasses : data.columnClasses,
                columns : data.columns,
            };

        // Check if row is already added
        if ($form.find('input[name="listIds[' + id + ']"]')[0]) {
            return;
        }

        $('<input />').val('1').attr({
            type : 'hidden',
            name : 'listIds[' + id + ']'
        }).appendTo($form);


        rowData.class = (rowData.class || '') + ' entity-id-' + id;

        olcs.list.addRow(table, rowData);
    };

    olcs.list.removeIdListRow = function (table, data) {
        var $table = $(table),
            $form = $table.data('olcs-id-list-form'),
            id = id || parseInt(data.id || data, 10);

        $form.find('input[name="listIds[' + id + ']"]').remove();
        $table.find('tr.entity-id-' + id).remove();
        
    };

    olcs.list.removeIdNewListRow = function (table, data) {
        var $table = $(table), id = data || data.id;
        
        $table.find('tr.entity-id-' + id).remove();
        $('.entity-id-' + id + 'F').remove();
    };
    
    olcs.list.removeIdListRowByElement = function () {
        var $row = $(this),
        $table = $row.closest('table'),
        matches = idListClassPattern.exec($row.attr('class'));

        id = matches ? matches[1] : false;

        if (id) {
            olcs.list.removeIdListRow($table, id);
        } else {
            matches = idNewListClassPattern.exec($row.attr('class'));
            id = matches ? matches[1] : false;
            
            if(id) {
                olcs.list.removeIdNewListRow($table, id); 
            }
        }            
    };

    olcs.list.removeIdListRowsBySelection = function () {
        $(this)
            .parent()
            .prev('table')
            .find('input.remove')
            .filter(':checked')
            .closest('tr')
            .each(olcs.list.removeIdListRowByElement);
    };

    olcs.list.addIdListRemoveCheckbox = function (row) {
        $('<input />', {type : 'checkbox'})
            .addClass('remove')
            .appendTo($('<td />').addClass('columntype-checkbox').appendTo(row));
    };

    olcs.list.enableIdListRemoval = function (table) {
        var $table = $(table).addClass('olcs-removable'),
            $td;

        $('<th />').addClass('text-muted').addClass('columntype-checkbox').text('Remove').appendTo($table.find('thead tr'));

        olcs.list.addIdListRemoveCheckbox($table.find('tbody tr'));

        $('<button />')
            .addClass('btn btn-remove')
            .attr('type', 'button')
            .text('Remove')
            .insertAfter($table)
            .click(olcs.list.removeIdListRowsBySelection)
            .wrap($('<div />').addClass('form-group'));
    };

    olcs.list.enableIdListAddition = function (table) {
        var $table = $(table).addClass('olcs-addable'),
            url = $table.data('overlay-form-url'),
            $accordionToggle = $table.parent().parent().prev().children('a.collapsible'),
            urlType = urlTypePattern.exec(url),
            $button;

        if (!url) {
            return;
        }

        $button = $('<button />')
            .addClass('btn btn-new')
            .attr('id', urlType[1] + 'Add')
            .attr('type', 'buttons')
            .text('Add')
            .magnificPopup($.extend({
                items: { src: url }
            }, olcs.config.magnificConfig));

        if ($accordionToggle.hasClass('acc-buttons')) {
            $button.addClass('pull-right').insertAfter($accordionToggle);
        } else {
            $button.insertBefore($table);
        }
    };

    olcs.list.newIndex = 0;
    
    olcs.list.addRowNew = function (fieldIndex, table, data) {
        data.class = 'entity-id-' + ++olcs.list.newIndex + 'N';
        
        olcs.list.addRow(table, data);
        $.each(data.fields, function (key, value) {
            $('<input />').val(value).attr({
                class: data.class + 'F',
                type : 'hidden',
                name : 'new['+fieldIndex+'][' + olcs.list.newIndex + ']['+key+']'
            }).appendTo($(table));  
        });
    },
    
    olcs.list.addRow = function (table, data) {
        var $row = $('<tr />'),
            $table = $(table),
            $tbody = $table.children('tbody');

        if ($.isArray(data)) {
            data = {columns : data};
        }

        $.each(data.columns, function (key, value) {
            var $column = $('<td />').appendTo($row);
            if (data.columnClasses && data.columnClasses[key]) {
                $column.addClass(data.columnClasses[key]);
            }
            if (typeof value === 'object') {
                $column.append(value);
            } else {
                $column.text(value);
            }
        });

        if ($table.hasClass('olcs-removable')) {
            olcs.list.addIdListRemoveCheckbox($row)
        }

        $row.addClass(data.class || '').appendTo($tbody[0] ? $tbody : $table);

        if ($table.hasClass('olcs-sortable')) {
            olcs.list.sortByColumn($table.find('thead th.sort-current'), true);
        }
    };

    olcs.list.sortByColumn = function (column, refresh) {
        if (column.preventDefault) {
            column.preventDefault();
            column = this;
        }

        var $column = $(column),
            $tbody = $column.closest('table').children('tbody'),
            sortAscending = !$column.hasClass('sort-asc'),
            values = [];

        if (!$column[0]) {
            return;
        }

        if (refresh) {
            sortAscending = !sortAscending;
        }

        $tbody.find('tr > td:nth-child(' + ($column.index() + 1) + ')').each(function () {
            var $td = $(this);
            values.push({
                text : $td.text(),
                row : $td.closest('tr')
            });
        });

        alphanumSort(values);

        $tbody.empty();
        $.each(sortAscending ? values : values.reverse(), function (key, value) {
            $tbody.append(value.row);
        });

        $column.addClass('sort-current')
            .siblings('th').removeClass('sort-current')
            .addBack().removeClass('sort-asc')
            .find('img.sort-arrow').removeClass('arrDn').addClass('arrUp');

        if (sortAscending) {
            $column.addClass('sort-asc');
            $column.find('img.sort-arrow').removeClass('arrUp').addClass('arrDn');
        }
    };

    olcs.list.enableSorting = function (table) {
        $(table).addClass('olcs-sortable')
            .find('thead th.columnSortable').click(olcs.list.sortByColumn);
    };

    olcs.list.maxHeight = function (table, maxContainerHeight, container) {
        var $table = $(table),
            $tbody = $table.find('tbody'),
            $container = container ? $(container) : $tbody,
            tableWidth = $table.width(),
            width = [],
            height;

        if ($container.outerHeight() <= maxContainerHeight) {
            return;
        }

        $table.find('th').each(function (i) {
            width[i] = Math.floor($(this).outerWidth() / tableWidth * 1000) / 10 + '%';
        });

        $table.find('tr').each(function (i) {
            $(this).children().each(function (i) {
                $(this).css('width', width[i]);
            });
        });

        $table.addClass('olcs-fixed-height-list');

        if (container) {
            $tbody
                .hide()
                .css('max-height', Math.max(140, maxContainerHeight - $container.outerHeight()) + 'px')
                .show();
        } else {
            $tbody.css('max-height', maxContainerHeight + 'px');
        }

        $table.find('thead').width($tbody.children('tr:first').width());
    };

    olcs.list.initiate = function (table) {
        var $table = $(table),
            $form;
        if ($table.hasClass('id-list-wrapper')) {
            $form = $table.children('form, .id-list-form');
            $table = $table.children('table');

            if (!$table.data('olcs-id-list-form')) {
                $table.data('olcs-id-list-form', $form);
            }

            olcs.list.enableIdListRemoval($table);
            olcs.list.enableIdListAddition($table);
        }
        if ($table.hasClass('olcs-sortable')) {
            olcs.list.enableSorting($table);
        }
    };

    olcs.jsToContext.push(function ($context) {
        $('div.id-list-wrapper', $context).each(function () {
           olcs.list.initiate(this);
        });
    });
}(jQuery));
