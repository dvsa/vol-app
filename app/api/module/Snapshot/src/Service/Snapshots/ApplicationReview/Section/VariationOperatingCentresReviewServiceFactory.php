<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class VariationOperatingCentresReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VariationOperatingCentresReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\PsvOperatingCentre'),
            $container->get('Review\VariationPsvOcTotalAuth'),
            $container->get('Review\GoodsOperatingCentre'),
            $container->get('Review\VariationGoodsOcTotalAuth'),
        );
    }
}
