<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\TextFieldsetPopulator;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

class AnnualTripsAbroadFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return AnnualTripsAbroadFieldsetPopulator
     */
    public function __construct(private TextFieldsetPopulator $textFieldsetPopulator, private TranslationHelperService $translator, private NiWarningConditionalAdder $niWarningConditionalAdder, private HtmlAdder $htmlAdder)
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

        $guidanceBlueMarkup = sprintf(
            '<div class="govuk-inset-text">%s</div>',
            $this->translator->translate('qanda.ecmt.annual-trips-abroad.guidance')
        );

        $ecmtTripsHintMarkup = $this->translator->translate('markup-ecmt-trips-hint');

        $this->htmlAdder->add($fieldset, 'hint', $guidanceBlueMarkup . $ecmtTripsHintMarkup);

        $fieldset->add(
            [
                'name' => 'warningVisible',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => 0
                ]
            ]
        );

        $this->textFieldsetPopulator->populate($form, $fieldset, $options['text']);
    }
}
