<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Laminas\Form\Element\DateSelect;
use Laminas\Form\Form;

trait Autocomplete
{
    private function addSoleTraderAutocomplete(Form $form): void
    {
        $person = $form->get('data');
        $person->get('forename')->setAttribute('autocomplete', 'given-name');
        $person->get('familyName')->setAttribute('autocomplete', 'family-name');
    }
}
