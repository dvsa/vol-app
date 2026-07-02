<?php

/**
 * Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\PsvVehiclesVehicle;
use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;

/**
 * Psv Vehicles Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicleTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'id' => 123,
            'version' => 1,
            'vehicle' => [
                'vrm' => 'AB111AB',
                'makeModel' => 'Foo',
            ],
            'receivedDate' => '2015-01-01',
            'specifiedDate' => '2015-01-01',
            'removalDate' => null
        ];

        $expected = [
            'data' => [
                'id' => 123,
                'version' => 1,
                'vrm' => 'AB111AB',
                'makeModel' => 'Foo',
            ],
            'licence-vehicle' => [
                'receivedDate' => '2015-01-01',
                'specifiedDate' => '2015-01-01',
                'removalDate' => null
            ]
        ];

        $this->assertEquals($expected, PsvVehiclesVehicle::mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $data = [
            'data' => [
                'id' => 123,
                'version' => 1,
                'vrm' => 'AB111AB',
                'makeModel' => 'Foo',
            ],
            'licence-vehicle' => [
                'receivedDate' => '2015-01-01',
                'specifiedDate' => '2015-01-01',
                'removalDate' => null
            ]
        ];

        $expected = [
            'version' => 1,
            'vrm' => 'AB111AB',
            'makeModel' => 'Foo',
            'receivedDate' => '2015-01-01',
            'specifiedDate' => '2015-01-01',
            'removalDate' => null
        ];

        $this->assertEquals($expected, PsvVehiclesVehicle::mapFromForm($data));
    }

    public function testMapFormErrors(): void
    {
        $form = m::mock(Form::class);
        $fm = m::mock(FlashMessengerHelperService::class);
        $messages = [
            'vrm' => [
                'foo' => 'foo'
            ],
            'removalDate' => [
                'bar' => 'bar'
            ],
            'bar'
        ];

        $expectedFormMessages = [
            'data' => [
                'vrm' => [
                    'foo'
                ]
            ],
            'licence-vehicle' => [
                'removalDate' => [
                    'bar'
                ]
            ]
        ];

        $form->shouldReceive('setMessages')
            ->once()
            ->with($expectedFormMessages);

        $fm->shouldReceive('addCurrentErrorMessage')
            ->once()
            ->with('bar');

        PsvVehiclesVehicle::mapFormErrors($form, $messages, $fm);
    }
}
