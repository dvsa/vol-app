<?php

namespace Common\Test;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Mockery\MockInterface;
use Mockery as m;

trait MocksServicesTrait
{
    /**
     * @var ServiceManager
     */
    private ServiceManager $serviceManager;

    protected function serviceManager(): ServiceManager
    {
        assert(null !== $this->serviceManager, 'Expected service manager to be set. Hint: You may need to call `setUpServiceManager` before trying to get a service manager');
        return $this->serviceManager;
    }

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
     * @deprecated Please use MocksServicesTrait::setUpServiceManager instead.
     */
    protected function setUpServiceLocator(): ServiceManager
    {
        return $this->setUpServiceManager();
    }

    protected function setUpAbstractPluginManager(ContainerInterface $serviceLocator): MockInterface
    {
        $instance = m::mock(AbstractPluginManager::class);
        $instance->shouldReceive('getServiceLocator')->andReturn($serviceLocator)->byDefault();
        return $instance;
    }

    protected function setUpMockService(string $class): MockInterface
    {
        $instance = m::mock($class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * Sets up default services.
     */
    abstract protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager|array;

    /**
     * @deprecated Use $this->serviceManager()->get($name) instead
     */
    protected function resolveMockService(ContainerInterface $serviceLocator, string $name): MockInterface
    {
        return $serviceLocator->get($name);
    }
}
