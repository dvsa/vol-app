<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ApplicationOperatingCentresReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ApplicationOperatingCentresReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\PsvOperatingCentre'),
            $container->get('Review\ApplicationPsvOcTotalAuth'),
            $container->get('Review\GoodsOperatingCentre'),
            $container->get('Review\ApplicationGoodsOcTotalAuth'),
            $container->get('Review\TrafficArea')
        );
    }
}
