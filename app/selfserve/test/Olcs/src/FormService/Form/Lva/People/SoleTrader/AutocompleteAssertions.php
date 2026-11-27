<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Common\Form\View\Helper\FormDateSelect;
use Laminas\Form\Element;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Fieldset;
use Laminas\Form\View\Helper\FormInput;
use Laminas\I18n\Translator\Translator;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

trait AutocompleteAssertions
{
    private function createPersonFieldset(): Fieldset
    {
        $person = new Fieldset('data');
        foreach (['forename', 'familyName'] as $name) {
            $person->add(new Element($name));
        }
        $birthDate = new DateSelect('birthDate');
        $birthDate->setAttribute('id', 'dob');
        $birthDate->setValue(['day' => '5', 'month' => '6', 'year' => '1990']);
        $person->add($birthDate);

        return $person;
    }

    private function assertPersonAutocomplete(Fieldset $person): void
    {
        self::assertSame('given-name', $person->get('forename')->getAttribute('autocomplete'));
        self::assertSame('family-name', $person->get('familyName')->getAttribute('autocomplete'));

        $helpers = new HelperPluginManager($this->createStub(ContainerInterface::class));
        $helpers->setService('forminput', new FormInput());
        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);
        $dateHelper = new FormDateSelect();
        $dateHelper->setView($view);
        $dateHelper->setTranslator(new Translator());
        $document = new \DOMDocument();
        $document->loadHTML($dateHelper->render($person->get('birthDate')));
        $xpath = new \DOMXPath($document);
        foreach (['day' => '5', 'month' => '6', 'year' => '1990'] as $part => $value) {
            $input = '//input[@id="dob_' . $part . '"]';
            self::assertSame(1, $xpath->query($input)->length);
            self::assertSame('bday-' . $part, $xpath->evaluate('string(' . $input . '/@autocomplete)'));
            self::assertSame($value, $xpath->evaluate('string(' . $input . '/@value)'));
        }
    }
}
