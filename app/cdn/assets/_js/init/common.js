OLCS.ready(function() {

  'use strict';

  OLCS.addAnother();
  OLCS.addMultiple();
  OLCS.tableCheckAll();
  OLCS.tableRows();
  OLCS.dataTooltip();
  OLCS.selectBox();
  OLCS.notices();
  OLCS.radioButton.initialize();
  OLCS.textareaPlaceholder();

  OLCS.modalLink({
    trigger: '.js-modal-ajax'
  });

  OLCS.characterCount({
    selector: 'textarea.js-financial-history'
  });

  OLCS.postcodeSearch({
    container: '.js-postcode-search'
  });

  OLCS.fileUpload({
    multiple: true
  });

  OLCS.formHandler({
    form: '.js-form-ajax-submit',
    onChange: false,
    disable: false,
    success: OLCS.normaliseResponse(function(data) {
      if (data.hasErrors) {
        OLCS.formHelper.render('.js-body', data.body);
        return;
      }
      OLCS.modalForm(data);
    })
  });

  OLCS.disableForm();

  // uses the jquery.details.min.js plugin to enhance <details>
  // element across multiple browsers
  $('details').details();

  OLCS.eventEmitter.on('render', function() {
    // A safe assumption that when the page is rendered
    // this preloader should be hidden
    OLCS.preloader.hide();
    OLCS.radioButton.initialize();
    // Call the plugin to improve select dropdowns
    $('.chosen-select-fixed').chosen({ width: '350px' });
    $('.chosen-select-medium').chosen({ width: '53%' });
    $('.chosen-select-large').chosen({ width: '97%' });
  });

  // Re-render the page in the background when a user is taken to a new tab
  $(window).blur(function() {
      OLCS.eventEmitter.emit('render');
  });

});
