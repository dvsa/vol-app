<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\SectionRenderer;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\ContentSectionRenderer;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererFactory
 */
class SectionRendererFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockConverterService = m::mock(ConverterService::class);

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->shouldReceive('get')
            ->with(ConverterService::class)
            ->andReturn($mockConverterService);

        $factory = new SectionRendererFactory();
        $result = $factory->__invoke($mockContainer, ContentSectionRenderer::class);

        $this->assertInstanceOf(ContentSectionRenderer::class, $result);
    }
}
