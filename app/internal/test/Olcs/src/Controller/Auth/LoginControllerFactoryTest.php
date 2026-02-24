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

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LoginControllerFactoryTest extends MockeryTestCase
{
    /**
     * @var LoginControllerFactory
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->__invoke(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsAnInstanceOfDispatcherWithLoginController(): void
    {
        // Setup
        $this->setUpSut();

        $sm = $this->createMock(ContainerInterface::class);
        $sm->method('get')->willReturnMap([
            [InternalCommandAdapter::class, $this->createStub(InternalCommandAdapter::class)],
            [AuthenticationServiceInterface::class, $this->createStub(AuthenticationServiceInterface::class)],
            [CurrentUser::class, $this->createStub(CurrentUser::class)],
            [FlashMessenger::class, $this->createStub(FlashMessenger::class)],
            [FormHelperService::class, $this->createStub(FormHelperService::class)],
            [Layout::class, $this->createStub(Layout::class)],
            [Redirect::class, $this->createStub(Redirect::class)],
            [Url::class, $this->createStub(Url::class)],
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

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $serviceManager->setService(InternalCommandAdapter::class, $this->createStub(InternalCommandAdapter::class));
        $serviceManager->setService(AuthenticationServiceInterface::class, $this->createStub(AuthenticationServiceInterface::class));
        $serviceManager->setService('Auth\CookieService', $this->createStub(CookieService::class));
        $serviceManager->setService(CurrentUser::class, $this->createStub(CurrentUser::class));
        $serviceManager->setService(FlashMessenger::class, $this->createStub(FlashMessenger::class));
        $serviceManager->setService(FormHelperService::class, $this->createStub(FormHelperService::class));
        $serviceManager->setService(Layout::class, $this->createStub(Layout::class));
        $serviceManager->setService(Redirect::class, $this->createStub(Redirect::class));
        $serviceManager->setService(Url::class, $this->createStub(Url::class));
    }
}
