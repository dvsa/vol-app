<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\LogoutController;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory;
use Laminas\Http\PhpEnvironment\Request;
use Mockery as m;

class LogoutControllerFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    private $container;

    private $mockRequest;

    private $config;

    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        // Set realm by default, but can be overwritten
        $this->mockRequest = $this->createMock(Request::class);

        $this->config = [
            'auth' => [
                'session_name' => 'session_name',
                'identity_provider' => 'identity_provider',
            ],
            'selfserve_logout_redirect_url' => 'test',
        ];

        parent::setUp();
    }

    public function testNoSelfServeLogoutUrlSpecified(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        // Set realm defined in data provider
        $this->mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn('test');
        $this->container->expects('get')->with('Request')->andReturn($this->mockRequest);

        // Remove the redirect URL from config in this mock
        unset($this->config['selfserve_logout_redirect_url']);
        $this->container->expects('get')->with('Config')->andReturn($this->config);

        // Initialise Factory
        $factory = new LogoutControllerFactory();
        $factory->__invoke($this->container, LogoutController::class);
    }
}
