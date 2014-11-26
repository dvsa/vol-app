OLCS.ready(function() {
  var businessType = '#businessType';
  var refreshButton = '#refresh';
  var form = '#operator';
  var typeChanged = '#typeChanged';

  // hide refresh button for js-enabled form
  $(refreshButton).hide();

  // reload form when business type changed
  $(businessType).change(function() {
    $(typeChanged).val(1);
    $(form).submit();
  });

});
