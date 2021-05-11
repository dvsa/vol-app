<?php

namespace Dvsa\Olcs\Application\Controller;

use Olcs\Controller\Lva\AbstractTransportManagersController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see \Dvsa\Olcs\Application\Controller\Factory\TransportManagersControllerFactory
 * @see TransportManagersControllerTest
 */
class TransportManagersController extends AbstractTransportManagersController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return TransportManagersController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $result = parent::createService($serviceLocator);
        $this->initialized = true;
        return $result;
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized === true;
    }
}
