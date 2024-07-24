var OLCS = OLCS || {};

OLCS.singleActivePermitsRequiredTextbox = (function(document, $, undefined) {
    "use strict";

    OLCS.eventEmitter.on("render", function() {
        updateTextBoxes();
    });

    return function init() {
        $("fieldset#ecmt-number-of-permits input[type='text']").bind("propertychange change keyup paste input", function () {
            updateTextBoxes();
        });
    };

    function updateTextBoxes() {
        var $requiredEuro5 = $("#requiredEuro5");
        var $requiredEuro6 = $("#requiredEuro6");

        if (($requiredEuro5.length + $requiredEuro6.length) < 2) {
            return;
        }

        var requiredEuro5Valid = isNormalInteger($requiredEuro5.val());
        var requiredEuro6Valid = isNormalInteger($requiredEuro6.val());

        if (requiredEuro5Valid) {
            enableField($requiredEuro5);
            disableField($requiredEuro6);
        } else if (requiredEuro6Valid) {
            enableField($requiredEuro6);
            disableField($requiredEuro5);
        } else {
            enableField($requiredEuro5);
            enableField($requiredEuro6);
        }
    }

    function isNormalInteger(str) {
        var n = Math.floor(Number(str));
        return n !== Infinity && String(n) === str && n > 0;
    }

    function enableField($field) {
        $field.removeAttr("disabled");
    }

    function disableField($field) {
        $field.attr("disabled", "disabled");
        $field.val("");
    }

}(document, window.jQuery));
