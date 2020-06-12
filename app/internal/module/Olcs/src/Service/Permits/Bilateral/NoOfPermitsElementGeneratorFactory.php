<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Factory as FormFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NoOfPermitsElementGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return NoOfPermitsElementGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NoOfPermitsElementGenerator(
            $serviceLocator->get('Helper\Translation'),
            new FormFactory()
        );
    }
}
