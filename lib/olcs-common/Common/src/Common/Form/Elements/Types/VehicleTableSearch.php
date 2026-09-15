<?php

declare(strict_types=1);

namespace Common\Form\Elements\Types;

use Common\Form\Elements\Custom\VehicleVrm;
use Laminas\Form\Element\Button;

class VehicleTableSearch extends AbstractInputSearch
{
    /**
     * @return void
     */
    #[\Override]
    protected function addHint()
    {
        $this->add(
            [
                'type' => PlainText::class,
                'name' => static::ELEMENT_HINT_NAME,
                'attributes' => [
                    'data-container-class' => 'hint',
                ],
                'options' => [
                    'value' => ''
                ]
            ]
        );
    }

    /**
     * @return void
     */
    #[\Override]
    protected function addInput()
    {
        $this->add(
            [
                'type' => VehicleVrm::class,
                'name' => static::ELEMENT_INPUT_NAME,
                'attributes' => [
                    'data-container-class' => 'inline',
                ],
                'options' => [
                    'validateVrm' => false
                ]
            ]
        );
    }

    /**
     * @return void
     */
    #[\Override]
    protected function addSubmit()
    {
        $this->add(
            [
                'type' => Button::class,
                'name' => static::ELEMENT_SUBMIT_NAME,
                'options' => [
                    'label' => 'licence.vehicle.table.search.button',
                ],
                'attributes' => [
                    'class' => 'govuk-button',
                    'data-container-class' => 'inline',
                    'type' => 'submit',
                ],
            ]
        );
    }
}
