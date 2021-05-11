<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Test\MockeryTestCase;
use Dvsa\Olcs\Application\Controller\Factory\TransportManagersControllerFactory;
use Common\Test\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use Common\Form\View\Helper\Form;
use Common\Service\Helper\TransportManagerHelperService;
use Mockery;

/**
 * @see TransportManagersControllerFactory
 */
class TransportManagersControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var TransportManagersControllerFactory
     */
    protected $sut;

    /**
     * @test
     */
    public function createService_IsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'createService']);
    }

    /**
     * @test
     * @depends createService_IsCallable
     */
    public function createService_ReturnsAnInstanceOfTransportManagersController(): TransportManagersController
    {
        // Setup
        $this->setUpSut();
        $pluginManager = $this->setUpAbstractPluginManager($this->setUpServiceManager());

        // Execute
        $result = $this->sut->createService($pluginManager);

        // Assert
        $this->assertInstanceOf(TransportManagersController::class, $result);

        return $result;
    }

    /**
     * @test
     * @depends createService_ReturnsAnInstanceOfTransportManagersController
     * @param TransportManagersController $controller
     */
    public function createService_InitializesController(TransportManagersController $controller)
    {
        // Assert
        $this->assertTrue($controller->isInitialized());
    }

    /**
     * @test
     */
    public function __invoke_IsCallable()
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
    public function __invoke_ReturnsAnInstanceOfTransportManagersController(): TransportManagersController
    {
        // Setup
        $this->setUpSut();
        $pluginManager = $this->setUpAbstractPluginManager($this->setUpServiceManager());

        // Execute
        $result = $this->sut->__invoke($pluginManager, null);

        // Assert
        $this->assertInstanceOf(TransportManagersController::class, $result);

        return $result;
    }

    /**
     * @test
     * @depends __invoke_ReturnsAnInstanceOfTransportManagersController
     * @param TransportManagersController $controller
     */
    public function __invoke_InitializesController(TransportManagersController $controller)
    {
        // Assert
        $this->assertTrue($controller->isInitialized());
    }

    protected function setUpSut()
    {
        $this->sut = new TransportManagersControllerFactory();
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $serviceManager->setService('Helper\Form', $this->setUpMockService(Form::class));
        $serviceManager->setService('Helper\TransportManager', $this->setUpMockService(TransportManagerHelperService::class));
    }
}
