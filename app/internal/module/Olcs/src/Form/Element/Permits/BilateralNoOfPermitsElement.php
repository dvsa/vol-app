<?php

namespace Olcs\Form\Element\Permits;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputProviderInterface;

class BilateralNoOfPermitsElement extends Text implements InputProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
        ];
    }
}
