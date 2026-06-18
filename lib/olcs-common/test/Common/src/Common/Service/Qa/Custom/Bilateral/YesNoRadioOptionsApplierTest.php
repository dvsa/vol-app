<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\YesNoRadioOptionsApplier;
use Common\Service\Qa\Custom\Bilateral\Radio;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class YesNoRadioOptionsApplierTest extends MockeryTestCase
{
    public const STANDARD_ATTRIBUTES = [
        'id' => 'yesNoRadio',
        'radios_wrapper_attributes' => [
            'id' => 'yesNoRadio',
            'class' => 'govuk-radios--conditional',
            'data-module' => 'radios',
        ]
    ];

    public const VALUE_OPTIONS = [
        'yes' => [
            'label' => 'Yes',
            'value' => 'Y',
        ],
        'no' => [
            'label' => 'No',
            'value' => 'N',
        ]
    ];

    public const NOT_SELECTED_MESSAGE = 'not.selected.message';

    public const RADIO_VALUE = 'radioValue';

    public function testApplyTo(): void
    {
        $radio = m::mock(Radio::class);

        $radio->shouldReceive('setValue')
            ->with(self::RADIO_VALUE)
            ->once();

        $radio->shouldReceive('setValueOptions')
            ->with(self::VALUE_OPTIONS)
            ->once();

        $radio->shouldReceive('setAttributes')
            ->with(self::STANDARD_ATTRIBUTES)
            ->once();

        $radio->shouldReceive('setOption')
            ->with('not_selected_message', self::NOT_SELECTED_MESSAGE)
            ->once();

        $yesNoRadioOptionsApplier = new YesNoRadioOptionsApplier();
        $yesNoRadioOptionsApplier->applyTo($radio, self::VALUE_OPTIONS, self::RADIO_VALUE, self::NOT_SELECTED_MESSAGE);
    }
}
