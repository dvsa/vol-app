<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangeLicenceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ChangeLicence
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ChangeLicence(
            $serviceLocator->get('Helper\Translation')
        );
    }
}
