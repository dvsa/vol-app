<?php

namespace Olcs\Form\Element\Permits;

use Zend\Filter\StringTrim;
use Zend\Form\Element\Text;
use Zend\InputFilter\InputProviderInterface;

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
