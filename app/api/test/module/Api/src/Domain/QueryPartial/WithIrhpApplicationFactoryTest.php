<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithIrhpApplication;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithIrhpApplicationFactory;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManager;
use Psr\Container\ContainerInterface;
use Mockery as m;

class WithIrhpApplicationFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $withPlugin = new With();

        $pluginManager = m::mock(QueryPartialServiceManager::class);
        $pluginManager->expects('get')->with('with')->andReturn($withPlugin);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('QueryPartialServiceManager')->andReturn($pluginManager);

        $sut = new WithIrhpApplicationFactory();
        $this->assertInstanceOf(
            WithIrhpApplication::class,
            $sut->__invoke($container, WithIrhpApplication::class)
        );
    }
}
