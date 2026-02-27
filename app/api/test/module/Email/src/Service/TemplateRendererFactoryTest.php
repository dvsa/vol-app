<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Email\Service\TemplateRendererFactory;

/**
 * TemplateRendererFactoryTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TemplateRendererFactoryTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new TemplateRendererFactory();
    }

    public function testInvoke(): void
    {
        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $sl = m::mock(ContainerInterface::class);
        $sl->shouldReceive('get')->with('TemplateStrategySelectingViewRenderer')->once()->andReturn($mockViewRenderer);
        $service = $this->sut->__invoke($sl, TemplateRenderer::class);

        $this->assertSame($mockViewRenderer, $service->getViewRenderer());
    }
}
