var OLCS = OLCS || {};

OLCS.nextFocusableElements = (function (document, $, undefined) {

    "use strict";

    var focusableElements = "a:visible, input:visible, select:visible, textarea:visible, button:visible, body:visible, [tabindex]:not([tabindex^=\"-\"]):visible";

    return function init(element) {

        var focusableElementChoices = $(focusableElements).add(element);
        return focusableElementChoices.slice(focusableElementChoices.index(element) + 1);
    };

}(document, window.jQuery));
