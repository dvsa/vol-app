<?php

namespace Olcs\Form\Element;

use Laminas\Form\Element\Textarea;
use Laminas\InputFilter\InputProviderInterface;
use Olcs\Service\EditorJs\HtmlConverter;

/**
 * EditorJS form element
 *
 * Custom form element that renders an EditorJS editor instead of a standard textarea
 */
class EditorJs extends Textarea implements InputProviderInterface
{
    /**
     * EditorJS version used for JSON structure
     */
    public const EDITORJS_VERSION = '2.28.2';

    /**
     * @var array
     */
    protected $attributes = [
        'type' => 'editorjs'
    ];

    /**
     * Constructor
     */
    public function __construct(private readonly HtmlConverter $htmlConverter, $name = null, iterable $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Get input specification for validation and filtering
     */
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => $this->getAttribute('required') ? true : false,
            'allow_empty' => !$this->getAttribute('required'),
            'filters' => [
                ['name' => 'StringTrim']
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 5,
                        'messages' => [
                            'stringLengthTooShort' => 'Comment must be at least 5 characters long'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Set value for the element
     * Can accept JSON (from EditorJS), HTML, or plain text (from database)
     */
    #[\Override]
    public function setValue($value)
    {
        if ($value && is_string($value)) {
            // Try to parse as EditorJS JSON first
            if ($this->isValidEditorJsJson($value)) {
                return parent::setValue($value);
            }

            // Not valid JSON - convert whatever it is (HTML or plain text)
            $value = $this->convertToEditorJson($value);
        }

        return parent::setValue($value);
    }


    /**
     * Convert content (HTML or plain text) to EditorJS JSON
     */
    private function convertToEditorJson(string $content): string
    {
        try {
            return $this->htmlConverter->convertHtmlToJson($content);
        } catch (\Exception) {
            return $this->fallbackConversion($content);
        }
    }

    /**
     * Check if string is valid EditorJS JSON
     */
    public function isValidEditorJsJson(string $string): bool
    {
        $data = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Check basic EditorJS structure
        if (!isset($data['blocks']) || !is_array($data['blocks'])) {
            return false;
        }

        return true;
    }


    /**
     * Fallback conversion for both HTML and plain text
     */
    private function fallbackConversion(string $content): string
    {
        // Clean up the content (handles both HTML and plain text)
        $content = str_replace('Â', '&nbsp;', $content); // Fix encoding issues
        $textContent = strip_tags($content); // Remove HTML tags if present
        $textContent = html_entity_decode($textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $textContent = trim($textContent);

        if (empty($textContent)) {
            return json_encode(['blocks' => [], 'version' => self::EDITORJS_VERSION]);
        }

        return json_encode([
            'blocks' => [
                [
                    'id' => 'converted-' . uniqid(),
                    'type' => 'paragraph',
                    'data' => ['text' => $textContent]
                ]
            ],
            'version' => self::EDITORJS_VERSION
        ], JSON_UNESCAPED_UNICODE);
    }


    /**
     * Get value for the element
     */
    #[\Override]
    public function getValue()
    {
        return parent::getValue();
    }
}
