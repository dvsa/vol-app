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
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Mockery as m;

class LoginControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var LoginControllerFactory
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

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

        // Execute
        $result = $this->sut->__invoke($this->serviceManager(), null);

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
        $serviceManager->setService(SelfserveCommandAdapter::class, $this->setUpMockService(SelfserveCommandAdapter::class));
        $serviceManager->setService(AuthenticationServiceInterface::class, $this->setUpMockService(AuthenticationServiceInterface::class));
        $serviceManager->setService('Auth\CookieService', $this->setUpMockService(CookieService::class));
        $serviceManager->setService(CurrentUser::class, $this->setUpMockService(CurrentUser::class));
        $serviceManager->setService(FlashMessenger::class, $this->setUpMockService(FlashMessenger::class));
        $serviceManager->setService(FormHelperService::class, $this->setUpMockService(FormHelperService::class));
        $serviceManager->setService(Redirect::class, $this->setUpMockService(Redirect::class));
        $serviceManager->setService(Url::class, $this->setUpMockService(Url::class));
    }
}
