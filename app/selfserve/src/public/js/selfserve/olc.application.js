/* 
 * JS for any page within the application pages
 */

(function ($) {
    olcs.jsToContext.push(function ($context) {
        $('#popup-PersonSearchForm')
            .find('.required :input')
            .change(olcs.validateOnFormChange)
            .keyup(olcs.validateOnFormChange)
            .change();
    });
}(jQuery));
    
$(document).ready(function(){


    $("#searchOperatorPopup").magnificPopup(olcs.config.magnificConfig);

    $("#mainOperatorName").keyup(function(e) {
        if ( $("#mainOperatorName").val() == "" ) {
            $("#searchOperatorButton").prop('disabled', true).toggleClass('disabled', true);
        } else {
            $("#searchOperatorButton").prop('disabled', false).toggleClass('disabled', false);
        }
    });

    $("#tradingNameId").keyup(function(e) {
        if ( $("#tradingNameId").val() == "" ) {
            $("#tradingAddAnother").prop('disabled', true).toggleClass('disabled', true);
        } else {
            $("#tradingAddAnother").prop('disabled', false).toggleClass('disabled', false);
        }
    });

    $('BODY').on("click","#tradingAddAnother", function(e){
        $('#tradingNameText').show();
        $('#tradingNameText').html($('#tradingNameText').html()+"<span class='tradingNameEntry'>"+$('#tradingNameId').val() + ' <a href="#" class="removeTradingName">X</a></span>');
        $('#tradingNameId').val("");
        $("#tradingAddAnother").prop('disabled', true).toggleClass('disabled', true);
    });

    $('BODY').on("click",".removeTradingName", function(e){
        $(this).parent().remove();
    });

    $('BODY').on("change","#tradingDropdown", function(e){
        if ( $(this).val() == "Other" ) {
            $("#tradingOther").removeClass("hidden");
            $("#tradingOther").focus();
        } else {
            $("#tradingOther").addClass("hidden");
        }
    });

    // Copy operator name to name container
    // Unhide name container
    // Hide input
    $('BODY').on("click","#popupNewbutton", function(e){
        //
        $('#searchOperatorPopup').attr("href", $('#searchOperatorPopup').attr("href"));
        $('#mainOperatorName').hide();
        $('#searchOperatorButton').hide();
        $('#operatorNameText').show();
        $('#operatorNameId').val($('#popupOperatorName').val());
        $('#operatorNameText').html('<a href="#" id="editOperatorName">'+$('#popupOperatorName').val() + '</a> <a href="#" id="removeOperatorName" class="closeCross">X</a>');
        $('#operatorNameTextHidden').val($('#popupOperatorName').val());

        var magnificPopup = $.magnificPopup.instance;
        magnificPopup.close();
    });

    $('BODY').on("click","#removeOperatorName", function(e){
        $('#mainOperatorName').show();
        $('#searchOperatorButton').show();
        $('#operatorNameTextHidden').val('');
        $('#operatorNameText').html('');
    });

    // Edit Operator Name OLCS-582
    $('BODY').on("click","#editOperatorName", function(e){
        $.magnificPopup.instance.open($.extend({
            items: {
                src: '/application/search/editoperator'
            },
            callbacks: {
              ajaxContentAdded: function() {
                  $('#popupOperatorName').val($('#operatorNameTextHidden').val());
                  $('#popupOperatorName').focus();
                  olcs.applyJSToContext($(this.content));
              }
            }
        }, olcs.config.magnificConfig));
    });

    // Save operator name after edit
    $('BODY').on("click","#popupSaveOperatorEditButton", function(e){

        if ( $('#popupOperatorName').val() != "" ) {
            $('#searchOperatorPopup').attr("href", $('#searchOperatorPopup').attr("href"));
            $('#mainOperatorName').hide();
            $('#searchOperatorButton').hide();
            $('#operatorNameText').show();
            $('#operatorNameId').val($('#popupOperatorName').val());
            $('#operatorNameText').html('<a href="#" id="editOperatorName">'+$('#popupOperatorName').val() + '</a> <a href="#" id="removeOperatorName" class="closeCross">X</a>');
            $('#operatorNameTextHidden').val($('#popupOperatorName').val());
        } else {
            $('#mainOperatorName').show();
            $('#searchOperatorButton').show();
            $('#operatorNameTextHidden').val('');
            $('#operatorNameText').html('');
            $('#mainOperatorName').val('');
        }


        var magnificPopup = $.magnificPopup.instance;
        magnificPopup.close();
    });

    // operator searches coming from within main page
    $('BODY').on("click","#searchOperatorButton", function(e){
        var searchTerm = $('#mainOperatorName').val();
        var src = '/application/search/operator/'+searchTerm;
        $.magnificPopup.instance.open($.extend({
            items: {
                src: src
            }
        }, olcs.config.magnificConfig));
    });

    // operator searches coming from within popup
    $('BODY').on("click","#popupSearchbutton", function(e){
        var searchTerm = $('#popupOperatorName').val();
        var src = '/application/search/operator/'+searchTerm;
        $.magnificPopup.instance.open($.extend({
            items: {
                src: src
            }
        }, olcs.config.magnificConfig));
    });


     $('BODY').on("click", "#popup-application-director-saveAndAddAnotherButton", function(e){
         $('#popup-application-director-saveButton').click();
         $('#application-directorAdd').click();
     });
    
    /*
     * Click New button to close popup and render person details in parent form
     */
    $('BODY').on("click", "#popup-application-director-Newbutton", function(e){
  
        // hide search results
        $('#popup-application-director-PersonSearchForm .olcs-list-table').closest('.row').hide();
        // hide search button    
        $('#popup-application-director-searchPersonButton').addClass('hidden');
        $('#popup-application-director-Newbutton').addClass('hidden');
        
        // add required class
        $('#popup-application-director-PersonSearchForm div.form-group').addClass('required');
        
        // add save button
        $('#popup-application-director-saveButton').removeClass('hidden');
        $('#popup-application-director-saveAndAddAnotherButton').removeClass('hidden');
    
        olcs.applyJSToContext( $('#popup-personSearchForm'));

    });

    /**
     * Function to add person details to parent form and close the overlay
     */
    $('BODY').on("click", "#popup-saveButton", function(e){
        olcs.overlayForm.mapDataToField('#personSearchForm', {
            'id' : '',
            'firstname' : $('#popup-person-first-name').val(),
            'surname' : $('#popup-person-surname').val(),
            'dob-day' : $('#popup-personDob\\[day\\]').val(),
            'dob-month' : $('#popup-personDob\\[month\\]').val(),
            'dob-year' : $('#popup-personDob\\[year\\]').val(),
            'name' : $('#popup-person-first-name').val() + ' ' + $('#popup-person-surname').val(),
            'dob' : $('#popup-personDob\\[day\\]').val() + '-' +
                    $('#popup-personDob\\[month\\]').val() + '-' +
                    $('#popup-personDob\\[year\\]').val()
        });

        e.preventDefault();
        $.magnificPopup.instance.close();
    });
    
    /**
     * click event to add person details to owner list and close the overlay
     */
    $('BODY').on("click", "#popup-application-director-saveButton", function(e){
        olcs.list.addRowNew('owners', '#owner-list table', {
            
        columns: {
            name : $('#popup-application-director-person-first-name').val() + ' ' + $('#popup-application-director-person-surname').val(),
            dob : $('#popup-application-director-personDob\\[day\\]').val() + '-' +
                    $('#popup-application-director-personDob\\[month\\]').val() + '-' +
                    $('#popup-application-director-personDob\\[year\\]').val(),
            disqualified: 'N'
        },
    
        columnClasses: {
            name: 'primary-column column-name',
            dob: 'column-dob',
            disqualified: 'column-disqualified columntype-bool'
        },
        
        fields: {
            first_name : $('#popup-application-director-person-first-name').val(),
            surname: $('#popup-application-director-person-surname').val(),
            date_of_birth: 
                    $('#popup-application-director-personDob\\[year\\]').val()  + '/' +
                    $('#popup-application-director-personDob\\[month\\]').val() + '/' +
                    $('#popup-application-director-personDob\\[day\\]').val()
        }
        });

        e.preventDefault();
        $.magnificPopup.instance.close();
    });
    
    /*
     * Function to remove person from main form and display original search form
     */
    $('BODY').on("click",".resetFieldMapping", function(e){
        if (!$(this).closest('#personSearchForm').length) {
            return;
        }

        olcs.overlayForm.unmapDataToField('#personSearchForm', {
            'firstname' : '',
            'surname' : '',
            'dob-day' : '',
            'dob-month' : '',
            'dob-year' : ''
        });

        e.preventDefault();
    });
    
    $('BODY').on('change keyup', '#personSearchForm :input, #popup-application-director-PersonSearchForm :input, #popup-PersonSearchForm :input,  #popup-SubsidiarySearchForm :input', olcs.enableOnFormChange);
}); // end document.ready()

/*
 * This function differs from validateOnFormChange as it looks for any fields prefilled
 * If any are satisfied, it enables the form. 
 * enableOnFormChange merely looks for *any* values prefilled to enable a form.
 * Used on forms with multiple fields where none are strictly required but it needs
 * something prefilled to enable it.
 */
olcs.enableOnFormChange = function (e) {

    var $formSection = $(this).closest('.form-section'),
        isEmpty = true,
        hasEmptiness = false;

    if (!$formSection.length) {
        $formSection = $(this).closest('form');
    }
    $formSection.find('input, textarea, select').not(':input[type=hidden], :input[type=button], :input[type=submit], :input[type=reset]').each(function () {
    
        var $this = $(this),
            val = $this.val(),
            nam = $this.attr('name');
        
        if ( !($this.is(':radio'))) {
            if (!val ||
                     (!$.isArray(val) && $.trim(val).length < ($this.is('textarea') ? 5 : 1))
                     || ($.isArray(val) && !val.length)) {
                 
                isEmpty = true;
            } else {
                isEmpty = false;
            }
        } else {
            if ( $(':radio[name="'+nam+'"]:checked').length ) {
                isEmpty=false;
            } else {
                isEmpty=true;
            }
        }
        if ( isEmpty ) {
            hasEmptiness=true;
        } else {
            hasEmptiness = false;
            return false;
        }
    });

    // Be careful which buttons this applies to. It should not interfere with validation functions (ie.save buttons) 
    // which require *all* required fields.
    $formSection.find('.enabled-btn, .btn-search').prop('disabled', hasEmptiness).toggleClass('disabled', hasEmptiness);
};
