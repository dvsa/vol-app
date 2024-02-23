<?php
declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\ServiceManager;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Controller\Auth\LoginControllerFactory;
use PHPUnit\Framework\TestCase;

class LoginControllerFactoryTest extends TestCase
{

    /**
     * @var LoginControllerFactory
     */
    protected $sut;

    /**
     * @test
     */
    public function __invoke_IsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__invoke']);
    }

    /**
     * @test
     * @depends __invoke_IsCallable
     */
    public function __invoke_ReturnsAnInstanceOfDispatcherWithLoginController()
    {
        // Setup
        $this->setUpSut();
        $serviceManager = $this->createMock(\Interop\Container\ContainerInterface::class);
        $serviceManager->method('get')->willReturnMap([
            [SelfserveCommandAdapter::class, $this->createMock(SelfserveCommandAdapter::class)],
            [AuthenticationServiceInterface::class  , $this->createMock(AuthenticationServiceInterface::class)],
            ['Auth\CookieService', $this->createMock(CookieService::class)],
            [CurrentUser::class, $this->createMock(CurrentUser::class)],
            [FlashMessenger::class, $this->createMock(FlashMessenger::class)],
            [FormHelperService::class, $this->createMock(FormHelperService::class)],
            [Redirect::class, $this->createMock(Redirect::class)],
            [Url::class, $this->createMock(Url::class)],
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
