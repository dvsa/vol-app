<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * IsRemovedMarkerTest
 *
 * @author Josh Curtis <josh@josh-curtis.co.uk>
 */
class IsRemovedMarkerTest extends TestCase
{
    /**
     *
     * @var \Olcs\Service\Marker\TransportManager\Rule50Marker
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\TransportManager\IsRemovedMarker();
    }

    public function testCanRenderWithNoData(): void
    {
        $this->assertFalse($this->sut->canRender());
    }

    public function testCanRenderWithData(): void
    {
        $this->sut->setData(['transportManager' => ['removedDate' => 'notnull']]);

        $this->assertTrue($this->sut->canRender());
    }

    public function testRender(): void
    {
        $this->sut->setData(['transportManager' => ['removedDate' => '1990-2-10 10:00']]);

        $mockPartialHelper = m::mock(\Laminas\View\Helper\Partial::class);
        $mockPartialHelper->shouldReceive('__invoke')
            ->with(
                'marker/transport-manager/is-removed',
                [
                    'date' => new \DateTime('1990-2-10 10:00')
                ]
            )->once()->andReturn('HTML5');

        $this->sut->setPartialHelper($mockPartialHelper);

        $this->assertSame('HTML5', $this->sut->render());
    }
}
