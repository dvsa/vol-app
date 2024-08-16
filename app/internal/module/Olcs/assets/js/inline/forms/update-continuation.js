$(function () {
    "use strict";

    continueLicenceUpdate();
    checklistStatusUpdate();

    $('#fields\\[checklistStatus\\]').change(continueLicenceUpdate);
    $('input[name="fields\\[received\\]"]').click(continueLicenceUpdate);
    $('input[name="fields\\[received\\]"]').click(checklistStatusUpdate);

    function checklistStatusUpdate()
    {
        if ($('input[name="fields\\[received\\]"]:checked').val() === 'Y' &&
        $('#fields\\[checklistStatus\\]').data('always-disabled') !== true) {
            $('#fields\\[checklistStatus\\]').removeAttr('disabled');
        } else {
            $('#fields\\[checklistStatus\\]').attr('disabled', 'disabled');
        }
    }

    function continueLicenceUpdate()
    {
        if ($('#fields\\[checklistStatus\\]').val() === 'con_det_sts_acceptable' &&
        $('input[name="fields\\[received\\]"]:checked').val() === 'Y') {
            $('#continue-licence').removeAttr('disabled');
        } else {
            $('#continue-licence').attr('disabled', 'disabled');
        }
    }
});
