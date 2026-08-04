<?php

namespace Common\Service\Qa\FieldsetModifier;

use Laminas\Form\Fieldset;

class RoadworthinessMakeAndModelFieldsetModifier implements FieldsetModifierInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function shouldModify(Fieldset $fieldset)
    {
        $eligibleFieldsetNames = [
            Fieldsets::ROADWORTHINESS_VEHICLE_MAKE_AND_MODEL,
            Fieldsets::ROADWORTHINESS_TRAILER_MAKE_AND_MODEL,
        ];

        return in_array(
            $fieldset->getName(),
            $eligibleFieldsetNames
        );
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function modify(Fieldset $fieldset): void
    {
        $qaElement = $fieldset->get('qaElement');

        $qaElement->setAttribute(
            'class',
            'govuk-input govuk-input--width-50'
        );
    }
}
