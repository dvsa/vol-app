<?php

namespace CommonTest\Service\Qa;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Service\Qa\CheckboxFactory;
use Common\Service\Qa\CheckboxFieldsetPopulator;
use Common\Service\Qa\TranslateableTextHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * CheckboxFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckboxFieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestPopulate
     */
    public function testPopulate($checked): void
    {
        $notCheckedMessageOptions = [
            'key' => 'notCheckedMessageKey',
            'parameters' => [
                'notCheckedMessageParam1',
                'notCheckedMessageParam2'
            ],
        ];

        $translatedNotCheckedMessage = 'translatedNotCheckedMessage';

        $labelOptions = [
            'key' => 'labelKey',
            'parameters' => [
                'labelParam1',
                'labelParam2'
            ],
        ];

        $translatedLabel = 'translatedLabel';

        $options = [
            'checked' => $checked,
            'label' => $labelOptions,
            'notCheckedMessage' => $notCheckedMessageOptions,
        ];

        $expectedCheckboxAttributes = [
            'class' => 'input--qasinglecheckbox',
            'id' => 'qaElement',
        ];

        $expectedCheckboxOptions = [
            'not_checked_message' => $translatedNotCheckedMessage,
            'must_be_value' => '1',
            'checked_value' => '1',
        ];

        $checkbox = m::mock(SingleCheckbox::class);
        $checkbox->shouldReceive('setAttributes')
            ->with($expectedCheckboxAttributes)
            ->once();
        $checkbox->shouldReceive('setLabel')
            ->with($translatedLabel)
            ->once();
        $checkbox->shouldReceive('setLabelAttributes')
            ->with(['class' => 'form-control form-control--checkbox form-control--advanced'])
            ->once();
        $checkbox->shouldReceive('setOptions')
            ->with($expectedCheckboxOptions)
            ->once();
        $checkbox->shouldReceive('setChecked')
            ->with($checked)
            ->once();

        $checkboxFactory = m::mock(CheckboxFactory::class);
        $checkboxFactory->shouldReceive('create')
            ->once()
            ->andReturn($checkbox);

        $translateableTextHandler = m::mock(TranslateableTextHandler::class);
        $translateableTextHandler->shouldReceive('translate')
            ->with($labelOptions)
            ->andReturn($translatedLabel);
        $translateableTextHandler->shouldReceive('translate')
            ->with($notCheckedMessageOptions)
            ->andReturn($translatedNotCheckedMessage);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($checkbox)
            ->once();

        $form = m::mock(Form::class);

        $sut = new CheckboxFieldsetPopulator($checkboxFactory, $translateableTextHandler);
        $sut->populate($form, $fieldset, $options);
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public function dpTestPopulate(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
