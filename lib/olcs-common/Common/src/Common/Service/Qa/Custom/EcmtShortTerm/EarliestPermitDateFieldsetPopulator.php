<?php

namespace Common\Service\Qa\Custom\EcmtShortTerm;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\DateSelect;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class EarliestPermitDateFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return EarliestPermitDateFieldsetPopulator
     */
    public function __construct(private TranslationHelperService $translator, private HtmlAdder $htmlAdder)
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
        $markup = sprintf(
            '<div class="govuk-inset-text">%s</div><div class="govuk-hint">%s<br>%s</div>',
            $this->translator->translate('qanda.ecmt-short-term.earliest-permit-date.inset'),
            $this->translator->translate('qanda.ecmt-short-term.earliest-permit-date.hint.line-1'),
            $this->translator->translate('qanda.ecmt-short-term.earliest-permit-date.hint.line-2')
        );

        $this->htmlAdder->add($fieldset, 'insetAndHint', $markup);

        $fieldset->add(
            [
                'name' => 'qaElement',
                'type' => DateSelect::class,
                'options' => [
                    'invalidDateKey' => 'qanda.ecmt-short-term.earliest-permit-date.error.date-invalid',
                    'dateInPastKey' => 'qanda.ecmt-short-term.earliest-permit-date.error.date-in-past',
                ],
                'attributes' => [
                    'value' => $options['value']
                ]
            ]
        );
    }
}
