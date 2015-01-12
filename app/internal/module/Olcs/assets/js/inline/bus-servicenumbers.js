OLCS.ready(function() {

    var targetFieldset = $('fieldset[data-group="fields[otherServices]"]');
    var numberOfFields = targetFieldset.length;

    addAnother = function () {
        var html = '<fieldset data-group="fields[otherServices][__id__]">' +
            '<div class="field ">' +
            '<input name="fields[otherServices][__id__][serviceNo]" class="" id="serviceNo" value="" type="text">' +
            '</div>' +
            '</fieldset>'.replace('__id__', numberOfFields);

        numberOfFields++;
        targetFieldset.append(html);
    }

    $('button[name="fields[addOne]"]').on('click', addAnother);
});
