<?php

namespace Common\Service\Qa;

use Laminas\Form\Element\Text as LaminasText;
use Laminas\InputFilter\InputProviderInterface;

class Text extends LaminasText implements InputProviderInterface
{
    protected $attributes = [
        'id' => 'qaText',
    ];

    /**
     * {@inheritdoc}
     *
     * @return (false|null|string|string[][])[]
     *
     * @psalm-return array{id: 'qaText', name: null|string, required: false, filters: list{array{name: 'StringTrim'}}, validators: array<never, never>}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
         return [
             'id' => 'qaText',
             'name' => $this->getName(),
             'required' => false,
             'filters' => [
                 ['name' => 'StringTrim'],
             ],
             'validators' => []
         ];
    }
}
