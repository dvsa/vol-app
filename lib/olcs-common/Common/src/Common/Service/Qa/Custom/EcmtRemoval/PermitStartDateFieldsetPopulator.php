<?php

namespace Common\Service\Qa\Custom\EcmtRemoval;

use Common\Service\Qa\Custom\Common\DateSelectMustBeBefore;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class PermitStartDateFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return PermitStartDateFieldsetPopulator
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
            '<div class="govuk-hint">%s<br>%s</div>',
            $this->translator->translate('qanda.ecmt-removal.permit-start-date.hint.line-1'),
            $this->translator->translate('qanda.ecmt-removal.permit-start-date.hint.line-2')
        );

        $this->htmlAdder->add($fieldset, 'hint', $markup);

        $fieldset->add(
            [
                'name' => 'qaElement',
                'type' => DateSelectMustBeBefore::class,
                'options' => [
                    'dateMustBeBefore' => $options['dateThreshold'],
                    'invalidDateKey' => 'qanda.ecmt-removal.permit-start-date.error.date-invalid',
                    'dateInPastKey' => 'qanda.ecmt-removal.permit-start-date.error.date-in-past',
                    'dateNotBeforeKey' => 'qanda.ecmt-removal.permit-start-date.error.date-too-far',
                    'create_empty_option' => true,
                ],
                'attributes' => [
                    'value' => $options['date']['value']
                ]
            ]
        );
    }
}
