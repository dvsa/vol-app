<?php

/**
 * Address fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;

/**
 * Address fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Address extends Fieldset
{
    public const TYPE_HOME = 'home';
    public const TYPE_WORK = 'work';

    #[\Override]
    public function prepareElement(FormInterface $form): void
    {
        if ($this->getLabel() === null || $this->getLabel() === '') {
            $this->setLabel('Address');
        }

        $type = $this->getOption('address_type');
        if ($type !== null && $this->has('searchPostcode')) {
            $search = $this->get('searchPostcode');
            if ($search instanceof PostcodeSearch) {
                $search->setAddressType($type);
            }
        }

        parent::prepareElement($form);
    }

    /**
     * The shared address fieldsets give some fields a fixed id, e.g. "postcode". Where a form has more than one
     * address, call this on each address after the first, so that its fields use their names as ids instead,
     * e.g. "workAddress[postcode]", and no id is repeated on the page.
     */
    public function useNamesAsIds(): void
    {
        foreach ($this->getElements() as $element) {
            if ($element->getAttribute('id')) {
                $element->setAttribute('id', sprintf('%s[%s]', $this->getName(), $element->getName()));
            }
        }
    }
}
