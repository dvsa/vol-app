<?php

namespace Common\Form\Elements\Types;

use Laminas\Form\Fieldset;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;
use Common\Form\Elements\Types\HtmlTranslated;

class ApplicationTransportManagers extends Fieldset
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

        $application = new Text('application');
        $application->setAttributes(
            [
                'class' => 'short js-input',
                'data-container-class' => 'inline'
            ]
        );
        $application->setOption('remove_if_readonly', true);

        $this->add($application);

        $searchButton = new Button('search', ['label' => 'Find application']);
        $searchButton->setAttributes(
            [
                'type' => 'submit',
                'class' => 'govuk-button js-find',
                'data-container-class' => 'inline'
            ]
        );
        $searchButton->setValue('search');

        $this->add($searchButton);
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
