var OLCS = OLCS || {};
var Modernizr = Modernizr || {};

OLCS.radioButton = (function(document, $, undefined) {

  'use strict';

  var exports = {};

  exports.initialize = function() {
    $(document).on('change', '[data-show-element]', function(){
      exports.showHide();
    });
    exports.checkBrowserSupport();
    exports.showHide();
  };

  exports.checkBrowserSupport = function(){
    if (!Modernizr.checked) {
      $('.checkbox__hidden-content').hide();
      $('.radio-button-content').hide();
    } 
  };

  exports.showHide = function(){
    var elements = $('[data-show-element]');

    elements.each(function(){
      var target = this.getAttribute('data-show-element'),
          show = $(this).is(':checked');
      $(target).toggle(show); 
    });
  };

  return exports;

}(document, window.jQuery));
