<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VariationVehiclesPsvReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VariationVehiclesPsvReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\VehiclesPsv')
        );
    }
}
