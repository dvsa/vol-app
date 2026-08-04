<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\CheckboxFieldsetPopulator;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class CheckEcmtNeededFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return CheckEcmtNeededFieldsetPopulator
     */
    public function __construct(private CheckboxFieldsetPopulator $checkboxFieldsetPopulator, private InfoIconAdder $infoIconAdder)
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
        $this->checkboxFieldsetPopulator->populate($form, $fieldset, $options);

        $this->infoIconAdder->add($fieldset, 'qanda.ecmt.check-ecmt-needed.footer-annotation');
    }
}
