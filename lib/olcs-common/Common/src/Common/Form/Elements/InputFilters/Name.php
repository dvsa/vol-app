<?php

/**
 * Name Filter
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;

/**
 * Name Filter
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Name extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return ((int[]|string)[][]|false|null|string)[]
     *
     * @psalm-return array{name: null|string, required: false, filters: list{array{name: \Laminas\Filter\StringTrim::class}}, validators: list{array{name: \Laminas\Validator\StringLength::class, options: array{min: 2, max: 35}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class],
            ],
            'validators' => [
                ['name' => \Laminas\Validator\StringLength::class, 'options' => ['min' => 2, 'max' => 35]]
            ]
        ];
    }
}
