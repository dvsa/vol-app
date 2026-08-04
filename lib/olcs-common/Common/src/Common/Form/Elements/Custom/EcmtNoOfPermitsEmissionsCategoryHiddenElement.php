<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Identical;

class EcmtNoOfPermitsEmissionsCategoryHiddenElement extends Hidden implements InputProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return ((array|string)[][]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, validators: list{array{name: Identical::class, options: array{token: mixed}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'validators' => [
                [
                    'name' => Identical::class,
                    'options' => [
                        'token' => $this->options['expectedValue'],
                    ]
                ]
            ]
        ];
    }
}
