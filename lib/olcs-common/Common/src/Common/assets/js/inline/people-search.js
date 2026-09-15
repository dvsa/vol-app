OLCS.ready(function() {
  "use strict";

  let licenceStatus = $('#search-filter select#filter\\[licStatus\\|appStatusId\\]');
  let firstRun = true;
  $('#search-filter select#filter\\[foundType\\]').on('change', function() {
    let showTransportManagerStatus = $(this).val() === 'TM';
    licenceStatus.parents('.field').css('display', showTransportManagerStatus ? 'block' : 'none');

    if (!firstRun) {
      licenceStatus.val('');
    }
    firstRun = false;
  }).trigger('change');
});
