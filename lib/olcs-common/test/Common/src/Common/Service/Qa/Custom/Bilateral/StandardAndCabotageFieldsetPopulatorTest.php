<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\Radio;
use Common\Service\Qa\Custom\Bilateral\RadioFactory;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageYesNoRadio;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageYesNoRadioFactory;
use Common\Service\Qa\Custom\Bilateral\StandardYesNoValueOptionsGenerator;
use Common\Service\Qa\Custom\Bilateral\YesNoRadioOptionsApplier;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * StandardAndCabotageFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageFieldsetPopulatorTest extends MockeryTestCase
{
    public const STANDARD_YES_NO_VALUE_OPTIONS = [
        'key1' => 'value1',
        'key2' => 'value2'
    ];

    private $yesContentRadio;

    private $yesNoRadio;

    private $yesNoRadioOptionsApplier;

    private $form;

    private $fieldset;

    private $standardAndCabotageFieldsetPopulator;

    #[\Override]
    protected function setUp(): void
    {
        $expectedValueOptionsForYes = [
            StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY
                => StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE
                => StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE
        ];

        $this->yesContentRadio = m::mock(Radio::class);
        $this->yesContentRadio->shouldReceive('setValueOptions')
            ->with($expectedValueOptionsForYes)
            ->once();

        $radioFactory = m::mock(RadioFactory::class);
        $radioFactory->shouldReceive('create')
            ->with('yesContent')
            ->once()
            ->andReturn($this->yesContentRadio);

        $this->yesNoRadio = m::mock(StandardAndCabotageYesNoRadio::class);
        $this->yesNoRadio->shouldReceive('setOption')
            ->with('yesContentElement', $this->yesContentRadio)
            ->once();

        $standardAndCabotageYesNoRadioFactory = m::mock(StandardAndCabotageYesNoRadioFactory::class);
        $standardAndCabotageYesNoRadioFactory->shouldReceive('create')
            ->with('qaElement')
            ->once()
            ->andReturn($this->yesNoRadio);

        $this->yesNoRadioOptionsApplier = m::mock(YesNoRadioOptionsApplier::class);

        $standardYesNoValueOptionsGenerator = m::mock(StandardYesNoValueOptionsGenerator::class);
        $standardYesNoValueOptionsGenerator->shouldReceive('generate')
            ->withNoArgs()
            ->andReturn(self::STANDARD_YES_NO_VALUE_OPTIONS);

        $this->form = m::mock(Form::class);

        $warningVisibleParams = [
            'name' => 'warningVisible',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 'none'
            ]
        ];

        $this->fieldset = m::mock(Fieldset::class);
        $this->fieldset->shouldReceive('add')
            ->with($warningVisibleParams)
            ->once();
        $this->fieldset->shouldReceive('add')
            ->with($this->yesNoRadio)
            ->once()
            ->globally()
            ->ordered();
        $this->fieldset->shouldReceive('add')
            ->with($this->yesContentRadio)
            ->once()
            ->globally()
            ->ordered();
        $this->fieldset->shouldReceive('setOption')
            ->with('radio-element', 'qaElement')
            ->once();
        $this->fieldset->shouldReceive('setLabel')
            ->with('qanda.bilaterals.cabotage.question')
            ->once();
        $this->fieldset->shouldReceive('setLabelAttributes')
            ->with(['class' => 'govuk-visually-hidden'])
            ->once();

        $this->standardAndCabotageFieldsetPopulator = new StandardAndCabotageFieldsetPopulator(
            $radioFactory,
            $standardAndCabotageYesNoRadioFactory,
            $this->yesNoRadioOptionsApplier,
            $standardYesNoValueOptionsGenerator
        );
    }

    public function testPopulateNull(): void
    {
        $options = [
            'value' => null
        ];

        $this->yesNoRadioOptionsApplier->shouldReceive('applyTo')
            ->with(
                $this->yesNoRadio,
                self::STANDARD_YES_NO_VALUE_OPTIONS,
                null,
                'qanda.bilaterals.cabotage.not-selected-message'
            )
            ->once();

        $this->standardAndCabotageFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    public function testPopulateCabotageNotRequired(): void
    {
        $options = [
            'value' => StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY
        ];

        $this->yesNoRadioOptionsApplier->shouldReceive('applyTo')
            ->with(
                $this->yesNoRadio,
                self::STANDARD_YES_NO_VALUE_OPTIONS,
                'N',
                'qanda.bilaterals.cabotage.not-selected-message'
            )
            ->once();

        $this->standardAndCabotageFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    /**
     * @dataProvider dpPopulateCabotageRequired
     */
    public function testPopulateCabotageRequired($answerValue): void
    {
        $options = [
            'value' => $answerValue
        ];

        $this->yesNoRadioOptionsApplier->shouldReceive('applyTo')
            ->with(
                $this->yesNoRadio,
                self::STANDARD_YES_NO_VALUE_OPTIONS,
                'Y',
                'qanda.bilaterals.cabotage.not-selected-message'
            )
            ->once();

        $this->yesContentRadio->shouldReceive('setValue')
            ->with($answerValue)
            ->once();

        $this->standardAndCabotageFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'qanda.bilaterals.cabotage.answer.cabotage-only'}, list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage'}}
     */
    public function dpPopulateCabotageRequired(): array
    {
        return [
            [StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY],
            [StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE],
        ];
    }
}
