OLCS.ready(function () {
  "use strict";

  // jshint newcap:false

  var form = "form[name=generate-document]";
  var F = OLCS.formHelper;

  if (F("bookmarks").find(":input").length === 0) {
    F("bookmarks").hide();
  }

  OLCS.cascadeInput({
    source: form + " #category",
    dest: form + " #documentSubCategory",
    url: "/list/document-sub-categories-with-docs",
    emptyLabel: "Please select",
    clearWhenEmpty: true,
  });

  OLCS.cascadeInput({
    source: form + " #documentSubCategory",
    dest: form + " #documentTemplate",
    url: "/list/document-templates",
    emptyLabel: "Please select",
    clearWhenEmpty: true,
  });

  OLCS.cascadeInput({
    source: form + " #documentTemplate",
    dest: "fieldset[data-group=details]",
    filter: "fieldset[data-group=bookmarks]",
    emptyLabel: "Please select",
    clearWhenEmpty: true,
    append: true,
    disableDestination: false,
    disableSubmit: "form-actions[submit]",
    process: function (templateId, callback) {
      if (templateId === "" || !templateId) {
        return callback([{ value: "" }]);
      }

      document.getElementById("form-actions[submit]").disabled = true;

      OLCS.ajax({
        url: "/list-template-bookmarks/" + templateId,
        success: function (response) {
          // Check if we need to redirect to new letter flow
          if (response && response.redirectToNewLetterFlow) {
            // Build redirect URL with entity context
            var redirectUrl = "/letter/create?template=" + response.templateId;

            // First, try to get entity context from hidden fields (PHP-provided)
            var entityType = $("#entity-context-type").val();
            var entityId = $("#entity-context-id").val();

            if (entityType && entityId) {
              redirectUrl += "&" + entityType + "=" + entityId;
            } else {
              // Fallback: Check query parameters for backward compatibility
              var queryParams = new URLSearchParams(window.location.search);
              var entityTypes = [
                "licence",
                "application",
                "busReg",
                "transportManager",
                "irhpApplication",
                "irfoOrganisation",
              ];
              entityTypes.forEach(function (entityType) {
                if (queryParams.has(entityType)) {
                  redirectUrl +=
                    "&" + entityType + "=" + queryParams.get(entityType);
                }
              });

              // Add return URL if present in query params
              if (queryParams.has("returnUrl")) {
                redirectUrl +=
                  "&returnUrl=" +
                  encodeURIComponent(queryParams.get("returnUrl"));
              }
            }

            // Load new letter form into existing modal using OLCS pattern
            var normaliseResponse = OLCS.normaliseResponse(
              function (parsedResponse) {
                // Update modal title
                if (parsedResponse.title) {
                  $(".modal__title").text(parsedResponse.title);
                }

                // Update modal body content
                if (OLCS.modal.isVisible()) {
                  OLCS.modal.updateBody(parsedResponse.body);
                }

                // Re-initialize GOV.UK Frontend components on the new content
                setTimeout(function () {
                  if (window.GOVUKFrontend) {
                    var $modalContent = $(".modal__content");
                    if ($modalContent.length) {
                      window.GOVUKFrontend.initAll({
                        scope: $modalContent[0],
                      });
                    }
                  }
                }, 100);
              },
            );

            OLCS.ajax({
              url: redirectUrl,
              success: normaliseResponse,
              error: function (xhr, status, error) {
                console.error("Failed to load letter form:", status, error);
              },
            });

            return;
          }

          // Normal bookmark loading
          callback(response);
        },
        error: function (xhr, status, error) {
          console.error("Bookmark AJAX error:", status, error);
          document.getElementById("form-actions[submit]").disabled = false;
        },
      });
    },
  });
});
