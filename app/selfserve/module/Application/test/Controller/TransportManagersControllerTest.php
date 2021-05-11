<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Laminas\ServiceManager\ServiceManager;
use Common\Form\View\Helper\Form;
use Common\Service\Helper\TransportManagerHelperService;

/**
 * @see TransportManagersController
 */
class TransportManagersControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var TransportManagersController
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
     */
    public function isInitialized_IsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'isInitialized']);
    }

    /**
     * @test
     * @depends isInitialized_IsCallable
     */
    public function isInitialized_ReturnsFalseBeforeCreateServiceIsCalled()
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isInitialized();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     * @depends isInitialized_IsCallable
     */
    public function isInitialized_ReturnsTrueAfterCreateServiceIsCalled()
    {
        // Setup
        $this->setUpSut();
        $serviceManager = $this->setUpServiceManager();

        // Execute
        $this->sut->createService($serviceManager);
        $result = $this->sut->isInitialized();

        // Assert
        $this->assertTrue($result);
    }

    protected function setUpSut()
    {
        $this->sut = new TransportManagersController();
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
