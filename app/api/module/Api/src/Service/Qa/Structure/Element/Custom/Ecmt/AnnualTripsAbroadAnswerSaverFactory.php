<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnnualTripsAbroadAnswerSaverFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AnnualTripsAbroadAnswerSaver
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnnualTripsAbroadAnswerSaver
    {
        return new AnnualTripsAbroadAnswerSaver(
            $container->get('QaBaseAnswerSaver')
        );
    }
}
