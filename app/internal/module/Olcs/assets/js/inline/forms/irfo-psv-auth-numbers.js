$(function() {
  "use strict";

  /**
   * @FIXME
   *
   * 1) Poorly indented
   * 2) Various linting errors
   * 3) Does too much (move logic to a component)
   * 4) Almost entirely in numerous other places
   */

  var targetFieldset = $('fieldset[data-group="fields[irfoPsvAuthNumbers]"]');
  var numberOfFields = $('fieldset', targetFieldset).length;
  var addAnotherButton  = $('<p class="hint"><a href="#">Add another</a></p>');

  var createAddAnother = function() {
    $('div', targetFieldset).last().append(addAnotherButton);
    addAnotherButton.on('click', addAnother);
  };

  var addAnother = function () {
    var html = ('' +
      '<div class="field">' +
      '<input name="fields[irfoPsvAuthNumbers][__id__][name]" class="" id="name" value="" type="text">' +
      '</div>' +
      '').replace('__id__', numberOfFields);

    numberOfFields++;
    targetFieldset.append(html);
    addAnotherButton.remove();
    createAddAnother();
    return false;
  };

  $('fieldset', targetFieldset).each(function (idx, element) {
    var markup = $(this).html();
    $(this).remove();
    targetFieldset.append(markup);
  });

  createAddAnother();
});
