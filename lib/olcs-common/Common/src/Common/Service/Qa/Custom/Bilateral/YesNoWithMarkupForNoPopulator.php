<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\RadioFactory;
use Laminas\Form\Fieldset;

class YesNoWithMarkupForNoPopulator
{
    /**
     * Create service instance
     *
     * @param RadioFactory $radioFactory
     *
     * @return YesNoWithMarkupForNoPopulator
     */
    public function __construct(private RadioFactory $radioFactory, private YesNoRadioOptionsApplier $yesNoRadioOptionsApplier, private HtmlAdder $htmlAdder)
    {
    }

    /**
     * Populate the fieldset with yes/no radio buttons, with the yes option being active in accordance with the yesNo
     * parameter, the no option being annotated with the specified markup, and notSelectedMessage being used as the
     * error if the form is submitted with neither option selected
     *
     * @param string $noMarkup
     * @param string $notSelectedMessage
     */
    public function populate(Fieldset $fieldset, array $valueOptions, $noMarkup, mixed $yesNo, $notSelectedMessage): void
    {
        $yesNoRadio = $this->radioFactory->create('qaElement');
        $yesNoValue = is_null($yesNo) ? null : 'Y';

        $this->yesNoRadioOptionsApplier->applyTo($yesNoRadio, $valueOptions, $yesNoValue, $notSelectedMessage);

        $fieldset->add($yesNoRadio);

        $this->htmlAdder->add(
            $fieldset,
            'noContent',
            sprintf('<div class="govuk-hint">%s</div>', $noMarkup)
        );

        $fieldset->setOption('radio-element', 'qaElement');
    }
}
