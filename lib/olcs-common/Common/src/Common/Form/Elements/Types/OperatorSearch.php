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

/**
 * Defendant fieldset
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class OperatorSearch extends Fieldset
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

        $operatorSearch = new Text('operatorSearch');
        $operatorSearch->setAttributes(
            [
                'class' => 'short',
                'data-container-class' => 'inline'
            ]
        );

        $this->add($operatorSearch);

        $searchButton = new Button('search', ['label' => 'Find operator']);
        $searchButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button govuk-button--secondary'
            ]
        );
        $searchButton->setValue('search');

        $this->add($searchButton);

        $selectList = new Select('entity-list', ['label' => '', 'empty_option' => 'Please select']);
        $selectList->setAttributes(
            [
                'data-container-class' => 'inline'
            ]
        );

        $this->add($selectList);

        $selectButton = new Button('select', ['label' => 'Select']);
        $selectButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button'
            ]
        );
        $selectButton->setValue('select');

        $this->add($selectButton);

        $operatorName = new \Common\Form\Elements\InputFilters\Name('operatorName', ['label' => 'operator-name']);
        $operatorName->setAttributes(
            [
                'id' => 'operatorName',
                'class' => 'long',
                'placeholder' => ''
            ]
        );
        $this->add($operatorName);

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
