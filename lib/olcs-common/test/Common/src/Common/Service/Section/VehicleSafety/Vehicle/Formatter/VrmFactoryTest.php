<?php

declare(strict_types=1);

namespace CommonTest\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm;
use Common\Service\Section\VehicleSafety\Vehicle\Formatter\VrmFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

/**
 * VrmTest builds the formatter directly with a UrlHelperService, so it cannot see what the factory
 * hands it. The factory fetched 'ViewHelperManager' and passed it to a constructor typed
 * UrlHelperService, and nothing noticed, because nothing resolved it.
 *
 * The constructor parameter is typed, so reaching an instance at all is the regression assertion.
 */
final class VrmFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $urlHelper = m::mock(UrlHelperService::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(UrlHelperService::class)->andReturn($urlHelper);

        $sut = new VrmFactory();

        $service = $sut->__invoke($container, Vrm::class);

        $this->assertInstanceOf(Vrm::class, $service);
    }
}
