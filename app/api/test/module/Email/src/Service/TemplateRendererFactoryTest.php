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
        $sl->shouldReceive('has')->with('config')->once()->andReturn(false);
        $service = $this->sut->__invoke($sl, TemplateRenderer::class);

        $this->assertSame($mockViewRenderer, $service->getViewRenderer());
        $this->assertFalse($service->isNotifyMode());
    }

    public function testInvokeReadsNotifyModeFromMailDsn(): void
    {
        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $config = [
            'mail' => ['dsn' => 'govuknotify+mailpit://mailpit:1025'],
            'email' => ['notify' => ['passthrough_templates' => ['en_GB' => 'uuid-en', 'cy_GB' => 'uuid-cy']]],
        ];

        $sl = m::mock(ContainerInterface::class);
        $sl->shouldReceive('get')->with('TemplateStrategySelectingViewRenderer')->once()->andReturn($mockViewRenderer);
        $sl->shouldReceive('has')->with('config')->once()->andReturn(true);
        $sl->shouldReceive('get')->with('config')->once()->andReturn($config);

        $service = $this->sut->__invoke($sl, TemplateRenderer::class);

        $this->assertTrue($service->isNotifyMode());
        $this->assertSame('uuid-en', $service->getPassthroughTemplateUuid('en_GB'));
        $this->assertSame('uuid-cy', $service->getPassthroughTemplateUuid('cy_GB'));
    }

    public function testInvokeStaysInSmtpModeForSmtpDsn(): void
    {
        $mockViewRenderer = m::mock(StrategySelectingViewRenderer::class);
        $config = ['mail' => ['dsn' => 'smtp://mailpit:1025']];

        $sl = m::mock(ContainerInterface::class);
        $sl->shouldReceive('get')->with('TemplateStrategySelectingViewRenderer')->once()->andReturn($mockViewRenderer);
        $sl->shouldReceive('has')->with('config')->once()->andReturn(true);
        $sl->shouldReceive('get')->with('config')->once()->andReturn($config);

        $service = $this->sut->__invoke($sl, TemplateRenderer::class);

        $this->assertFalse($service->isNotifyMode());
    }
}
