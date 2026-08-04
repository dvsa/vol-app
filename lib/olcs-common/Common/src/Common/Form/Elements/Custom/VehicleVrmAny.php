<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;

/**
 * Vrm field for vehicles from any country
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class VehicleVrmAny extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return (((int[]|string)[]|\Laminas\Filter\StringTrim)[]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, filters: list{\Laminas\Filter\StringTrim}, validators: list{array{name: \Laminas\Validator\StringLength::class, options: array{min: 1, max: 20}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                new \Laminas\Filter\StringTrim(),
            ],
            'validators' => [
                [
                    'name' => \Laminas\Validator\StringLength::class,
                    'options' => [
                        'min' => 1,
                        'max' => 20,
                    ],
                ],
            ],
        ];
    }
}
