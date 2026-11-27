<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Laminas\Form\Element;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Fieldset;

trait AutocompleteAssertions
{
    private function createPersonFieldset(): Fieldset
    {
        $person = new Fieldset('data');
        foreach (['forename', 'familyName'] as $name) {
            $person->add(new Element($name));
        }

        return $person;
    }

    private function assertPersonAutocomplete(Fieldset $person): void
    {
        self::assertSame('given-name', $person->get('forename')->getAttribute('autocomplete'));
        self::assertSame('family-name', $person->get('familyName')->getAttribute('autocomplete'));
    }
}
