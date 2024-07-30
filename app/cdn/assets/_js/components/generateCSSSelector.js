var OLCS = OLCS || {};

OLCS.generateCSSSelector = (function (document, $, undefined) {

    "use strict";

    return function init(node) {

        var path;
        while (node.length) {
            var realNode = node[0],
                element = realNode.localName;
            if (!element) {
                break;
            }
            var nameAttr = realNode.getAttribute("name");
            var parent = node.parent();
            var sameTagSiblings = parent.children(element);

            element = element.toLowerCase();

            if(realNode.id) {
                element += "[id='"+realNode.id+"']";
                path = element + (path ? ">" + path : "");
                node = parent;
                break;
            }

            if(nameAttr && nameAttr !== "undefined") {
                element += "[name='"+nameAttr+"']";
                path = element + (path ? ">" + path : "");
                node = parent;
                break;
            }

            if (sameTagSiblings.length > 1) {
                var allSiblings = parent.children();
                var index = allSiblings.index(realNode) + 1;
                if (index > 0) {
                    element += ":nth-child(" + index + ")";
                }
            }

            path = element + (path ? ">" + path : "");
            node = parent;
        }

        return path;

    };

}(document, window.jQuery));
