<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * BusRefEbsrMarkerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRefEbsrMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\BusRegEbsrMarker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\BusRegEbsrMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderN(): void
    {
        $data = [
            'busReg' => ['isTxcApp' => 'N']
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderY(): void
    {
        $data = [
            'busReg' => ['isTxcApp' => 'Y']
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $data = [
            'busReg' => ['isTxcApp' => 'Y']
        ];

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/busreg-ebsr', ['busReg' => $data['busReg']])->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
