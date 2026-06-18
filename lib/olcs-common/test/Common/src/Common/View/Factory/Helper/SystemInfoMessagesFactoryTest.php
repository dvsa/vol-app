<?php

namespace CommonTest\View\Factory\Helper;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\View\Factory\Helper\SystemInfoMessagesFactory;
use Common\View\Helper\SystemInfoMessages;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * @covers Common\View\Factory\Helper\SystemInfoMessagesFactory
 */
class SystemInfoMessagesFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $queryService = m::mock(CachingQueryService::class);
        $transferAnnotationBuilder = m::mock(AnnotationBuilder::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('QueryService')->andReturn($queryService);
        $container->expects('get')->with('TransferAnnotationBuilder')->andReturn($transferAnnotationBuilder);

        static::assertInstanceOf(
            SystemInfoMessages::class,
            (new SystemInfoMessagesFactory())->__invoke($container, SystemInfoMessages::class)
        );
    }
}
