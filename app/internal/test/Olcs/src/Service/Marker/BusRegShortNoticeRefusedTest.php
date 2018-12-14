<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * BusRegShortNoticeRefusedTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BusRegShortNoticeRefusedTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\BusRegShortNoticeRefused
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\BusRegShortNoticeRefused();
    }

    public function testCanRenderWithNoData()
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderN()
    {
        $data = [
            'busReg' => ['shortNoticeRefused' => 'N']
        ];

        $this->sut->setData($data);

        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderY()
    {
        $data = [
            'busReg' => ['shortNoticeRefused' => 'Y']
        ];

        $this->sut->setData($data);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender()
    {
        $data = [
            'busReg' => ['shortNoticeRefused' => 'Y']
        ];

        $mockPartialHelper = m::mock(\Zend\View\Helper\Partial::class);

        $mockPartialHelper->shouldReceive('__invoke')
            ->with('marker/busreg-notice-refused', [])->once()->andReturn('HTML1');

        $this->sut->setData($data);
        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML1', $this->sut->render());
    }
}
