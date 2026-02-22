<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\FormValidator;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\ServiceManager;
use Olcs\Session\LicenceVehicleManagement;

/**
 * @see SwitchBoardControllerFactory
 */
class SwitchBoardControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $sut = $this->setUpSut();

        // Assert
        $this->assertIsCallable($sut->__invoke(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsInstanceOfDispatcherWithSwitchBoardController(): void
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

    protected function setUpSut(): SwitchBoardControllerFactory
    {
        return new SwitchBoardControllerFactory();
    }

    /**
     * @return (FormValidator|LicenceVehicleManagement|\Mockery\MockInterface)[]
     * @psalm-return array{'Laminas\\Mvc\\Plugin\\FlashMessenger\\FlashMessenger'::class: \Mockery\MockInterface, 'Common\\Service\\Helper\\FormHelperService'::class: \Mockery\MockInterface, 'Common\\Controller\\Plugin\\HandleQuery'::class: \Mockery\MockInterface, 'Common\\Controller\\Plugin\\Redirect'::class: \Mockery\MockInterface, 'Common\\Service\\Helper\\ResponseHelperService'::class: \Mockery\MockInterface, 'Laminas\\Mvc\\Controller\\Plugin\\Url'::class: \Mockery\MockInterface, 'Olcs\\Session\\LicenceVehicleManagement'::class: LicenceVehicleManagement, 'Common\\Form\\FormValidator'::class: FormValidator}
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager): array
    {
        return [
            FlashMessenger::class => $this->setUpMockService(FlashMessenger::class),
            FormHelperService::class => $this->setUpMockService(FormHelperService::class),
            HandleQuery::class => $this->setUpMockService(HandleQuery::class),
            Redirect::class => $this->setUpMockService(Redirect::class),
            ResponseHelperService::class => $this->setUpMockService(ResponseHelperService::class),
            Url::class => $this->setUpMockService(Url::class),
            LicenceVehicleManagement::class => new LicenceVehicleManagement(),
            FormValidator::class => new FormValidator(),
        ];
    }
}
