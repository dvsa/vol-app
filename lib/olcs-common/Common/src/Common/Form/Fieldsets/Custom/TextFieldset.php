<?php

/**
 * TextFieldset
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Common\Form\Fieldsets\Custom;

use Laminas\Form\Fieldset;

/**
 * TextFieldset
 *
 * @author Someone <someone@valtech.co.uk>
 */
class TextFieldset extends Fieldset
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->setOptions(['wrapElements', false]);

        $this->add(
            [
                'name' => 'text',
                'options' => [
                    //'label' => 'Name of the brand'
                ],
            ]
        );
    }

    public function getInputSpecification(): array
    {
        return [];
    }
}
