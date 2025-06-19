<?php

namespace Olcs\Form\Element;

use Laminas\Form\Element\Textarea;
use Laminas\InputFilter\InputProviderInterface;

/**
 * EditorJS form element
 * 
 * Custom form element that renders an EditorJS editor instead of a standard textarea
 */
class EditorJs extends Textarea implements InputProviderInterface
{
    /**
     * @var array
     */
    protected $attributes = [
        'type' => 'editorjs'
    ];

    /**
     * Get input specification for validation and filtering
     */
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => $this->getAttribute('required') ? true : false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => \Olcs\Filter\EditorJsFilter::class]
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
     * Can accept both JSON (from EditorJS) and HTML (from database)
     */
    public function setValue($value)
    {
        // Store the raw value - conversion will be handled in the view helper
        return parent::setValue($value);
    }

    /**
     * Get value for the element
     */
    public function getValue()
    {
        return parent::getValue();
    }
}