<?php

namespace Olcs\Service\Permits\Bilateral;

use Laminas\Form\Fieldset;

/**
 * Fieldset populator interface
 */
interface FieldsetPopulatorInterface
{
    /**
     * Populate a fieldset in accordance with the behaviour associated with a country
     */
    public function populate(Fieldset $fieldset, array $fields);
}
