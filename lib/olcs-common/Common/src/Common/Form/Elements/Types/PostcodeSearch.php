<?php

/**
 * PostcodeSearch fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Types;

use Common\Form\Element\OptionalSelect;
use Laminas\Form\Fieldset;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Common\Form\Elements\Types\HtmlTranslated;

/**
 * PostcodeSearch fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PostcodeSearch extends Fieldset
{
    /** @var int Count the number of instances of this class */
    private static $count = 0;

    /**
     * Setup the elements
     *
     * @param null|int|string $name    Optional name for the element
     * @param array           $options Optional options for the element
     */
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        ++self::$count;

        $postcodeSearchId = 'postcodeInput' . self::$count;

        $postcodeSearch = new Text('postcode');
        $postcodeSearch->setAttributes(
            [
                'class' => 'short js-input',
                'data-container-class' => 'inline',
                'id' => $postcodeSearchId,
            ]
        );
        $postcodeSearch->setOptions(
            [
                'remove_if_readonly' => true,
                'label' => 'Postcode search',
                'label_attributes' => [
                    'class' => 'govuk-visually-hidden',
                    'for' => $postcodeSearchId,
                ],
            ]
        );

        $this->add($postcodeSearch);

        $searchButton = new Button('search', ['label' => 'Find address']);
        $searchButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button js-find',
                'data-container-class' => 'inline'
            ]
        );
        $searchButton->setValue('search');

        $this->add($searchButton);

        $selectAddressId = 'selectAddress' . self::$count;

        $selectAddress = new OptionalSelect(
            'addresses',
            [
                'label' => 'postcode.select_address.label',
                'label_attributes' => [
                    'for' => $selectAddressId,
                ],
                'empty_option' => 'Please select'
            ]
        );
        $selectAddress->setAttributes(
            [
                'id' => $selectAddressId,
                'data-container-class' => 'compound address__select'
            ]
        );

        $this->add($selectAddress);

        $selectButton = new Button('select', ['label' => 'Select']);
        $selectButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button js-select',
                'data-container-class' => 'js-hidden'
            ]
        );
        $selectButton->setValue('select');

        $this->add($selectButton);

        $manualLink = new HtmlTranslated('manual-link');
        $manualLink->setValue(
            '<p class="govuk-visually-hidden hint postcode-connectionLost">%s</p><p class="hint"><a class="govuk-link" href=#>%s</a></p>'
        );
        $manualLink->setTokens(['postcode.error.not-available', 'postcode.address.manual_entry']);
        $manualLink->setAttributes(
            [
                'data-container-class' => 'js-visible'
            ]
        );
        $manualLink->setOption('remove_if_readonly', true);

        $this->add($manualLink);
    }

    /**
     * Set messages
     * NB Not sure if this is used
     *
     * @param mixed $messages Messages
     */
    #[\Override]
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    /**
     * Get messages
     * NB Not sure if this is used
     */
    #[\Override]
    public function getMessages(?string $elementName = null): array
    {
        return $this->messages;
    }
}
