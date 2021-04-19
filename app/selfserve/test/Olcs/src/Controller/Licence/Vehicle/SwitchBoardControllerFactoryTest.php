<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Olcs\Session\LicenceVehicleManagement;

/**
 * @see SwitchBoardControllerFactory
 */
class SwitchBoardControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @test
     */
    public function createService_IsCallable()
    {
        // Setup
        $sut = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$sut, 'createService']);
    }

    /**
     * @test
     * @depends createService_IsCallable
     */
    public function createService_ReturnsInstanceOfDispatcherWithSwitchBoardController()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut();

        // Execute
        $result = $sut->createService($serviceLocator);

        // Assert
        $this->assertInstanceOf(Dispatcher::class, $result);
        $this->assertInstanceOf(SwitchBoardController::class, $result->getDelegate());
    }

    /**
     * @test
     */
    public function __invoke_IsCallable()
    {
        // Setup
        $sut = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$sut, '__invoke']);
    }

    /**
     * @test
     * @depends __invoke_IsCallable
     */
    public function __invoke_ReturnsInstanceOfDispatcherWithSwitchBoardController()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut();

        // Execute
        $result = $sut->__invoke($serviceLocator, SwitchBoardController::class);

        // Assert
        $this->assertInstanceOf(Dispatcher::class, $result);
        $this->assertInstanceOf(SwitchBoardController::class, $result->getDelegate());
    }

    /**
     * @return FormElementMessageFormatterFactory
     */
    protected function setUpSut(): SwitchBoardControllerFactory
    {
        return new SwitchBoardControllerFactory();
    }

    /**
     *
     */
    protected function setUpDefaultServices()
    {
        return [
            FlashMessenger::class => $this->setUpMockService(FlashMessenger::class),
            FormHelperService::class => $this->setUpMockService(FormHelperService::class),
            HandleQuery::class => $this->setUpMockService(HandleQuery::class),
            Redirect::class => $this->setUpMockService(Redirect::class),
            ResponseHelperService::class => $this->setUpMockService(ResponseHelperService::class),
            Url::class => $this->setUpMockService(Url::class),
            LicenceVehicleManagement::class => new LicenceVehicleManagement()
        ];
    }
}
