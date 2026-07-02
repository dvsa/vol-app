<?php

namespace CommonTest\Service\Api;

use Common\Service\Api\Resolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Psr\Container\ContainerInterface;

class ResolverTest extends MockeryTestCase
{
    public function testGetClient(): void
    {
        $container = m::mock(ContainerInterface::class);
        $mockService = new \StdClass();

        $sut = new Resolver($container);
        $sut->setService('Olcs\RestService\Backend\Tasks', $mockService);

        $this->assertSame($mockService, $sut->getClient('Backend\Tasks'));
    }

    public function testValidate(): void
    {
        $container = m::mock(ContainerInterface::class);
        $sut = new Resolver($container);
        $this->assertNull($sut->validate(null));
    }
}
