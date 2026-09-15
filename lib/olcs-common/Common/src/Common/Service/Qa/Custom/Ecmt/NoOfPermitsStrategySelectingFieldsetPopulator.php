<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class NoOfPermitsStrategySelectingFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsStrategySelectingFieldsetPopulator
     */
    public function __construct(private FieldsetPopulatorInterface $singleEmissionsCategoryFieldsetPopulator, private FieldsetPopulatorInterface $multipleEmissionsCategoryFieldsetPopulator)
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
        if (count($options['emissionsCategories']) == 1) {
            $this->singleEmissionsCategoryFieldsetPopulator->populate($form, $fieldset, $options);
            return;
        }

        $this->multipleEmissionsCategoryFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
