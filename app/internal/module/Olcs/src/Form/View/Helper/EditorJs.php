<?php

namespace Olcs\Form\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Olcs\Form\Element\EditorJs as EditorJsElement;

/**
 * View helper to render the EditorJS element
 */
class EditorJs extends AbstractHelper
{
    /**
     * Render an EditorJS element from the provided $element
     *
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function __invoke(ElementInterface $element)
    {
        if (!$element instanceof EditorJsElement) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s requires that the element is of type Olcs\Form\Element\EditorJs',
                    __METHOD__
                )
            );
        }

        $id = $element->getAttribute('id') ?: 'editorjs-' . uniqid();
        $name = $element->getName();
        $value = $element->getValue();
        $required = $element->getAttribute('required') ? 'required' : '';
        $classes = $element->getAttribute('class') ?? '';
        
        // Add EditorJS specific classes
        $classes = trim($classes . ' editorjs-element');

        // Escape the value for safe HTML output
        $escapedValue = htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5);

        // Create the container and hidden input
        $markup = sprintf(
            '<div class="editorjs-container" data-element-name="%s" data-required="%s">
                <div id="%s" class="editorjs-editor"></div>
                <input type="hidden" name="%s" value="%s" class="%s" %s />
                <textarea class="editorjs-fallback" name="%s_fallback" style="display:none;" placeholder="Rich text editor not available - please enter your comment here">%s</textarea>
            </div>',
            htmlspecialchars($name, ENT_QUOTES),
            $required,
            htmlspecialchars($id, ENT_QUOTES),
            htmlspecialchars($name, ENT_QUOTES),
            $escapedValue,
            htmlspecialchars($classes, ENT_QUOTES),
            $required,
            htmlspecialchars($name . '_fallback', ENT_QUOTES),
            $escapedValue
        );

        // Add initialization script
        $markup .= sprintf(
            '<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    if (typeof window.initializeEditorJs === "function") {
                        window.initializeEditorJs("%s", "%s", %s);
                    } else {
                        console.warn("EditorJS initialization function not found");
                        // Show fallback textarea
                        var container = document.querySelector(\'[data-element-name="%s"]\');
                        if (container) {
                            container.querySelector(".editorjs-editor").style.display = "none";
                            container.querySelector(".editorjs-fallback").style.display = "block";
                        }
                    }
                });
            </script>',
            htmlspecialchars($id, ENT_QUOTES),
            htmlspecialchars($name, ENT_QUOTES),
            json_encode($escapedValue),
            htmlspecialchars($name, ENT_QUOTES)
        );

        return $markup;
    }
}