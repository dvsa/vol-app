<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery\MockInterface;
use Mockery as m;

trait MocksServicesTrait
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @return ServiceManager
     */
    protected function serviceManager(): ServiceManager
    {
        assert(null !== $this->serviceManager, 'Expected service manager to be set. Hint: You may need to call `setUpServiceManager` before trying to get a service manager');
        return $this->serviceManager;
    }

    /**
     * @return AbstractPluginManager
     */
    protected function pluginManager(): AbstractPluginManager
    {
        return $this->setUpAbstractPluginManager($this->serviceManager());
    }

    /**
     * @return ServiceManager
     */
    protected function setUpServiceManager(): ServiceManager
    {
        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $services = $this->setUpDefaultServices($this->serviceManager);

        // Maintain support for deprecated way of registering services via an array of services. Instead, services
        // should be registered by calling the available setter methods on the ServiceManager instance.
        if (is_array($services)) {
            foreach ($services as $serviceName => $service) {
                $this->serviceManager->setService($serviceName, $service);
            }
        }

        // Set controller plugin manager to the main service manager so that all services can be resolved from the one
        // service manager instance.
        $this->serviceManager->setService('ControllerPluginManager', $this->serviceManager);

        return $this->serviceManager;
    }

    /**
     * @return ServiceManager
     * @deprecated Please use MocksServicesTrait::setUpServiceManager instead.
     */
    protected function setUpServiceLocator(): ServiceManager
    {
        return $this->setUpServiceManager();
    }

    /**
     * @return MockInterface|AbstractPluginManager
     */
    protected function setUpAbstractPluginManager(ServiceLocatorInterface $serviceLocator): MockInterface
    {
        $instance = m::mock(AbstractPluginManager::class);
        $instance->shouldReceive('getServiceLocator')->andReturn($serviceLocator)->byDefault();
        return $instance;
    }

    protected function getMockService(string $class): MockInterface
    {
        if (!$this->serviceManager->has($class)) {
            $this->serviceManager->setService(
                $class,
                $this->setUpMockService($class)
            );
        }

        return $this->serviceManager->get($class);
    }

    protected function getMockServiceWithName(string $class, string $serviceName): MockInterface
    {
        if (!$this->serviceManager->has($serviceName)) {
            $this->serviceManager->setService(
                $serviceName,
                $this->setUpMockService($class)
            );
        }

        return $this->serviceManager->get($serviceName);
    }

    /**
     * @return MockInterface
     */
    protected function setUpMockService(string $class): MockInterface
    {
        $instance = m::mock($class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * Sets up default services.
     *
     *
     * @return void|array
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        // Set up any default services
    }

    /**
     * @return MockInterface
     * @deprecated Use $this->serviceManager()->get($name) instead
     */
    protected function resolveMockService(ServiceLocatorInterface $serviceLocator, string $name): MockInterface
    {
        return $serviceLocator->get($name);
    }
}
