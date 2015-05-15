$(function() {
  "use strict";

  checklistStatusChange();
  $('#fields\\[checklistStatus\\]').change(checklistStatusChange);

  function checklistStatusChange() {
    if ($('#fields\\[checklistStatus\\]').val() == 'con_det_sts_acceptable') {
      $('#continue-licence').removeAttr('disabled');
    } else {
      $('#continue-licence').attr('disabled', 'disabled');
    }
  }
});
