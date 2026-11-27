<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Laminas\Form\Form;

trait Autocomplete
{
    private function addSoleTraderAutocomplete(Form $form): void
    {
        $person = $form->get('data');
        $person->get('forename')->setAttribute('autocomplete', 'given-name');
        $person->get('familyName')->setAttribute('autocomplete', 'family-name');
        $birthDate = $person->get('birthDate');
        $birthDate->getDayElement()->setAttribute('autocomplete', 'bday-day');
        $birthDate->getMonthElement()->setAttribute('autocomplete', 'bday-month');
        $birthDate->getYearElement()->setAttribute('autocomplete', 'bday-year');
    }
}
