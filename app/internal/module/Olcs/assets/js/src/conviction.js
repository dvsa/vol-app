/**
 * Javascript for convictions page.
 *  Author: Mike Cooper
 *  Author: Ian Lindsay
 */

function checkCategories() {
    var parentCategory = $('#parentCategory').val();
    var subCategory = $('#category').val();    
    
    if (parseInt(parentCategory)) {
        current_data = {};
    
        getDataCategories(parentCategory).success(function (data) {
            $.each(data.categories,function(key, value) {
                current_data[value.id] = value.description;
            });
        });
    }
    
    if (subCategory == 168) {
        $('#categoryText').prop('disabled',false);
    } else {
        $('#categoryText').prop('disabled','disabled');
    }
}

function getSubCategory(parentCategory){
    $select = $('#category');
    $textarea = $('#categoryText');
    $textarea.val('');
    $textarea.prop('disabled','disabled');
    $select.find('option').remove();
    $select.append('<option value="">Loading...</option>');
    $select.prop('disabled','disabled');
    current_data = {};

    if(!parseInt(parentCategory)){
        $select.find('option').remove();
        $select.append('<option value="">Please Select</option>');
    } else { 
        getDataCategories(parentCategory).success(function (data) {
            $select.find('option').remove();
            $select.append('<option value="">Please Select</option>');
            $.each(data.categories,function(key, value) 
            {
                $select.append('<option value=' + value.id + '>' + value.description.substring(0,73) + '</option>');
                current_data[value.id] = value.description;
            });
            
            $select.prop('disabled',false);
        });
    }
}

function getDescription(){
    subCategory = $('#category').val()
    $textarea = $('#categoryText');
    $textarea.prop('disabled','disabled');
    
    if (subCategory == '') {
        $textarea.val('');
    } else if (subCategory == 168) {
        $textarea.prop('disabled',false);
        $textarea.val('User defined: ');
        var strLength= $textarea.val().length;
        $textarea.focus();
        $textarea[0].setSelectionRange(strLength, strLength);
    } else { 
        $textarea.val(current_data[subCategory]);
    }
    
}

function getDataCategories(parentCategory){
    return $.ajax({
        type:"POST",
        url:"/ajax/convictions/categories",
        dataType: 'json',
        data: {parent:parentCategory},
    });
}

function showDependantTypeFields(dependant) 
{
    if ($(dependant).val() == 'defendant_type.operator') {
        $('#personFirstname, #personLastname').val('');
        $('#personFirstname, #personLastname').parent().addClass('visually-hidden');
        $("[name='defendant-details[dateOfBirth][month]']").parent().addClass('visually-hidden');
        $('#operatorName').parent().removeClass('visually-hidden');
    } else {
        $('#personFirstname, #personLastname').parent().removeClass('visually-hidden');
        $('#operatorName').val('');
        $('#operatorName').parent().addClass('visually-hidden');
        $("[name='defendant-details[dateOfBirth][month]']").parent().removeClass('visually-hidden');
    }
}
