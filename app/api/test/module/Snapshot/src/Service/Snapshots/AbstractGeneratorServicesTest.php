<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices::class)]
final class AbstractGeneratorServicesTest extends MockeryTestCase
{
    public function testGetRenderer(): void
    {
        $renderer = m::mock(RendererInterface::class);

        $sut = new AbstractGeneratorServices($renderer);

        $this->assertSame(
            $renderer,
            $sut->getRenderer()
        );
    }
}
