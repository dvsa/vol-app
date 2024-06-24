var OLCS = OLCS || {};

/**
 * Modal response
 *
 * Thin wrapper around normalise response; if the response we get back
 * looks okay, we pop open a modal. If not, we try and render any errors
 * in the parent page if a body selector was provided
 */

OLCS.modalResponse = (function(document, $, undefined) {

  'use strict';

  return function modaliseResponse(bodySelector) {

    return OLCS.normaliseResponse(function(data) {

      if (bodySelector && data.hasErrors) {
        OLCS.formHelper.render(bodySelector, data.body);
        return;
      }

      var formData = data;

      // Make an ajax request to determine if the user is still 
      // logged in, if so bind a form handler on the modal's content, 
      // otherwise redirect user to homepage
      OLCS.ajax({
        url: '/auth/validate',
        cache: false,
        success: function(data) {
          // If the returned JSON is empty, the user is not logged in
          var unauthorised = JSON.stringify(data) === '[]';
          // If the user is authorised (not unauthorised), continue as normal
          if (!unauthorised) {
            OLCS.modalForm(formData);
          }
          // otherwise redirect the user to the homepage
          else {
            document.location.href='/';
          }
        } 
      });

    });

  };

}(document, $));
