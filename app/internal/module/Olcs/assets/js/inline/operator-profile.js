OLCS.ready(function () {
    'use strict';

  // reload form when business type changed
    $(document).on('change', '#businessType', function () {
        var form = $('#operator');
        var button = $('#refresh');

        OLCS.formHelper.pressButton(form, button);
        form.submit();
    });
});
