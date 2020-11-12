<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Fieldset;

/**
 * Fieldset populator interface
 */
interface FieldsetPopulatorInterface
{
    /**
     * Populate a fieldset in accordance with the behaviour associated with a country
     *
     * @param Fieldset $fieldset
     * @param array $fields
     */
    public function populate(Fieldset $fieldset, array $fields);
}
