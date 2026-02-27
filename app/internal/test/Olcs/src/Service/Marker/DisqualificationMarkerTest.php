<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DisqualificationMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DisqualificationMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\DisqualificationMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\DisqualificationMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRender(): void
    {
        $data = [
            'organisation' => ['disqualifications' => ['XX']],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRenderActiveWithEndDate(): void
    {
        $data = [
            'organisation' => [
                'id' => 75,
                'disqualifications' => [
                    [
                        'startDate' => '2015-08-04',
                        'endDate' => '2016-10-25',
                        'status' => 'AcTiVe'
                    ]

                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/disqualification',
                [
                    'startDateTime' => new \DateTime('2015-08-04'),
                    'endDateTime' => new \DateTime('2016-10-25'),
                    'active' => true,
                    'organisationId' => 75
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }

    public function testRenderNotActiveWithOutEndDate(): void
    {
        $data = [
            'organisation' => [
                'id' => 75,
                'disqualifications' => [
                    [
                        'startDate' => '2015-08-04',
                        'endDate' => '',
                        'status' => 'XXX'
                    ]

                ]
            ],
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/disqualification',
                [
                    'startDateTime' => new \DateTime('2015-08-04'),
                    'endDateTime' => null,
                    'active' => false,
                    'organisationId' => 75
                ]
            )
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
