<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Common\WarningAdder;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class EmissionsStandardsFieldsetPopulator implements FieldsetPopulatorInterface
{
    public function __construct(private WarningAdder $warningAdder, private TranslationHelperService $translator, private YesNoWithMarkupForNoPopulator $yesNoWithMarkupForNoPopulator, private YesNoValueOptionsGenerator $yesNoValueOptionsGenerator)
    {
    }

    #[\Override]
    public function populate(mixed $form, Fieldset $fieldset, array $options): void
    {
        $valueOptions = $this->yesNoValueOptionsGenerator->generate(
            'qanda.bilaterals.emissions-standards.euro3-or-euro4',
            'qanda.bilaterals.emissions-standards.euro5-euro6-or-higher'
        );

        $this->warningAdder->add($fieldset, 'qanda.bilaterals.emissions-standards.euro2-warning');

        $noMarkup = $this->translator->translate('qanda.bilaterals.emissions-standards.no-blurb');

        $this->yesNoWithMarkupForNoPopulator->populate(
            $fieldset,
            $valueOptions,
            $noMarkup,
            $options['yesNo'],
            'qanda.bilaterals.emissions-standards.not-selected-message'
        );
    }
}
