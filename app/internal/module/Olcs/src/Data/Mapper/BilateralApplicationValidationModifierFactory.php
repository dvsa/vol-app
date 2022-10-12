<?php

namespace Olcs\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;

class BilateralApplicationValidationModifierFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BilateralApplicationValidationModifier
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : BilateralApplicationValidationModifier
    {
        return $this->__invoke($serviceLocator, BilateralApplicationValidationModifier::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BilateralApplicationValidationModifier
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : BilateralApplicationValidationModifier
    {
        return new BilateralApplicationValidationModifier(
            $container->get(ApplicationFormPopulator::class)
        );
    }
}
