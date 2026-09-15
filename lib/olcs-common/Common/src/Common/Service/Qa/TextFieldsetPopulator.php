<?php

namespace Common\Service\Qa;

use Laminas\Form\Fieldset;

class TextFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return TextFieldsetPopulator
     */
    public function __construct(private TextFactory $textFactory, private TranslateableTextHandler $translateableTextHandler)
    {
    }

    /**
     * Populate the fieldset with a textbox element based on the supplied options array
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $text = $this->textFactory->create('qaElement');
        $text->setValue($options['value']);

        if (isset($options['label'])) {
            $text->setLabel(
                $this->translateableTextHandler->translate($options['label'])
            );
        }

        if (isset($options['hint'])) {
            $text->setOptions(
                [
                    'hint' => $this->translateableTextHandler->translate($options['hint']),
                    'hint-class' => 'govuk-hint'
                ]
            );
        }

        $text->setAttribute('class', 'govuk-input govuk-input--width-10');

        $fieldset->add($text);
    }
}
