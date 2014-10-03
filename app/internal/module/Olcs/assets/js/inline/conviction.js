var OLCS = OLCS || {};

/**
 * Cascade Input
 *
 * Given a source and destination input, and a process callback
 * invoked when the source value changes, apply those changes
 * to the destination input.
 *
 * Currently assumes destination is a select (which obviously
 * won't be true once this component is adopted a bit more).
 */

OLCS.showHideInput = (function(document, $, undefined) {

    "use strict";

    return function init(options) {
        var destination = $(options.dest);
        var trap = options.trap === undefined ? true : options.trap;
        var predicate = options.predicate === undefined ? function (value) { return true; } :options.predicate;

        $(document).on("change", options.source, function(e) {
            e.preventDefault();

            // make sure the event doesn't bubble up if we've askesd for it to be
            // trapped. This is useful because it prevents more generic change
            // listeners (like say a form submit) from firing prematurely
            if (trap) {
                e.stopPropagation();
            }

            if (predicate.call(this, $(this).val())) {
                destination.parent('div.field').show();
            } else {
                destination.parent('div.field').hide();
            }
        });

        if (predicate.call(this, $(options.source).val())) {
            destination.parent('div.field').show();
        } else {
            destination.parent('div.field').hide();
        }
    };

}(document, window.jQuery));

$(function() {

    var defendantType = function (value) {
        return value != 'def_t_op';
    };

    OLCS.showHideInput({
        'source': 'select[name="fields[defendantType]"]',
        'dest': 'label[for="fields[personFirstname]"]',
        'predicate': defendantType
    });

    OLCS.showHideInput({
        'source': 'select[name="fields[defendantType]"]',
        'dest': 'label[for="fields[personLastname]"]',
        'predicate': defendantType
    });

    OLCS.showHideInput({
        'source': 'select[name="fields[defendantType]"]',
        'dest': 'label[for="fields[birthDate]"]',
        'predicate': defendantType
    });
});

$(function() {
	
	var category = $('#category');
	var categoryText = $('#categoryText');
	
	category.change(function() {
		if ($(this).val() != '') {
			//categoryText.val('');
			categoryText.prop('readonly', 'true');
			categoryText.val($(this).find('*:selected').html());
		} else {
			categoryText.removeProp('readonly');
			categoryText.val('');			
		}
	});
	
	/*category.change(function() {
		if ($(this).val() == '') {
			categoryText.removeProp('readonly');
			categoryText.val('');
		}
	});*/
});