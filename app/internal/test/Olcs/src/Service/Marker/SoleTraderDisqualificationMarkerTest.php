<?php

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

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\SoleTraderDisqualificationMarker();
    }

    public function testCanRenderWithNoData()
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNotSoleTrader()
    {
        $data = [
            'organisation' => ['type' => ['id' => 'XXX']]
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderNoOrgPerson()
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

    public function testCanRenderNoDisqualification()
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

    public function testCanRenderCountDisqualification()
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

    public function testCanRender()
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

    public function testRender()
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

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

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

    public function testRenderNoEndDate()
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

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

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
