<?php

namespace Common\Service\Qa;

use Laminas\Form\Fieldset;

class RadioFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return RadioFieldsetPopulator
     */
    public function __construct(private RadioFactory $radioFactory, private TranslateableTextHandler $translateableTextHandler)
    {
    }

    /**
     * Populate the fieldset with a radio element based on the supplied options array
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $radio = $this->radioFactory->create('qaElement');

        $notSelectedMessage = $this->translateableTextHandler->translate($options['notSelectedMessage']);

        $options['options'][0]['attributes'] = [
            'id' => 'qaElement'
        ];

        $radio->setValueOptions($options['options']);
        $radio->setValue($options['value']);
        $radio->setOption('not_selected_message', $notSelectedMessage);

        $fieldset->add($radio);
    }
}
