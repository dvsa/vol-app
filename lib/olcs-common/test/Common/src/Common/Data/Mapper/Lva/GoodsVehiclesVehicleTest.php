<?php

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Form\FormInterface;

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsVehiclesVehicleTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'bar' => 'foo',
            'version' => 1,
            'vehicle' => [
                'foo' => 'bar'
            ],
            'goodsDiscs' => [
                [
                    'discNo' => 1234
                ]
            ]
        ];

        // Mock the VehicleDiscNo
        $mockVehicleDiscNo = m::mock(VehicleDiscNo::class);
        $mockVehicleDiscNo->shouldReceive('format')
            ->with([
                'bar' => 'foo',
                'version' => 1,
                'goodsDiscs' => [
                    [
                        'discNo' => 1234
                    ]
                ]])
            ->andReturn('Pending');

        $sut = new GoodsVehiclesVehicle($mockVehicleDiscNo);
        $output = $sut->mapFromResult($input);

        $expected = [
            'licence-vehicle' => [
                'bar' => 'foo',
                'version' => 1,
                'discNo' => 'Pending'
            ],
            'data' => [
                'foo' => 'bar',
                'version' => 1
            ]
        ];

        $this->assertEquals($expected, $output);
    }

    public function testMapFromErrors(): void
    {
        $errors = [
            'vrm' => [
                'Error1'
            ],
            'receivedDate' => [
                'Error2'
            ],
            'global' => [
                'Error3'
            ]
        ];
        $formMessages = [
            'data' => [
                'vrm' => ['Error1']
            ],
            'licenceVehicle' => [
                'receivedDate' => ['Error2']
            ]
        ];
        $expected = [
            'global' => [
                'Error3'
            ]
        ];

        /** @var FormInterface $mockForm */
        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($formMessages)
            ->once()
            ->getMock();

        static::assertEquals($expected, GoodsVehiclesVehicle::mapFromErrors($errors, $mockForm));
    }
}
