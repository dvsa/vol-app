<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * ContinuationDetailMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\ContinuationDetailMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\ContinuationDetailMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRender(): void
    {
        $data = [
            'continuationDetail' => ['continuation' => ''],
            'licence' => ['id' => 1],
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'continuationDetail' => ['continuation' => ['year' => '2015', 'month' => '07']],
            'licence' => ['id' => 63],
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/continuation', ['dateTime' => new \DateTime('2015-07-01'), 'licenceId' => 63])
            ->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
