<?php

/**
 * Time
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;

/**
 * Time
 *
 * @author Someone <someone@valtech.co.uk>
 */
class Time extends LaminasElement\Time
{
    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInput()}.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
        ];
    }
}
