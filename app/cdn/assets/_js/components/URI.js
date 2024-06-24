var OLCS = OLCS || {};

OLCS.URI = (function(document, $, URI, undefined) {

    "use strict";

    return function init(string) {

        return new URI(string);

    };

}(document, window.jQuery, window.URI));