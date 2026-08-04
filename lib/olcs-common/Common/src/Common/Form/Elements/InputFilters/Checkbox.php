<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\Validator as LaminasValidator;

/**
 * Checkbox element
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Checkbox extends LaminasElement\Checkbox
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Provide default input rules for checkbox element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $options = $this->getOptions();

        if (
            !isset($options['must_be_value'])
            || $options['must_be_value'] === false
            || $options['must_be_value'] === null
        ) {
            return [];
        }

        return [
            'name' => $this->getName(),
            'required' => true,
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => $options['must_be_value'],
                        'messages' => [
                            LaminasValidator\Identical::NOT_SAME =>
                                $this->getOptions()['not_checked_message'] ?? 'common.form.validation.checkbox.not_same',
                        ],
                    ],
                ]
            ]
        ];
    }
}
