<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class NoOfPermitsMoroccoFieldsetPopulator implements FieldsetPopulatorInterface
{
    #[\Override]
    public function populate(mixed $form, Fieldset $fieldset, array $options): void
    {
        $fieldset->add(
            [
                'type' => NoOfPermitsElement::class,
                'name' => 'qaElement',
                'options' => [
                    'label' => $options['label'],
                ],
                'attributes' => [
                    'value' => $options['value']
                ]
            ]
        );
    }
}
