
var OLCS = OLCS || {};


OLCS.validation = (function(document, $, undefined) {

  "use strict";

  var exports = {};

  exports.addEvent = function(){
  	$(document).on("change", "[data-js-validate='required']", function(){
  		exports.removeErrorClasses(this);
  	});
  };

  exports.removeErrorClasses = function(input){
    if(input.checked){
      $(input).removeClass("checkbox--error");
      $(input).next(".checkbox__label").removeClass("checkbox__label--error");
    }
  };

  return exports;


}(document, window.jQuery));
