<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\CurrentUser;
use Common\View\Helper\CurrentUserFactory;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

class CurrentUserFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockAuth = m::mock(AuthorizationService::class);
        $config = [
            'auth' => [
                'user_unique_id_salt' => 'salt',
            ],
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(AuthorizationService::class)->andReturn($mockAuth);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $sut = new CurrentUserFactory();
        $service = $sut->__invoke($mockSl, CurrentUser::class);

        $this->assertInstanceOf(CurrentUser::class, $service);
    }

    public function testMissingConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(CurrentUserFactory::MSG_MISSING_ANALYTICS_CONFIG);

        $config = [
            'auth' => [],
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $sut = new CurrentUserFactory();
        $sut->__invoke($mockSl, CurrentUser::class);
    }
}
