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

  // Hidden input per editor, so the value that gets POSTed can be brought up to date on
  // demand rather than only when EditorJS gets round to announcing a change.
  var hiddenInputs = {};

  /**
   * Bring every editor's hidden input up to date with what is currently on screen.
   *
   * The onChange handler below is the normal path, but it is debounced by EditorJS and
   * completes asynchronously. Anything that reads the hidden input in direct response to
   * a user action — a save button, a form submit — can therefore read the previous
   * content, send it, and report success. Callers await this first to close that gap.
   *
   * Always resolves: a failure to serialise one editor must not strand a save handler
   * waiting on a promise that never settles, leaving a disabled button and no message.
   *
   * @returns {Promise} resolved once every editor's hidden input has been refreshed
   */
  function flushEditors() {
    var pending = Object.keys(editorInstances)
      .filter(function (name) {
        return !name.endsWith("_observer") && hiddenInputs[name];
      })
      .map(function (name) {
        var editor = editorInstances[name];

        if (!editor || typeof editor.save !== "function") {
          return null;
        }

        return editor
          .save()
          .then(function (outputData) {
            var next = JSON.stringify(outputData);

            // Only write on a real change. Callers flush speculatively — before a
            // click that may not be a save at all — and letter-edit.js watches this
            // input to decide whether there are unsaved edits. Assigning an identical
            // value would mark clean content dirty and clear the "Saved" indicator.
            if (hiddenInputs[name].value !== next) {
              hiddenInputs[name].value = next;
            }
          })
          .catch(function (error) {
            if (typeof OLCS.logger !== "undefined") {
              OLCS.logger("EditorJS flush failed for " + name + ": " + error.message);
            }
          });
      })
      .filter(Boolean);

    return Promise.all(pending);
  }

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

    // Add Header tool if available.
    // VOL-7305: inlineToolbar enables Bold / Italic / Link from EditorJS core so
    // admins can format chrome headings the same way as paragraphs.
    if (typeof Header !== "undefined") {
      tools.header = {
        class: Header,
        inlineToolbar: true,
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
          var editableElements = editorContainer.querySelectorAll("[contenteditable=true]");
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
                    var newEditables = node.querySelectorAll("[contenteditable=true]");
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

      // Store editor instance for cleanup, and its input so it can be flushed on demand
      editorInstances[inputName] = editor;
      hiddenInputs[inputName] = hiddenInput;
    } catch (error) {
      if (typeof OLCS.logger !== "undefined") {
        OLCS.logger("EditorJS creation failed: " + error.message);
      }
    }
  }

  // Exposed so save handlers elsewhere can await an up-to-date hidden input before they
  // read it. Assigned on the OLCS namespace rather than returned, because this module's
  // export is already its init function.
  OLCS.editorjsFlush = flushEditors;

  return function init() {
    /**
     * Keep the hidden input close behind the keystrokes.
     *
     * EditorJS announces changes on its own debounce, which leaves a window where what
     * is on screen and what would be POSTed disagree. Plain form posts — the admin
     * modals, which carry no JavaScript of their own — read the input directly and have
     * nothing to await, so the window is closed from this end instead: every keystroke
     * schedules a flush, and each flush is coalesced so a fast typist queues one save
     * rather than one per character.
     *
     * Code that can await something should still call OLCS.editorjsFlush() before
     * reading the input; that is a guarantee, whereas this is a very short window.
     */
    function trackInput() {
      var pending = false;

      $(document)
        .off("input.editorjs")
        .on("input.editorjs", ".editorjs-container", function () {
          if (pending) {
            return;
          }

          pending = true;

          // A frame is long enough to coalesce a burst of typing and short enough that
          // the input is current well before a click on a save button lands.
          window.requestAnimationFrame(function () {
            pending = false;
            flushEditors();
          });
        });
    }

    /**
     * Flush before an activation that is about to read the input.
     *
     * A pointer press precedes its click, and a key press precedes the submit it
     * triggers, so flushing here gives the save a fully current value even if the
     * keystroke that preceded it has not yet been through the frame above.
     */
    function flushBeforeActivation() {
      $(document)
        .off("pointerdown.editorjs keydown.editorjs")
        .on("pointerdown.editorjs", "button, input[type=submit], a", function () {
          flushEditors();
        })
        .on("keydown.editorjs", "button, input[type=submit]", function (event) {
          if (event.key === "Enter" || event.key === " ") {
            flushEditors();
          }
        });
    }

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
    trackInput();
    flushBeforeActivation();

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
      hiddenInputs = {};

      // Reset initialization flags
      $(".editorjs-container").removeData("editorjs-initialized");
    });
  };
})(document, window.jQuery);
