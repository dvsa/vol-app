// Toggles the selection elements on the submission page and disables them
jQuery(function () {
     $('#submitActionTypes').change(function(event) {
         if ($(this).val()=='Recommendation') {
             $('#divRecommendActions').removeClass('hide').addClass('isLive required').removeAttr('disabled');
             $('#divRecommendActions select').removeAttr('disabled');
             $('#divDecisionActions').removeClass('required');
             $('#divEmptyActions, #divDecisionActions').addClass('hide').removeClass('isLive');
             $('#divEmptyActions select, #divDecisionActions select').attr('disabled', 'disabled');
         } else if($(this).val()=='Decision') {
             $('#divDecisionActions').removeClass('hide').addClass('isLive required').removeAttr('disabled');
             $('#divDecisionActions select').removeAttr('disabled');
             $('#divRecommendActions').removeClass('required');
             $('#divEmptyActions, #divRecommendActions').addClass('hide').removeClass('isLive');
             $('#divEmptyActions select, #divRecommendActions select').attr('disabled', 'disabled');
         } else {
             $('#divEmptyActions').removeClass('hide').addClass('isLive').removeAttr('disabled');
             $('#divDecisionActions, #divRecommendActions').addClass('hide').removeClass('isLive');
             $('#divDecisionActions select, #divRecommendActions select').attr('disabled', 'disabled');
         }

         setTimeout(function () {
             $('#divRecommendActions, #divDecisionActions, #divEmptyActions').find('select').change();
         }, 1);
    }).change();
    $('#divRecommendActions, #divDecisionActions, #divEmptyActions').find('select').change(function(event) {
        var $recommendActions = $('#divRecommendActions');

        if (!($recommendActions.hasClass('hide')) && $recommendActions.find('select').val() === 'Other') {
            $('#otherDiv').removeClass('hide').addClass('required');
            $('#otherDiv input').removeAttr('disabled');
        } else {
            $('#otherDiv').addClass('hide').removeClass('required');
            $('#otherDiv').attr('disabled', 'disabled');
        }

        setTimeout(function () {
            $('#otherDiv input').change();
        }, 1);
    }).change();
});


