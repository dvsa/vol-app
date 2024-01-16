<?php

namespace Olcs\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;

class BilateralApplicationValidationModifierFactory implements FactoryInterface
{
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
