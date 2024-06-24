var OLCS = OLCS || {};

/**
 * CRUD table handler
 */

OLCS.crudTableHandler = (function(document, $, undefined) {

  "use strict";

  return function init(options) {

    if (!$.isPlainObject(options)) {
      options = {};
    }

    var crudActionSelector = options.selector || [
      ".table__header button:not(.js-disable-crud)",
      ".table__wrapper button[type=submit]",
      ".table__empty button"
    ].join(",");

    var modalBodySelector  = ".modal__content";
    var mainBodySelector   = ".js-body";
    var modalWrapper       = ".modal__wrapper";

    var F = OLCS.formHelper;
    var modalEvent;

    //reload the parent window behind the modal
    function reloadParent() {
      OLCS.ajax({
        url: window.location.href,
        success: OLCS.normaliseResponse(function(response) {
          F.render(mainBodySelector, response.body);
        }),
        preloaderType: "modal"
      });
    }

    $(document).on("click", crudActionSelector, function handleCrudClick(e) {
      e.preventDefault();

      // save the last focused element for later
      OLCS.modal.lastFocus = this;
      OLCS.modal.lastFocusSelector = OLCS.generateCSSSelector($(this));
      OLCS.modal.nextFocusables = OLCS.nextFocusableElements(this);

      var button = $(this);
      var form   = $(this).parents("form");

      // manually handle rendering the modal because we need to intercept
      // any errors triggered when the user clicks a CRUD button
      function handleCrudAction(response) {
        // if we find any errors or flash warnings, completely
        // re-render our main body
        if (response.hasErrors || response.hasWarnings) {
          return F.render(mainBodySelector, response.body);
        }

        // otherwise clear any we might have had previously
        F.clearErrors();

        var options = {
          success: OLCS.normaliseResponse({
            // We trap redirects and handle them ourselves, because if the
            // redirect URL is the current page we want to ignore it and
            // just hide the modal instead
            followRedirects: false,
            callback: handleCrudResponse
          }),
          error: function(){
            window.alert("fail");
          }
        };

        OLCS.modalForm($.extend(response, options));
      }

      // Manually handle any responses or redirects
      // inside the modal
      function handleCrudResponse(response) {

        // if the original response was a redirect then be sure to respect
        // that by closing the modal
        if (response.status === 302) {

          // We check for the data-hard-refresh attribute, as in some cases we want to fully reload the page
          // to ensure that markers etc are reloaded
          if (!form.attr("data-hard-refresh") && OLCS.url.isCurrentPage(response.location)) {
            return OLCS.modal.hide();
          }
          return OLCS.url.load(response.location);
        }

        if (response.status === 200) {

          // always render; could be a clean form (if we clicked add another),
          // could be riddled with errors
          F.render(modalBodySelector, response.body);

          $(modalWrapper).scrollTop(0);
        }
      }

      // make sure any backend code sniffing button presses isn't disappointed
      F.pressButton(form, button);

      // hook everything up and submit the form
      OLCS.submitForm({
        form: form,
        success: OLCS.normaliseResponse(handleCrudAction)
      });
    });

    /**
     * Reload the parent page every time a modal is hidden. By and large this
     * works well and means our parent page is always fresh (CSRF, version numbers etc).
     * The only downside is that a user can close the modal without any interaction and
     * still trigger a spinner and a refresh which might confuse them. However, it's
     * still necessary because they've actually POSTed the original form and possibly
     * updated the version, so if they try and view another modal they'll get a version conflict
     */
    modalEvent = OLCS.eventEmitter.on("hide:modal", reloadParent);
  };

}(document, window.jQuery));
