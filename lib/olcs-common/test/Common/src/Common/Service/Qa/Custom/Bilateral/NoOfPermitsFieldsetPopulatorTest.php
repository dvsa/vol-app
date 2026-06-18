<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\NoOfPermitsElement;
use Common\Service\Qa\Custom\Bilateral\NoOfPermitsFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * NoOfPermitsFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $text1Name = 'text1_name';
        $text1Label = 'text 1 label';
        $text1Hint = 'text 1 hint';
        $text1Value = '44';

        $text2Name = 'text2_name';
        $text2Label = 'text 2 label';
        $text2Hint = 'text 2 hint';
        $text2Value = '54';

        $options = [
            'texts' => [
                [
                    'name' => $text1Name,
                    'label' => $text1Label,
                    'hint' => $text1Hint,
                    'value' => $text1Value,
                ],
                [
                    'name' => $text2Name,
                    'label' => $text2Label,
                    'hint' => $text2Hint,
                    'value' => $text2Value,
                ],
            ]
        ];

        $expectedText1AddParams = [
            'type' => NoOfPermitsElement::class,
            'name' => $text1Name,
            'options' => [
                'label' => $text1Label,
                'hint' => $text1Hint,
            ],
            'attributes' => [
                'id' => $text1Name,
                'value' => $text1Value
            ]
        ];

        $expectedText2AddParams = [
            'type' => NoOfPermitsElement::class,
            'name' => $text2Name,
            'options' => [
                'label' => $text2Label,
                'hint' => $text2Hint,
            ],
            'attributes' => [
                'id' => $text2Name,
                'value' => $text2Value
            ]
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($expectedText1AddParams)
            ->once()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($expectedText2AddParams)
            ->once()
            ->ordered();

        $noOfPermitsFieldsetPopulator = new NoOfPermitsFieldsetPopulator();
        $noOfPermitsFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
