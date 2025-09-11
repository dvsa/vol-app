/**
 * EditorJS component for OLCS submission comments
 *
 * Initializes EditorJS editors with JSON data from the API
 * Follows OLCS component pattern for modal compatibility
 */

/* global EditorJS, Header, List, Paragraph, Underline */
OLCS.editorjs = (function (document, $, undefined) {
  "use strict";

  // Store editor instances for cleanup
  var editorInstances = {};

  /**
   * Initialize an EditorJS instance for a form element
   * @param {string} editorId - The ID of the editor container
   * @param {string} inputName - The name of the form input
   * @param {string} initialValue - Initial value (JSON string from API)
   */
  function initializeEditorJs(editorId, inputName, initialValue) {
    // Check if EditorJS is available
    if (typeof EditorJS === "undefined") {
      if (typeof OLCS.logger !== "undefined") {
        OLCS.logger("EditorJS library not available");
      }
      return;
    }

    // Get DOM elements
    var editorContainer = document.getElementById(editorId);
    var hiddenInput = document.querySelector("input[name='" + inputName + "']");

    if (!editorContainer || !hiddenInput) {
      if (typeof OLCS.logger !== "undefined") {
        OLCS.logger("EditorJS DOM elements not found: " + editorId + ", " + inputName);
      }
      return;
    }

    // Configure EditorJS tools
    var tools = {};

    // Add Header tool if available
    if (typeof Header !== "undefined") {
      tools.header = {
        class: Header,
        config: {
          placeholder: "Enter a header",
          levels: [1, 2, 3, 4, 5, 6],
          defaultLevel: 3,
        },
      };
    }

    // Add List tool if available
    if (typeof List !== "undefined") {
      tools.list = {
        class: List,
        inlineToolbar: true,
        config: {
          defaultStyle: "unordered",
        },
      };
    }

    // Add Paragraph tool if available (should be default)
    if (typeof Paragraph !== "undefined") {
      tools.paragraph = {
        class: Paragraph,
        inlineToolbar: true,
      };
    }

    // Add Underline tool if available
    if (typeof Underline !== "undefined") {
      tools.underline = Underline;
    }

    // Parse initial data
    var initialData = {
      blocks: [],
      version: "2.28.2",
    };

    if (initialValue && initialValue.trim()) {
      try {
        initialData = JSON.parse(initialValue);
      } catch (e) {
        if (typeof OLCS.logger !== "undefined") {
          OLCS.logger("Failed to parse initial EditorJS data: " + e.message);
        }
        // Continue with empty data rather than failing
      }
    }

    try {
      // Initialize EditorJS
      var editor = new EditorJS({
        holder: editorId,
        tools: tools,
        placeholder: "Enter your submission comment...",
        autofocus: false,
        data: initialData,
        onChange: function () {
          // Save editor content to hidden input on change
          editor
            .save()
            .then(function (outputData) {
              hiddenInput.value = JSON.stringify(outputData);
            })
            .catch(function (error) {
              if (typeof OLCS.logger !== "undefined") {
                OLCS.logger("EditorJS save failed: " + error.message);
              }
            });
        },
      });

      // Wait for editor to be ready
      editor.isReady
        .then(function () {
          // Save initial state to hidden input
          editor.save().then(function (outputData) {
            hiddenInput.value = JSON.stringify(outputData);
          });

          // Enable spellcheck on all contenteditable elements
          var editableElements = editorContainer.querySelectorAll('[contenteditable="true"]');
          editableElements.forEach(function (element) {
            element.setAttribute("spellcheck", "true");
          });

          // Watch for new blocks being added and enable spellcheck on them
          var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
              if (mutation.type === "childList") {
                mutation.addedNodes.forEach(function (node) {
                  if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check if the added node itself is contenteditable
                    if (node.getAttribute("contenteditable") === "true") {
                      node.setAttribute("spellcheck", "true");
                    }
                    // Also check for contenteditable descendants
                    var newEditables = node.querySelectorAll('[contenteditable="true"]');
                    newEditables.forEach(function (element) {
                      element.setAttribute("spellcheck", "true");
                    });
                  }
                });
              }
            });
          });

          // Start observing the editor container for changes
          observer.observe(editorContainer, {
            childList: true,
            subtree: true,
          });

          // Store observer for cleanup
          editorInstances[inputName + "_observer"] = observer;
        })
        .catch(function (error) {
          if (typeof OLCS.logger !== "undefined") {
            OLCS.logger("EditorJS initialization failed: " + error.message);
          }
        });

      // Store editor instance for cleanup
      editorInstances[inputName] = editor;
    } catch (error) {
      if (typeof OLCS.logger !== "undefined") {
        OLCS.logger("EditorJS creation failed: " + error.message);
      }
    }
  }

  return function init() {
    function setup() {
      // Initialize all EditorJS containers on the page
      $(".editorjs-container").each(function () {
        var container = $(this);
        var elementName = container.data("element-name");
        var editor = container.find(".editorjs-editor");

        if (editor.length && elementName) {
          var editorId = editor.attr("id");
          var hiddenInput = container.find("input[type='hidden']");
          var initialValue = hiddenInput.val() || "";

          // Skip if already initialized (prevent duplicate editors)
          if (container.data("editorjs-initialized")) {
            return;
          }

          container.data("editorjs-initialized", true);
          initializeEditorJs(editorId, elementName, initialValue);
        }
      });
    }

    // Initial setup for page load
    setup();

    // Re-setup on render events (for modals and AJAX content)
    OLCS.eventEmitter.on("render", setup);

    // Cleanup when modal closes
    OLCS.eventEmitter.on("hide:modal", function () {
      // Clean up EditorJS instances and observers
      Object.keys(editorInstances).forEach(function (name) {
        try {
          // Clean up MutationObserver if it exists
          if (name.endsWith("_observer")) {
            var observer = editorInstances[name];
            if (observer && typeof observer.disconnect === "function") {
              observer.disconnect();
            }
          } else {
            // Clean up EditorJS instance
            var editor = editorInstances[name];
            if (editor && typeof editor.destroy === "function") {
              editor.destroy();
            }
          }
        } catch (e) {
          if (typeof OLCS.logger !== "undefined") {
            OLCS.logger("Error destroying instance: " + name + " - " + e.message);
          }
        }
      });
      editorInstances = {};

      // Reset initialization flags
      $(".editorjs-container").removeData("editorjs-initialized");
    });
  };
})(document, window.jQuery);
