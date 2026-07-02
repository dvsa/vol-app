<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa;

use Common\Form\Elements\InputFilters\QaRadio;
use Common\Service\Qa\RadioFactory;
use Common\Service\Qa\RadioFieldsetPopulator;
use Common\Service\Qa\TranslateableTextHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

class RadioFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $valueOptions = [
            [
                'value' => 'permit_app_uc',
                'label' => 'Under Consideration'
            ],
            [
                'value' => 'permit_app_nys',
                'label' => 'Not Yet Submitted'
            ]
        ];

        $updatedValueOptions = [
            [
                'value' => 'permit_app_uc',
                'label' => 'Under Consideration',
                'attributes' => [
                    'id' => 'qaElement'
                ]
            ],
            [
                'value' => 'permit_app_nys',
                'label' => 'Not Yet Submitted'
            ]
        ];

        $value = 'permit_app_uc';

        $notSelectedMessageOptions = [
            'key' => 'notSelectedMessageKey',
            'parameters' => [
                'notSelectedMessageParam1',
                'notSelectedMessageParam2'
            ],
        ];

        $translatedNotSelectedMessage = 'translatedNotSelectedMessage';

        $options = [
            'options' => $valueOptions,
            'value' => $value,
            'notSelectedMessage' => $notSelectedMessageOptions,
        ];

        $radio = m::mock(QaRadio::class);
        $radio->shouldReceive('setValueOptions')
            ->with($updatedValueOptions)
            ->once();
        $radio->shouldReceive('setValue')
            ->with($value)
            ->once();
        $radio->shouldReceive('setOption')
            ->with('not_selected_message', $translatedNotSelectedMessage)
            ->once();

        $radioFactory = m::mock(RadioFactory::class);
        $radioFactory->shouldReceive('create')
            ->once()
            ->andReturn($radio);

        $translateableTextHandler = m::mock(TranslateableTextHandler::class);
        $translateableTextHandler->shouldReceive('translate')
            ->with($notSelectedMessageOptions)
            ->andReturn($translatedNotSelectedMessage);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($radio)
            ->once();

        $form = m::mock(Form::class);

        $sut = new RadioFieldsetPopulator($radioFactory, $translateableTextHandler);
        $sut->populate($form, $fieldset, $options);
    }
}
