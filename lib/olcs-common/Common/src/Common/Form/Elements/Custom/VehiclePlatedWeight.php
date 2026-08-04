<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class VehiclePlatedWeight extends LaminasElement implements InputProviderInterface
{
    /**
     * Get Input Specification
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return array_filter(
            [
                'type' => \Laminas\InputFilter\Input::class,
                'name' => $this->getName(),
                'required' => $this->getOption('required'),
                'allow_empty' => $this->getOption('allow_empty'),
                'validators' => [
                    [
                        'name' => \Laminas\Validator\Digits::class,
                        'options' => [],
                    ],
                    [
                        'name' => \Laminas\Validator\Between::class,
                        'options' => [
                            'min' => 0,
                            'max' => 999999,
                        ],
                    ],
                ],
            ]
        );
    }
}
