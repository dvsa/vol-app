<?php

/**
 * Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\PsvVehicles;
use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;

/**
 * Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'version' => 1,
            'hasEnteredReg' => 'Y',
            'organisation' => [
                'confirmShareVehicleInfo' => 'Y'
            ]
        ];

        $expected = [
            'data' => [
                'version' => 1,
                'hasEnteredReg' => 'Y'
            ],
            'shareInfo' => [
                'shareInfo' => 'Y'
            ]
        ];

        $this->assertEquals($expected, PsvVehicles::mapFromResult($data));
    }

    public function testMapFormErrors(): void
    {
        $form = m::mock(Form::class);
        $fm = m::mock(FlashMessengerHelperService::class);
        $messages = [
            'hasEnteredReg' => [
                'foo' => 'foo'
            ],
            'bar'
        ];

        $expectedFormMessages = [
            'data' => [
                'hasEnteredReg' => [
                    'foo'
                ]
            ]
        ];

        $form->shouldReceive('setMessages')
            ->once()
            ->with($expectedFormMessages);

        $fm->shouldReceive('addCurrentErrorMessage')
            ->once()
            ->with('bar');

        PsvVehicles::mapFormErrors($form, $messages, $fm);
    }
}
