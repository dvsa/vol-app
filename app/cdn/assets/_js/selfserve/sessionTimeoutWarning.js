var OLCS = OLCS || {};

OLCS.sessionTimeoutWarning = (function (document) {
    "use strict";
    var sessionTimeoutButtonStaySignedIn;
    var sessionTimeoutModal;
    var sessionTimeoutTimeout;
    var sessionTimeoutWarningTimeout;
    var actionInProgress = false;
    var metaTags = {
        TimeoutRedirectUrl: "timeout-redirect-url",
        SessionWarningTimeout: "session-warning-timeout",
        SessionRedirectTimeout: "session-redirect-timeout",
    };
    var loggingNameSpace = "sessionTimeoutWarning";

    /**
     * Show the session timing out warning modal
     */
    function showSessionTimeout() {
        OLCS.logger.debug("Showing Session Timeout Modal", loggingNameSpace);
        sessionTimeoutModal.classList.remove("govuk-visually-hidden");
    }

    /**
     * Hide the session timing out warning modal
     */
    function hideSessionTimeout() {
        OLCS.logger.debug("Hiding Session Timeout Modal", loggingNameSpace);
        sessionTimeoutModal.classList.add("govuk-visually-hidden");
    }

    /**
     * Redirect the user to the session timed out page
     */
    function redirectToSessionTimeout() {
        window.location.replace(getMetaTagContent(metaTags.TimeoutRedirectUrl));
    }

    /**
     * Lock the stay signed button
     */
    function lockStaySignedInButton() {
        actionInProgress = true;
        sessionTimeoutButtonStaySignedIn.disabled = true;
    }

    /**
     * Unlock the stay signed in button
     */
    function unlockStaySignedInButton() {
        actionInProgress = false;
        sessionTimeoutButtonStaySignedIn.disabled = false;
    }

    /**
     * Get the value from meta data tag
     *
     * @param metaName {string}
     * @returns {string}
     */
    function getMetaTagContent(metaName) {
        var meta = document.querySelector("meta[name=\"" + metaName + "\"");

        if (meta === null) {
            OLCS.logger.warn("Unable to find meta tag for: " + metaName, loggingNameSpace);
            return "";
        }

        return meta.getAttribute("content");
    }

    /**
     * Initiate the session timeout warning
     */
    function startSessionTimeoutTimeout() {
        hideSessionTimeout();
        var warnTimeout = parseInt(getMetaTagContent(metaTags.SessionWarningTimeout)) * 1000;
        var redirectTimeout = parseInt(getMetaTagContent(metaTags.SessionRedirectTimeout)) * 1000;
        clearTimeout(sessionTimeoutWarningTimeout);
        clearTimeout(sessionTimeoutTimeout);
        sessionTimeoutWarningTimeout = setTimeout(showSessionTimeout, warnTimeout);
        sessionTimeoutTimeout = setTimeout(redirectToSessionTimeout, redirectTimeout);
        unlockStaySignedInButton();
        OLCS.logger.debug("Resetting timeouts - Warning Timeout: " + warnTimeout + " Redirect Timeout: " + redirectTimeout, loggingNameSpace);
    }

    /**
     * Validates the metatags required are correct
     *
     * @returns {boolean}
     */
    function validateMetaTags() {
        for (var tag in metaTags) {
            if (getMetaTagContent(metaTags[tag]) === "") {
                return false;
            }
        }
        return true;
    }

    /**
     * Send a request to refresh the users login time
     */
    function sendLoginRefresh() {
        if (actionInProgress === true) {
            return;
        }

        OLCS.logger.debug("User wants to remain signed in", loggingNameSpace);
        lockStaySignedInButton();

        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function() {
            if (xmlHttp.readyState === xmlHttp.DONE) {
                if (xmlHttp.status > 199 && xmlHttp.status < 300) {
                    OLCS.logger.debug("User session refreshed", loggingNameSpace);
                    startSessionTimeoutTimeout();
                } else {
                    OLCS.logger.warn("Unable to refresh user session: " +xmlHttp.readyState + ", " + xmlHttp.status + ", " +  xmlHttp.responseText, loggingNameSpace);
                    redirectToSessionTimeout();
                }
            }
        };
        xmlHttp.open("GET", "/", true); // true for asynchronous
        xmlHttp.send(null);
    }

    /**
     * On DOMContentLoaded initiate session timeout
     */
    document.addEventListener("DOMContentLoaded", function() {

        if (!validateMetaTags()) {
            OLCS.logger.warn("meta-tags not valid. Aborting", loggingNameSpace);
            return;
        }

        sessionTimeoutModal = document.getElementById("sessionTimeoutModal");
        sessionTimeoutButtonStaySignedIn = document.getElementById("sessionTimeoutButtonStaySignedIn");

        if (sessionTimeoutButtonStaySignedIn === null || sessionTimeoutModal === null) {
            OLCS.logger.warn("Required markup missing. Aborting.", loggingNameSpace);
            return;
        }

        sessionTimeoutButtonStaySignedIn.addEventListener("click", sendLoginRefresh);
        startSessionTimeoutTimeout();
    });

}(document));
