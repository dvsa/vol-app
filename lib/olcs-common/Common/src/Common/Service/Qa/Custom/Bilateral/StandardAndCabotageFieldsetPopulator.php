<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

class StandardAndCabotageFieldsetPopulator implements FieldsetPopulatorInterface
{
    public const ANSWER_CABOTAGE_ONLY = 'qanda.bilaterals.cabotage.answer.cabotage-only';

    public const ANSWER_STANDARD_AND_CABOTAGE = 'qanda.bilaterals.cabotage.answer.standard-and-cabotage';

    public const ANSWER_STANDARD_ONLY = 'qanda.bilaterals.cabotage.answer.standard-only';

    public const CABOTAGE_VALUE_OPTIONS = [
        self::ANSWER_CABOTAGE_ONLY => self::ANSWER_CABOTAGE_ONLY,
        self::ANSWER_STANDARD_AND_CABOTAGE => self::ANSWER_STANDARD_AND_CABOTAGE
    ];

    /**
     * Create service instance
     *
     *
     * @return StandardAndCabotageFieldsetPopulator
     */
    public function __construct(private RadioFactory $radioFactory, private StandardAndCabotageYesNoRadioFactory $standardAndCabotageYesNoRadioFactory, private YesNoRadioOptionsApplier $yesNoRadioOptionsApplier, private StandardYesNoValueOptionsGenerator $standardYesNoValueOptionsGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $fieldset->add(
            [
                'name' => 'warningVisible',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => 'none'
                ]
            ]
        );

        $cabotageOptions = $this->radioFactory->create('yesContent');
        $cabotageOptions->setValueOptions(self::CABOTAGE_VALUE_OPTIONS);

        $yesNoRadio = $this->standardAndCabotageYesNoRadioFactory->create('qaElement');
        $yesNoRadio->setOption('yesContentElement', $cabotageOptions);

        $optionsValue = $options['value'];
        $yesNoValue = null;
        if (!is_null($optionsValue)) {
            $yesNoValue = 'N';

            if ($optionsValue != self::ANSWER_STANDARD_ONLY) {
                $yesNoValue = 'Y';
                $cabotageOptions->setValue($optionsValue);
            }
        }

        $valueOptions = $this->standardYesNoValueOptionsGenerator->generate();

        $this->yesNoRadioOptionsApplier->applyTo(
            $yesNoRadio,
            $valueOptions,
            $yesNoValue,
            'qanda.bilaterals.cabotage.not-selected-message'
        );

        $fieldset->add($yesNoRadio);
        $fieldset->add($cabotageOptions);

        $fieldset->setOption('radio-element', 'qaElement');
        $fieldset->setLabel('qanda.bilaterals.cabotage.question');
        $fieldset->setLabelAttributes(['class' => 'govuk-visually-hidden']);
    }
}
