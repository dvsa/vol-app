<?php

/**
 * PersonSearch fieldset
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Common\Form\Elements\InputFilters\Name;
use Common\Form\Elements\Custom\DateSelect;

/**
 * PersonSearch fieldset
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PersonSearch extends Fieldset
{
    /**
     * Setup the elements
     *
     * @param string $name
     * @param array $options
     */
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $personSearch = new Text('personSearch');
        $personSearch->setAttributes(
            [
                'class' => 'short',
                'data-container-class' => 'inline'
            ]
        );

        $this->add($personSearch);

        $searchButton = new Button('search', ['label' => 'Find person']);
        $searchButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button govuk-button--secondary'
            ]
        );
        $searchButton->setValue('search');

        $this->add($searchButton);

        $selectPerson = new Select('person-list', ['label' => '', 'empty_option' => 'Please select']);
        $selectPerson->setAttributes(
            [
                'data-container-class' => 'inline'
            ]
        );

        $this->add($selectPerson);

        $selectButton = new Button('select', ['label' => 'Select']);
        $selectButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button'
            ]
        );
        $selectButton->setValue('select');

        $this->add($selectButton);

        $personFirstname = new Name('personFirstname', ['label' => 'First name(s)']);
        $personFirstname->setAttributes(
            [
                'id' => 'personFirstname',
                'class' => 'long',
                'placeholder' => ''
            ]
        );
        $this->add($personFirstname);

        $personLastname = new Name('personLastname', ['label' => 'Last name']);
        $personLastname->setAttributes(
            [
                'id' => 'personLastname',
                'class' => 'long',
                'placeholder' => ''
            ]
        );

        $this->add($personLastname);

        $birthDate = new DateSelect(
            'birthDate',
            ['label' => 'Date of birth']
        );
        $birthDate->setAttributes(
            [
                'id' => 'dob',
                'class' => 'long',
            ]
        );
        $birthDate->setOptions(
            [
                'create_empty_option' => true,
                'render_delimiters' => false,
                'required' => false,
            ]
        );
        $this->add($birthDate);

        $addNewButton = new Button('addNew', ['label' => 'Add new']);
        $addNewButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button govuk-button--secondary'
            ]
        );
        $addNewButton->setValue('addNew');

        $this->add($addNewButton);
    }

    #[\Override]
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    #[\Override]
    public function getMessages(?string $elementName = null): array
    {
        return $this->messages;
    }
}
