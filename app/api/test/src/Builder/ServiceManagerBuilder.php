<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Builder;

use Laminas\ServiceManager\ServiceManager;

/**
 * @deprecated Use \Olcs\TestHelpers\Service\MocksServicesTrait instead
 */
class ServiceManagerBuilder implements BuilderInterface
{
    /**
     * @var callable
     */
    protected $servicesProvider;

    public function __construct(callable $servicesProvider)
    {
        $this->servicesProvider = $servicesProvider;
    }

    /**
     * @inheritDoc
     */
    public function build(): mixed
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $result = call_user_func($this->servicesProvider, $serviceManager);
        if (is_array($result)) {
            foreach ($result as $serviceName => $service) {
                $serviceManager->setService($serviceName, $service);
            }
        }
        return $serviceManager;
    }
}
