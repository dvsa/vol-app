<?php

namespace Common\Service\Qa\FieldsetModifier;

use Laminas\Form\Fieldset;

class FieldsetModifier
{
    /** @var array */
    private $fieldsetModifiers = [];

    /**
     * Add an implementation of FieldsetModifierInterface to be evaluated against each fieldset
     */
    public function registerModifier(FieldsetModifierInterface $fieldsetModifier): void
    {
        $this->fieldsetModifiers[] = $fieldsetModifier;
    }

    /**
     * Apply all registered fieldset modifiers to the specified fieldset
     */
    public function modify(Fieldset $fieldset): void
    {
        foreach ($this->fieldsetModifiers as $fieldsetModifier) {
            if ($fieldsetModifier->shouldModify($fieldset)) {
                $fieldsetModifier->modify($fieldset);
            }
        }
    }
}
