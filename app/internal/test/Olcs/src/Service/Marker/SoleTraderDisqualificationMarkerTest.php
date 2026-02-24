<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SoleTraderDisqualificationMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SoleTraderDisqualificationMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\BusRegEbsrMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\SoleTraderDisqualificationMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNotSoleTrader(): void
    {
        $data = [
            'organisation' => ['type' => ['id' => 'XXX']]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNoOrgPerson(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'organisationPersons' => [
                    ['foo']
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNoDisqualification(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'organisationPersons' => [
                    [
                        'person' => []
                    ]
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderCountDisqualification(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'organisationPersons' => [
                    [
                        'person' => [
                            'disqualifications' => []
                        ]
                    ]
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRender(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'organisationPersons' => [
                    [
                        'person' => [
                            'disqualifications' => ['foob']
                        ]
                    ]
                ]
            ]
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'id' => 667,
                'organisationPersons' => [
                    [
                        'person' => [
                            'id' => 64,
                            'disqualifications' => [
                                [
                                    'startDate' => '2013-02-12',
                                    'endDate' => '2014-04-23',
                                    'status' => 'Active',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/soletrader-disqualification',
                [
                    'startDateTime' => new \DateTime('2013-02-12'),
                    'endDateTime' => new \DateTime('2014-04-23'),
                    'active' => true,
                    'organisationId' => 667,
                    'personId' => 64,
                ]
            )->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderNoEndDate(): void
    {
        $data = [
            'organisation' => [
                'type' => ['id' => 'org_t_st'],
                'id' => 667,
                'organisationPersons' => [
                    [
                        'person' => [
                            'id' => 64,
                            'disqualifications' => [
                                [
                                    'startDate' => '2013-02-12',
                                    'endDate' => '',
                                    'status' => 'XXX',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/soletrader-disqualification',
                [
                    'startDateTime' => new \DateTime('2013-02-12'),
                    'endDateTime' => null,
                    'active' => false,
                    'organisationId' => 667,
                    'personId' => 64,
                ]
            )->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
