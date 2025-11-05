console.log('[VOL-5516] generate-document.js loaded!');

OLCS.ready(function () {
    "use strict";

  // jshint newcap:false

    console.log('[VOL-5516] OLCS.ready executing');

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
        clearWhenEmpty: true
    });

    OLCS.cascadeInput({
        source: form + " #documentSubCategory",
        dest: form + " #documentTemplate",
        url: "/list/document-templates",
        emptyLabel: "Please select",
        clearWhenEmpty: true
    });

    console.log('[VOL-5516] Setting up documentTemplate cascadeInput with custom process');

    OLCS.cascadeInput({
        source: form + " #documentTemplate",
        dest: "fieldset[data-group=details]",
        filter: "fieldset[data-group=bookmarks]",
        // url removed - using custom process function instead
        emptyLabel: "Please select",
        clearWhenEmpty: true,
        append: true,
        disableDestination: false,
        disableSubmit: "form-actions[submit]",
        process: function(templateId, callback) {
            console.log('[VOL-5516] Custom process function called with templateId:', templateId);
            if (templateId === "" || !templateId) {
                return callback([{value: ""}]);
            }

            document.getElementById("form-actions[submit]").disabled = true;

            OLCS.ajax({
                url: "/list-template-bookmarks/" + templateId,
                success: function(response) {
                    console.log('[VOL-5516] Bookmark response received:', response);
                    console.log('[VOL-5516] Response type:', typeof response);
                    console.log('[VOL-5516] Has redirect flag:', response && response.redirectToNewLetterFlow);

                    // Check if we need to redirect to new letter flow
                    if (response && response.redirectToNewLetterFlow) {
                        console.log('[VOL-5516] Redirecting to new letter flow...');

                        // Build redirect URL with entity context
                        var queryParams = new URLSearchParams(window.location.search);
                        var redirectUrl = "/letter/create?template=" + response.templateId;

                        // Add entity context from current URL
                        var entityTypes = ['licence', 'application', 'busReg', 'transportManager', 'irhpApplication', 'irfoOrganisation'];
                        entityTypes.forEach(function(entityType) {
                            if (queryParams.has(entityType)) {
                                redirectUrl += "&" + entityType + "=" + queryParams.get(entityType);
                            }
                        });

                        // Add return URL if present
                        if (queryParams.has('returnUrl')) {
                            redirectUrl += "&returnUrl=" + encodeURIComponent(queryParams.get('returnUrl'));
                        }

                        console.log('[VOL-5516] Redirect URL:', redirectUrl);

                        // Load new letter form into existing modal using OLCS pattern
                        var normaliseResponse = OLCS.normaliseResponse(function(parsedResponse) {
                            console.log('[VOL-5516] Response parsed:', parsedResponse);

                            // Update modal title
                            if (parsedResponse.title) {
                                $('.modal__title').text(parsedResponse.title);
                            }

                            // Update modal body content
                            if (OLCS.modal.isVisible()) {
                                OLCS.modal.updateBody(parsedResponse.body);
                            }

                            console.log('[VOL-5516] Modal content updated');

                            // Re-initialize GOV.UK Frontend components on the new content
                            // Wait a tick for the DOM to settle
                            setTimeout(function() {
                                if (window.GOVUKFrontend) {
                                    console.log('[VOL-5516] Re-initializing GOV.UK Frontend components');
                                    var $modalContent = $('.modal__content');
                                    if ($modalContent.length) {
                                        window.GOVUKFrontend.initAll({
                                            scope: $modalContent[0]
                                        });
                                        console.log('[VOL-5516] GOV.UK Frontend components initialized');
                                    }
                                }
                            }, 100);
                        });

                        OLCS.ajax({
                            url: redirectUrl,
                            success: normaliseResponse,
                            error: function(xhr, status, error) {
                                console.error('[VOL-5516] Failed to load letter form:', status, error);
                            }
                        });

                        return;
                    }

                    console.log('[VOL-5516] Loading normal bookmarks');
                    // Normal bookmark loading
                    callback(response);
                },
                error: function(xhr, status, error) {
                    console.error('[VOL-5516] Bookmark AJAX error:', status, error);
                    console.error('[VOL-5516] XHR:', xhr);
                    document.getElementById("form-actions[submit]").disabled = false;
                }
            });
        }
    });

});
