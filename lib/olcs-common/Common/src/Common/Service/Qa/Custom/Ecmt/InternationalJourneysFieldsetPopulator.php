<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Types\Html;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\RadioFieldsetPopulator;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

class InternationalJourneysFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return InternationalJourneysFieldsetPopulator
     */
    public function __construct(private RadioFieldsetPopulator $radioFieldsetPopulator, private NiWarningConditionalAdder $niWarningConditionalAdder)
    {
    }

    /**
     * Populate the fieldset with elements based on the supplied options array
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $this->niWarningConditionalAdder->addIfRequired($fieldset, $options['showNiWarning']);

        $fieldset->add(
            [
                'name' => 'warningVisible',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => 0
                ]
            ]
        );

        $this->radioFieldsetPopulator->populate($form, $fieldset, $options['radio']);
    }
}
