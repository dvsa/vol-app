OLCS.ready(function() {
  'use strict';

  OLCS.conditionalButton({
    form: '.table__form',
    selector: '.js-require--one:not(.js-force-disable)',
    predicate: function(length, callback) {
      callback(length === 1);
    }
  });

  OLCS.conditionalButton({
    form: '.table__form',
    selector: '.js-require--multiple:not(.js-force-disable)',
    predicate: function(length, callback) {
      callback(length >= 1);
    }
  });

  OLCS.toggleElement({
    triggerSelector: '.admin__title',
    targetSelector: '.admin__menu'
  });

  OLCS.wysiwyg();

  // Small script to append date to internal search result links
  // to ensure :visited styles reset each day
  $('form[action*="search"] .table__wrapper').find('a').each(function() {
    var date = new Date().toJSON().slice(0,10);
    $(this).attr('href', function() {
        return this.href + '?' + date;
    });
  });

  var path = location.pathname;
  if (path.indexOf('/admin/publication/pending') > -1) {

    var options = {
      dataElSelector: '[data-heading="Document status"]',
      dataElValToCheck1: 'New',
      dataElValToCheck2: 'Generated',
      buttonSelector1: '#publish',
      buttonSelector2: '#generate',
      stateAttr: 'disabled',
      stateAttrVal: true
    };

    $(document).on('change', function() {

      OLCS.conditionallyDisableButtons(options).onChange();

    });

  }

});
