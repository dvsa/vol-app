<?php

namespace Common\Service\Qa\FieldsetModifier;

use Laminas\Form\Fieldset;

interface FieldsetModifierInterface
{
    /**
     * Whether the specified fieldset needs to be modified by this fieldset modifier
     *
     *
     * @return bool
     */
    public function shouldModify(Fieldset $fieldset);

    /**
     * Make the required changes to the fieldset when shouldModify has returned true
     */
    public function modify(Fieldset $fieldset);
}
