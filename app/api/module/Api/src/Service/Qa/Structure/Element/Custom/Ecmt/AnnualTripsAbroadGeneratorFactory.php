<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnnualTripsAbroadGeneratorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AnnualTripsAbroadGenerator(
            $container->get('QaEcmtAnnualTripsAbroadElementFactory'),
            $container->get('QaTextElementGenerator')
        );
    }
}
