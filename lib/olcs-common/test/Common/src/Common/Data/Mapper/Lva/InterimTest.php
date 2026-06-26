<?php

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Mockery as m;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Data\Mapper\Lva\Interim;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'version' => 1,
            'interimReason' => 'foo',
            'interimStart' => '2015-01-01',
            'interimEnd' => '2015-01-02',
            'interimAuthHgvVehicles' => 10,
            'interimAuthLgvVehicles' => 11,
            'interimAuthTrailers' => 12,
            'interimStatus' => [
                'id' => '123'
            ]
        ];
        $expected = [
            'version' => 1,
            'data' => [
                'interimReason' => 'foo',
                'interimStart' => '2015-01-01',
                'interimEnd' => '2015-01-02',
                'interimAuthHgvVehicles' => 10,
                'interimAuthLgvVehicles' => 11,
                'interimAuthTrailers' => 12
            ],
            'requested' => [
                'interimRequested' => 'Y'
            ],
            'interimStatus' => [
                'status' => '123'
            ]
        ];

        $this->assertEquals($expected, Interim::mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $data = [
            'version' => 1,
            'data' => [
                'interimReason' => 'foo',
                'interimStart' => '2015-01-01',
                'interimEnd' => '2015-01-02',
                'interimAuthHgvVehicles' => 10,
                'interimAuthLgvVehicles' => 11,
                'interimAuthTrailers' => 12
            ],
            'requested' => [
                'interimRequested' => 'Y'
            ],
            'interimStatus' => [
                'status' => '123'
            ],
            'operatingCentres' => ['id' => [123]],
            'vehicles' => ['id' => [321]]
        ];
        $expected = [
            'version' => 1,
            'requested' => 'Y',
            'reason' => 'foo',
            'startDate' => '2015-01-01',
            'endDate' => '2015-01-02',
            'authHgvVehicles' => 10,
            'authLgvVehicles' => 11,
            'authTrailers' => 12,
            'operatingCentres' => [123],
            'vehicles' => [321],
            'status' => '123'
        ];

        $this->assertEquals($expected, Interim::mapFromForm($data));
    }

    public function testMapFormErrors(): void
    {
        $messages = [
            'reason' => [
                'foo' => 'bar1'
            ],
            'startDate' => [
                'foo' => 'bar2'
            ],
            'endDate' => [
                'foo' => 'bar3'
            ],
            'authHgvVehicles' => [
                'foo' => 'bar4'
            ],
            'authLgvVehicles' => [
                'foo' => 'bar4.1'
            ],
            'authTrailers' => [
                'foo' => 'bar5'
            ],
            'foo' => 'bar6'
        ];

        $expected = [
            'data' => [
                'interimReason' => [
                    'bar1'
                ],
                'interimStart' => [
                    'bar2'
                ],
                'interimEnd' => [
                    'bar3'
                ],
                'interimAuthHgvVehicles' => [
                    'bar4'
                ],
                'interimAuthLgvVehicles' => [
                    'bar4.1'
                ],
                'interimAuthTrailers' => [
                    'bar5'
                ]
            ]
        ];

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('setMessages')
            ->once()
            ->with($expected);

        $fm = m::mock(FlashMessengerHelperService::class);
        $fm->shouldReceive('addCurrentErrorMessage')
            ->once()
            ->with('bar6');

        Interim::mapFormErrors($form, $messages, $fm);
    }
}
