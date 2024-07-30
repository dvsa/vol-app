var OLCS = OLCS || {};

/**
 * Normalise response
 *
 * the return value is a simple function which takes
 * a callback; this callback will be invoked with the
 * normalised response...
 */

OLCS.normaliseResponse = (function(window, $, undefined) {

  "use strict";

  return function init(options) {

    if (!$.isPlainObject(options)) {
      options = {
        callback: options
      };
    }

    if (typeof options.callback !== "function") {
      throw new Error("OLCS.normaliseResponse requires at least a callback argument");
    }

    var preloader      = options.preloader || false;
    var titleSelector  = options.title     || ".js-title";
    var bodySelector   = options.body      || ".js-body,.js-body__main";
    var scriptSelector = options.script    || ".js-script";
    var rootSelector   = options.root      || ".js-response";
    var keepModalOpen  = options.keepModalOpen || false;

    var callback = options.callback;
    var followRedirects = options.followRedirects !== undefined ? options.followRedirects : true;

    // preloader value needs to be a type {string}, not a truthy
    if (preloader === true) { preloader = "modal"; }

    function findTitle(body) {
      var title;
      var text = "";

      // first up try the sensible option; just grab the first heading which is an
      // immediate descendent of the content block
      title = $(body).find(".js-content").children("h1,h2,h3,h4,h5,h6").first();

      if (title.length === 0) {
        // okay, no luck. Internal templates often appear within a header container - try that
        title = $(body).find(".content__header");
      }

      // hopefully we've got a title. If so we need to explicitly remove it from the body block
      // otherwise it'll be duplicated
      if (title.length) {
        text = title.text();
        $(title).remove();
      }

      return text;
    }


    function parse(responseString) {
      var title  = "";
      var body   = "";
      var script = "";
      var response;

      // this can throw if the response we get back can't be parsed (i.e. var dumped data during debug)
      try {
        title  = $(responseString).find(titleSelector);
        body   = $(responseString).find(bodySelector);
        script = $(responseString).find(scriptSelector);
      } catch (e) {
        OLCS.logger.debug("Caught error parsing response", "normaliseResponse");
      }

      // We set up some sensible defaults here so that if we can't parse anything else
      // of use we at least turn a usable response
      response = {
        status: 200,
        title: "",
        body: responseString,
        //errors: [],
        hasErrors: false,
        hasWarnings: false,
        hasValidationErrors: false
      };

      if (title.length) {
        OLCS.logger.debug("found response title matching " + titleSelector, "normaliseResponse");
        response.title = title.last().text();
        if ($.trim(response.title) === "") {
          OLCS.logger.debug("title selector contents is empty, falling back to searching body");
          response.title = findTitle(body);
        }
      } else {
        OLCS.logger.debug("no matching response title for " + titleSelector + ", searching headings...", "normaliseResponse");
        response.title = findTitle(body);
      }

      if (body.length) {
        var deepest = null;
        var depth = -1;

        // we sometimes find multiple .js-body tags and sometimes even have a
        // .js-body within a .js-body__main
        $.each(body, function(_, v) {
          var dist = $(v).parentsUntil(rootSelector).length;
          if (dist > depth) {
            depth = dist;
            deepest = $(v);
          }
        });

        OLCS.logger.debug("got response body matching ." + deepest.attr("class") + " at depth " + depth, "normaliseResponse");

        // js-script will often live within js-body; we want to lift it
        // out as it'll be appended afterwards
        deepest.find(scriptSelector).remove();
        response.body = deepest.html();

      } else {
        OLCS.logger.debug("no matching response body for " + bodySelector, "normaliseResponse");
      }

      // ensure scripts are injected too
      if (script.length) {
        OLCS.logger.debug("found inline script matching " + scriptSelector, "normaliseResponse");
        response.body += script.html();
      } else {
        OLCS.logger.debug("no matching inline script for " + scriptSelector, "normaliseResponse");
      }

      //check for validation errors
      response.hasValidationErrors = (function(){
        return $(responseString).find(".validation-summary").length > 0;
      })();

      return response;
    }

    // ... the inner function will be invoked, we suppose, by an AJAX request or similar
    return function onResponse(response) {

      // expose the redirect location to the DOM
      // so that inline JS can make use of it
      $("body").data("target",response.location);

      if (typeof response === "string") {
        OLCS.logger.debug("converting response string to object", "normaliseResponse");
        response = parse(response);
        if(response.hasValidationErrors){
          $(".modal__wrapper").scrollTop(0);
        }
      }

      // we won't invoke the callback if the status is a straightforward redirect
      if (response.status === 302 && followRedirects) {

        // Fake the modal.hide functionality to avoid reloading the parent
        if(keepModalOpen){
          $(".modal__wrapper, .overlay").remove();
        }

        // We may or may not want to show a preloader when calling this component
        if (preloader) {
          OLCS.preloader.show(preloader);
        }

        // If the parent form action has a query string we want
        // to preserve it to make sure the user doesn't lose their state
        var url = response.location;
        var queryString;

        // if our response location doesn't contain a query string
        if (response.location.indexOf("?") === -1) {
          try {
            // try to find one in the .table__form's action and append this
            // to the reponse location
            queryString = $(".table__form").attr("action").match(/\?(.*)/);
            url = response.location + queryString[0];
          } catch(e) {
            OLCS.logger.debug("couldn't find a query string on the .table__form element");
          }
        }

        OLCS.logger.debug(
          "caught 302 redirect; followRedirects=true; redirecting to " + url,
          "normaliseResponse"
        );

        return OLCS.url.load(url);
      }

      // otherwise start to inspect the response for any things of interest
      if (response.body) {
        response.hasErrors = OLCS.formHelper.containsErrors(response.body);
        response.hasWarnings = OLCS.formHelper.containsWarnings(response.body);

        if (response.hasErrors) {
          OLCS.logger.debug(
            "normalised response contains errors",
            "normaliseResponse"
          );
        }

        if (response.hasWarnings) {
          OLCS.logger.debug(
            "normalised response contains warnings",
            "normaliseResponse"
          );
        }
      }

      // by the time we get here we've got a nice consistent response, whatever
      // we got back from the backend
      return callback(response);
    };
  };

}(window, window.jQuery));
