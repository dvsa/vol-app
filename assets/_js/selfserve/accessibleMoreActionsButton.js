var OLCS = OLCS || {};

OLCS.accessibleMoreActionsButton = (function (document, $) {
    "use strict";

    var exports = {};

    exports.init = function () {

        $(".more-actions").keydown(function (event) {

            var moreActionsList = $(".more-actions__list");
            var focusableButtons = moreActionsList.find(".more-actions__item:not(:disabled)");
            var currentFocusedButton = focusableButtons.index(document.activeElement);
            var isLastButtonFocused = currentFocusedButton + 1 === focusableButtons.length;
            var isTabBackwards = event.shiftKey;

            switch (event.which) {
                case 40 : //Arrow down
                    if (currentFocusedButton === -1) {
                        focusableButtons.first().focus();
                    } else {
                        focusableButtons.eq(currentFocusedButton + 1).focus();
                    }
                    event.preventDefault();
                    break;
                case 38 : //Arrow up
                    if (currentFocusedButton > 0) {
                        focusableButtons.eq(currentFocusedButton - 1).focus();
                    }

                    if (currentFocusedButton === -1) {
                        focusableButtons.last().focus();
                    }
                    event.preventDefault();
                    break;
                case 9 : //Tab - close more actions list if user tabs out of list
                    if (currentFocusedButton === -1 && isTabBackwards === true ||
                        isLastButtonFocused === true && isTabBackwards === false) {
                        $(".more-actions").removeClass("active");
                        moreActionsList.removeAttr("style");
                    }
                    break;
            }
        });
    };
    return exports;

}(document, window.jQuery));

