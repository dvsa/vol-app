<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceConditionsUndertakingsReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LicenceConditionsUndertakingsReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\ConditionsUndertakings')
        );
    }
}
