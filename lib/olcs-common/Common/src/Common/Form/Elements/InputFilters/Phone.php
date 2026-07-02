<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\NotEmpty;

/**
 * Phone Filter
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class Phone extends LaminasElement implements InputProviderInterface
{
    protected $required = false;

    /**
     * Initialise the form
     */
    #[\Override]
    public function init(): void
    {
        $this->setAttribute('pattern', '\d(\+|-|\(|\))*');
        $this->setLabel('contact-number-optional');
        parent::init();
    }

    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => $this->required,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'Contact number is missing',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => \Laminas\Validator\Regex::class,
                    'options' => [
                        'pattern' => '/^[0-9 \(\)\-\+]+$/',
                        'messages' => [
                            'regexNotMatch' => 'The input must contain only digits or spaces',
                        ],
                    ],
                ],
                [
                    'name' => \Laminas\Validator\StringLength::class,
                    'options' => [
                        'min' => 5,
                        'max' => 45,
                    ],
                ],
            ],
        ];
    }
}
