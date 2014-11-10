$(function() {

    var defendantType = function (value) {
        return !(value == 'def_t_op' || value == '');
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
