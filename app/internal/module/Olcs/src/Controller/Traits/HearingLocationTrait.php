<?php

/**
 * Hearing Location Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Hearing Location Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait HearingLocationTrait
{
    /**
     * @codeCoverageIgnore This method is to assist with unit testing
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     * @return object
     */
    public function callParentGenerateFormWithData($name, $callback, $data = null, $tables = false)
    {
        return parent::generateFormWithData($name, $callback, $data, $tables);
    }

    /**
     * Overrides the parent so that hearing location can be processed properly
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     * @return object
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $form = $this->callParentGenerateFormWithData($name, $callback, $data, $tables);

        $fields = $form->get('fields');

        $piVenue = $fields->get('piVenue')->getValue();
        $piVenueOther = $fields->get('piVenueOther')->getValue();

        //second check not strictly necessary but would mean the piVenue
        //field would have priority if both fields somehow had data
        if (!empty($piVenueOther) && empty($piVenue)) {
            $fields->get('piVenue')->setValue('other');
        }

        return $form;
    }
}
