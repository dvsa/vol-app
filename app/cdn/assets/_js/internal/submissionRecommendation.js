var OLCS = OLCS || {};


OLCS.submissionRecommendation = (function(document, $, undefined) {
  "use strict";

  var exports = {};

  exports.addChangeEvent = function(options) {
    $(options.source).on("change", function() {
      exports.removeRevokations(options);
    });
  };



  exports.removeRevokations = function(options){
    if(!this.originalHTML){
      this.originalHTML = $(options.dest).clone();  
    }
    var subset   = this.originalHTML.clone();
    subset.find("[data-in-office-revokation=N]").remove();

    var contents;
    var recommendations = $(options.source)[0].value;

    if (recommendations && recommendations.indexOf(options.target) >= 0) {
      contents = subset.html();
    } else {
      contents = this.originalHTML.html();
    }

    $(options.dest).html(contents).trigger("chosen:updated");
  };

  return exports;

}(document, window.jQuery));
