/**
 * 1) Defensively declare our project-level namespace
 */
var OLCS = OLCS || {};

OLCS.ajaxError = (function(document, $, undefined) {

    "use strict";
    var exports = {};

    exports.translations = {
        en: "An unknown error has occurred. Please try again later.",
        cy: "Mae gwall wedi digwydd. Rhowch gynnig arall yn nes ymlaen."
    };

    exports.errorHTML = function(language) {
        var translationString = this.translations[language] || this.translations.en;
        if(!this.translations[language]){
            OLCS.logger.warn("Language '" + language + "' not supported, using en as default");
        }
        return "<div class=\"notice-container\"><div class=\"notice--danger\"><a href=\"\" class=\"notice__close\">Close</a>" +
               "<p role=\"alert\">" + translationString + " </p>" + 
               "</div></div>";
    };

    exports.showError = function(){
        var errorNumber = $(".page-header").find(".notice-container").length;
        var language = this.getCookie(document.cookie, "langPref");
        if(errorNumber === 0 && !OLCS.modal.isVisible()){
            $(".page-header").prepend(this.errorHTML(language));
        } else if(OLCS.modal.isVisible()){
            $(".modal").prepend(this.errorHTML(language));
            $(".modal--alert").prepend(this.errorHTML(language));
        }
    };

    exports.removeError = function(){
        $(".notice-container").remove();
    };

    exports.getCookie = function(cookieString, cookieName){
        var cookies = cookieString.split(";");
        var cookieObject = {};
        for(var i = 0; i < cookies.length; i++){
           cookies[i] = cookies[i].trim();
           var thisCookie = cookies[i].split("=");
           cookieObject[thisCookie[0]] = thisCookie[1];
        }
        return cookieObject[cookieName];
    };

    return exports;

}(document, window.jQuery));