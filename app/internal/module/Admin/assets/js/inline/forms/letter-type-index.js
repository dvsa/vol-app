OLCS.ready(function () {
  "use strict";

  // The Builder button opens a different page, which the standard CRUD path cannot do.
  //
  // Table actions submit through OLCS.formHandler, and jQuery follows the controller's redirect
  // transparently — so the XHR comes back as the builder's HTML with a 200 and gets injected into
  // this page. The markup lands with the right letter type id, but its inline script never runs,
  // leaving a dead husk: empty composition list, no preview, nothing wired up.
  //
  // So navigate for real instead. LetterTypeController::builderAction() still exists and still
  // redirects, which covers a non-AJAX submit; this just makes the normal path work.
  $(document).on("click", "#builder", function (e) {
    var selected = $(".table__form input[type='radio']:checked").val();

    // js-require--one disables the button until exactly one row is picked, so this is a guard
    // against the class being removed rather than something a user should hit.
    if (!selected) {
      return;
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    window.location.href =
      "/admin/letter-type-builder/index/" + encodeURIComponent(selected) + "/";
  });
});
