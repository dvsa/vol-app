<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class CabotageOnlyFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return CabotageOnlyFieldsetPopulator
     */
    public function __construct(private TranslationHelperService $translator, private YesNoWithMarkupForNoPopulator $yesNoWithMarkupForNoPopulator, private StandardYesNoValueOptionsGenerator $standardYesNoValueOptionsGenerator)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate(mixed $form, Fieldset $fieldset, array $options): void
    {
        $valueOptions = $this->standardYesNoValueOptionsGenerator->generate();

        $noMarkup = sprintf(
            $this->translator->translate('qanda.bilaterals.cabotage-only.no-blurb'),
            $this->translator->translate($options['countryName'])
        );

        $this->yesNoWithMarkupForNoPopulator->populate(
            $fieldset,
            $valueOptions,
            $noMarkup,
            $options['yesNo'],
            'qanda.bilaterals.cabotage.not-selected-message'
        );

        $fieldset->setLabel('qanda.bilaterals.cabotage.question');
        $fieldset->setLabelAttributes(['class' => 'govuk-visually-hidden']);
    }
}
