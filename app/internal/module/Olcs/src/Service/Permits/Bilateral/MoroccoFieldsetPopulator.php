<?php

namespace Olcs\Service\Permits\Bilateral;

use Common\Service\Qa\Custom\Bilateral\NoOfPermitsElement;
use Laminas\Form\Fieldset;

/**
 * Morocco fieldset populator
 */
class MoroccoFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function populate(Fieldset $fieldset, array $fields)
    {
        $fieldset->add(
            [
                'type' => NoOfPermitsElement::class,
                'name' => 'permitsRequired',
                'options' => [
                    'label' => $fields['caption'],
                ],
                'attributes' => [
                    'value' => $fields['value']
                ]
            ]
        );
    }
}
