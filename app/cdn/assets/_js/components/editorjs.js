/**
 * EditorJS component for OLCS submission comments
 *
 * Initializes EditorJS editors with JSON data from the API
 * Follows OLCS component pattern for modal compatibility
 */

OLCS.editorjs = (function (document, $, undefined) {
  "use strict";

  /**
   * Initialize an EditorJS instance for a form element
   * @param {string} editorId - The ID of the editor container
   * @param {string} inputName - The name of the form input
   * @param {string} initialValue - Initial value (JSON string from API)
   */
  window.initializeEditorJs = function (editorId, inputName, initialValue) {
    // Check if EditorJS is available
    if (typeof EditorJS === "undefined") {
      console.error("EditorJS not available - showing fallback textarea");
      showFallbackTextarea(inputName);
      return;
    }

    // Get DOM elements
    const editorContainer = document.getElementById(editorId);
    const hiddenInput = document.querySelector('input[name="' + inputName + '"]');

    if (!editorContainer || !hiddenInput) {
      console.error("EditorJS DOM elements not found");
      return;
    }

    // Configure EditorJS tools
    const tools = {};

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

    // Parse initial data
    let initialData = {
      blocks: [],
      version: "2.28.2",
    };

    if (initialValue && initialValue.trim()) {
      console.log("EditorJS initializing with value:", initialValue);
      try {
        initialData = JSON.parse(initialValue);
        console.log("EditorJS successfully parsed JSON:", initialData);
      } catch (e) {
        console.error("Failed to parse initial EditorJS data:", e);
        console.log("Raw value that failed to parse:", JSON.stringify(initialValue));
        // If parsing fails, show fallback
        showFallbackTextarea(inputName);
        return;
      }
    }

    try {
      // Initialize EditorJS
      const editor = new EditorJS({
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
              console.error("EditorJS save failed:", error);
            });
        },
      });

      // Wait for editor to be ready
      editor.isReady
        .then(function () {
          console.log("EditorJS ready for:", inputName);

          // Save initial state to hidden input
          editor.save().then(function (outputData) {
            hiddenInput.value = JSON.stringify(outputData);
          });
        })
        .catch(function (error) {
          console.error("EditorJS initialization failed:", error);
          showFallbackTextarea(inputName);
        });

      // Store editor instance for potential later access
      window.editorJsInstances = window.editorJsInstances || {};
      window.editorJsInstances[inputName] = editor;
    } catch (error) {
      console.error("EditorJS creation failed:", error);
      showFallbackTextarea(inputName);
    }
  };

  /**
   * Show fallback textarea when EditorJS fails
   * @param {string} inputName - Form input name
   */
  function showFallbackTextarea(inputName) {
    const container = document.querySelector('[data-element-name="' + inputName + '"]');
    if (container) {
      const editor = container.querySelector(".editorjs-editor");
      const fallback = container.querySelector(".editorjs-fallback");
      const hiddenInput = container.querySelector('input[type="hidden"]');

      if (editor) editor.style.display = "none";
      if (fallback) {
        fallback.style.display = "block";
        fallback.style.width = "100%";
        fallback.style.minHeight = "200px";

        // Copy value from hidden input to textarea
        if (hiddenInput && hiddenInput.value) {
          // If it's JSON, extract text content for display
          try {
            const data = JSON.parse(hiddenInput.value);
            let text = "";
            if (data.blocks) {
              data.blocks.forEach(function (block) {
                if (block.data) {
                  if (block.type === "paragraph" && block.data.text) {
                    text += stripHtml(block.data.text) + "\n\n";
                  } else if (block.type === "header" && block.data.text) {
                    text += stripHtml(block.data.text) + "\n\n";
                  } else if (block.type === "list" && block.data.items) {
                    block.data.items.forEach(function (item) {
                      text += "• " + stripHtml(item) + "\n";
                    });
                    text += "\n";
                  }
                }
              });
            }
            fallback.value = text.trim();
          } catch (e) {
            // Not JSON, use as-is
            fallback.value = hiddenInput.value;
          }
        }

        // Update hidden input when textarea changes
        fallback.addEventListener("input", function () {
          if (hiddenInput) {
            // Convert plain text to basic EditorJS format
            const paragraphs = fallback.value.split("\n\n").filter(function (p) {
              return p.trim();
            });

            const blocks = paragraphs.map(function (text, index) {
              return {
                id: "fallback-block-" + index,
                type: "paragraph",
                data: {
                  text: text.trim(),
                },
              };
            });

            hiddenInput.value = JSON.stringify({
              blocks: blocks,
              version: "2.28.2",
            });
          }
        });
      }
    }
  }

  /**
   * Strip HTML tags from text
   * @param {string} html - HTML string
   * @returns {string} - Plain text
   */
  function stripHtml(html) {
    const tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
  }

  /**
   * Get text content from all EditorJS instances (useful for validation)
   */
  window.getEditorJsTextContent = function () {
    const content = {};

    if (window.editorJsInstances) {
      Object.keys(window.editorJsInstances).forEach(function (name) {
        const editor = window.editorJsInstances[name];
        editor.save().then(function (data) {
          let text = "";
          if (data.blocks) {
            data.blocks.forEach(function (block) {
              if (block.data) {
                if (block.type === "paragraph" && block.data.text) {
                  text += stripHtml(block.data.text) + " ";
                } else if (block.type === "header" && block.data.text) {
                  text += stripHtml(block.data.text) + " ";
                } else if (block.type === "list" && block.data.items) {
                  block.data.items.forEach(function (item) {
                    text += stripHtml(item) + " ";
                  });
                }
              }
            });
          }
          content[name] = text.trim();
        });
      });
    }

    return content;
  };
  return function init() {
    function setup() {
      // Initialize all EditorJS containers on the page
      $(".editorjs-container").each(function () {
        var container = $(this);
        var elementName = container.data("element-name");
        var editor = container.find(".editorjs-editor");

        if (editor.length && elementName) {
          var editorId = editor.attr("id");
          var hiddenInput = container.find('input[type="hidden"]');
          var initialValue = hiddenInput.val() || "";

          // Skip if already initialized (prevent duplicate editors)
          if (container.data("editorjs-initialized")) {
            return;
          }

          container.data("editorjs-initialized", true);
          window.initializeEditorJs(editorId, elementName, initialValue);
        }
      });
    }

    // Initial setup for page load
    setup();

    // Re-setup on render events (for modals and AJAX content)
    OLCS.eventEmitter.on("render", setup);

    // Cleanup when modal closes
    OLCS.eventEmitter.on("hide:modal", function () {
      // Clean up EditorJS instances
      if (window.editorJsInstances) {
        Object.keys(window.editorJsInstances).forEach(function (name) {
          try {
            var editor = window.editorJsInstances[name];
            if (editor && typeof editor.destroy === "function") {
              editor.destroy();
            }
          } catch (e) {
            console.warn("Error destroying EditorJS instance:", name, e);
          }
        });
        window.editorJsInstances = {};
      }

      // Reset initialization flags
      $(".editorjs-container").removeData("editorjs-initialized");
    });
  };
})(document, window.jQuery);
