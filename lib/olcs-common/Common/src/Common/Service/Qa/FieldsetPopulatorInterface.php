<?php

namespace Common\Service\Qa;

use Laminas\Form\Fieldset;

interface FieldsetPopulatorInterface
{
    /**
     * Populate the supplied fieldset with form elements in accordance with the specified options
     */
    public function populate(mixed $form, Fieldset $fieldset, array $options);
}
