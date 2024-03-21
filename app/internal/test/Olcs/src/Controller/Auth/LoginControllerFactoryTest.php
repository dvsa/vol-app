<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Psr\Container\ContainerInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Auth\Adapter\InternalCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Controller\Auth\LoginControllerFactory;

class LoginControllerFactoryTest extends MockeryTestCase
{
    /**
     * @var LoginControllerFactory
     */
    protected $sut;

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__invoke']);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfDispatcherWithLoginController()
    {
        // Setup
        $this->setUpSut();

        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->willReturnMap([
            [InternalCommandAdapter::class, $this->createMock(InternalCommandAdapter::class)],
            [AuthenticationServiceInterface::class, $this->createMock(AuthenticationServiceInterface::class)],
            [CurrentUser::class, $this->createMock(CurrentUser::class)],
            [FlashMessenger::class, $this->createMock(FlashMessenger::class)],
            [FormHelperService::class, $this->createMock(FormHelperService::class)],
            [Layout::class, $this->createMock(Layout::class)],
            [Redirect::class, $this->createMock(Redirect::class)],
            [Url::class, $this->createMock(Url::class)],
            ['ControllerPluginManager', $sm],
        ]);

        // Execute
        $result = $this->sut->__invoke($sm, null);

        // Assert
        $this->assertInstanceOf(Dispatcher::class, $result);
        $this->assertInstanceOf(LoginController::class, $result->getDelegate());
    }

    protected function setUpSut(): void
    {
        $this->sut = new LoginControllerFactory();
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $serviceManager->setService(InternalCommandAdapter::class, $this->createMock(InternalCommandAdapter::class));
        $serviceManager->setService(AuthenticationServiceInterface::class, $this->createMock(AuthenticationServiceInterface::class));
        $serviceManager->setService('Auth\CookieService', $this->createMock(CookieService::class));
        $serviceManager->setService(CurrentUser::class, $this->createMock(CurrentUser::class));
        $serviceManager->setService(FlashMessenger::class, $this->createMock(FlashMessenger::class));
        $serviceManager->setService(FormHelperService::class, $this->createMock(FormHelperService::class));
        $serviceManager->setService(Layout::class, $this->createMock(Layout::class));
        $serviceManager->setService(Redirect::class, $this->createMock(Redirect::class));
        $serviceManager->setService(Url::class, $this->createMock(Url::class));
    }
}
