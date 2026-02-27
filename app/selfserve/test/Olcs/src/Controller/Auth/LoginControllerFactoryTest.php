<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Controller\Auth\LoginControllerFactory;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LoginControllerFactoryTest extends TestCase
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
        $serviceManager = $this->createMock(\Interop\Container\ContainerInterface::class);
        $serviceManager->method('get')->willReturnMap([
            [SelfserveCommandAdapter::class, $this->createStub(SelfserveCommandAdapter::class)],
            [AuthenticationServiceInterface::class  , $this->createStub(AuthenticationServiceInterface::class)],
            [CurrentUser::class, $this->createStub(CurrentUser::class)],
            [FlashMessenger::class, $this->createStub(FlashMessenger::class)],
            [FormHelperService::class, $this->createStub(FormHelperService::class)],
            [Redirect::class, $this->createStub(Redirect::class)],
            [Url::class, $this->createStub(Url::class)],
            ['ControllerPluginManager', $serviceManager]
        ]);

        // Execute
        $result = $this->sut->__invoke($serviceManager, null);

        // Assert
        $this->assertInstanceOf(Dispatcher::class, $result);
        $this->assertInstanceOf(LoginController::class, $result->getDelegate());
    }

    protected function setUpSut(): void
    {
        $this->sut = new LoginControllerFactory();
    }
}
